<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Module;
use App\Models\Slot;
use App\Models\Effect;
use Illuminate\Http\Request;

class SimulationController extends Controller
{

    private const GRID_WIDTH  = 3;
    private const GRID_HEIGHT = 4;

    public function index(Request $request)
    {
        $category = $request->input('category');
        $all_modules = $this->getModules();
        $modules = $this->getModules($category);
        $categories = Module::select('category')->distinct()->pluck('category');
        $slots = Slot::with(['module.effects'])->get(); // preload relaties

        return view('sim_dashboard', compact('modules', 'category', 'categories', 'slots', 'all_modules'));
    }

    private function getModules($category = null)
    {
        $query = Module::with('effects'); // preload effecten
        if ($category) {
            $query->where('category', $category);
        }
        return $query->get();
    }

    public function koppelModule(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'module_id' => 'required|exists:modules,id',
                'slot_id'   => 'required|exists:slots,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()->toArray()
            ], 422);
        }

        $newModule  = Module::findOrFail($request->module_id);
        $targetSlot = Slot::findOrFail($request->slot_id);
        $category   = $newModule->category;

        $incompatible = [
            'Veiligheid'      => ['Recreatie', 'Mobiliteit'],
            'Recreatie'       => ['Veiligheid', 'Voorzieningen'],
            'Milieukwaliteit' => ['Mobiliteit'],
            'Voorzieningen'   => ['Recreatie', 'Mobiliteit'],
            'Mobiliteit'      => ['Veiligheid', 'Milieukwaliteit', 'Voorzieningen'],
        ];

        if (isset($incompatible[$category])) {
            $pos = $targetSlot->index;

            if ($pos === null) {
                return response()->json(['message' => 'Fout: Slot index (positie) is niet ingesteld.'], 500);
            }

            $x = $pos % self::GRID_WIDTH;
            $y = intdiv($pos, self::GRID_WIDTH);

            $adjacentPositions = [];
            foreach ([-1, 0, 1] as $dx) {
                foreach ([-1, 0, 1] as $dy) {
                    if ($dx === 0 && $dy === 0) continue;
                    $nx = $x + $dx;
                    $ny = $y + $dy;
                    if ($nx < 0 || $nx >= self::GRID_WIDTH)  continue;
                    if ($ny < 0 || $ny >= self::GRID_HEIGHT) continue;
                    $adjacentPositions[] = $ny * self::GRID_WIDTH + $nx;
                }
            }

            if (!empty($adjacentPositions)) {
                $adjacentSlotIds = Slot::whereIn('index', $adjacentPositions)->pluck('id');

                if ($adjacentSlotIds->isNotEmpty()) {
                    $conflict = Slot::whereIn('id', $adjacentSlotIds)
                        ->whereHas('module', function ($q) use ($incompatible, $category) {
                            $q->whereIn('category', $incompatible[$category]);
                        })
                        ->exists();

                    if ($conflict) {
                        return response()->json([
                            'message' => __('errors.category_incompatible', ['category' => $category])
                        ], 422);
                    }
                }
            }
        }

        $categoryLimits = [
            'Veiligheid'      => 4,
            'Recreatie'       => 2,
            'Milieukwaliteit' => 3,
            'Voorzieningen'   => 5,
            'Mobiliteit'      => 4,
        ];

        if (isset($categoryLimits[$category])) {
            $currentCountQuery       = Slot::whereHas('module', fn ($q) => $q->where('category', $category));
            $isReplacingSameCategory = $targetSlot->module && $targetSlot->module->category === $category;

            if (!$isReplacingSameCategory) {
                $countExcludingTarget = $currentCountQuery->where('id', '!=', $targetSlot->id)->count();
                $prospectiveCount     = $countExcludingTarget + 1;

                if ($prospectiveCount > $categoryLimits[$category]) {
                    return response()->json([
                        'message' => __('errors.category_limit_reached', [
                            'limit'    => $categoryLimits[$category],
                            'category' => $category,
                        ]),
                    ], 409);
                }
            }
        }

        $targetSlot->update(['module_id' => $newModule->id]);

        return response()->json(['success' => true]);
    }

    public function removeModule(Slot $slot)
    {
        $slot->update(['module_id' => null]);
        return redirect()->back();
    }

    private function getAllSlots()
    {
        return Slot::with('module')->get();
    }

    public function updateEffect(Request $request, $moduleId, $type)
    {
        $validTypes = ['safety', 'recreation', 'climate', 'facilities', 'infrastructure'];

        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid effect type'], 400);
        }

        $validated = $request->validate([
            'value' => 'required|integer|min:-5|max:5',
        ]);

        $effect = Effect::updateOrCreate(
            ['module_id' => $moduleId, 'type' => $type],
            ['value' => $validated['value']]
        );

        return response()->json(['success' => true, 'effect' => $effect]);
    }
}
