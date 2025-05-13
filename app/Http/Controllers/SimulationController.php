<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Module;
use App\Models\Slot;

class SimulationController extends Controller
{
    private const GRID_WIDTH  = 3;
    private const GRID_HEIGHT = 4;

    public function index(Request $request)
    {
        $category   = $request->input('category');
        $modules    = $this->getModules($category);
        $categories = Module::select('category')->distinct()->pluck('category');
        $slots      = $this->getAllSlots();

        return view('sim_dashboard', compact('modules', 'category', 'categories', 'slots'));
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
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()->toArray()], 422);
        }

        $newModule  = Module::findOrFail($request->module_id);
        $targetSlot = Slot::findOrFail($request->slot_id);
        $category   = $newModule->category;

        $incompatible = [
            'Safety'                => ['Recreation', 'Mobility'],
            'Recreation'            => ['Safety', 'Facilities'],
            'Environmental Quality' => ['Mobility'],
            'Facilities'            => ['Recreation', 'Mobility'],
            'Mobility'              => ['Safety', 'Environmental Quality', 'Facilities'],
        ];


        if (isset($incompatible[$category])) {
            $pos = $targetSlot->index;

            if ($pos === null) {
                return response()->json(['message' => 'Fout: Slot index (positie) is niet ingesteld.'], 500);
            }

            $x   = $pos % self::GRID_WIDTH;
            $y   = intdiv($pos, self::GRID_WIDTH);

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
                // Query op 'index'
                $adjacentSlotIds = Slot::whereIn('index', $adjacentPositions)->pluck('id');

                if ($adjacentSlotIds->isNotEmpty()) {
                    $conflict = Slot::whereIn('id', $adjacentSlotIds)
                        ->whereHas('module', function ($q) use ($incompatible, $category) {
                            $q->whereIn('category', $incompatible[$category]);
                        })
                        ->exists();

                    if ($conflict) {
                        return response()->json(['message' => __('errors.category_incompatible', ['category' => $category])], 422);
                    }
                }
            }
        }

        // Category Limit Check
        $categoryLimits = [
            'Safety'                => 4,
            'Recreation'            => 2,
            'Environmental Quality' => 3,
            'Facilities'            => 5,
            'Mobility'              => 4,
        ];


        if (isset($categoryLimits[$category])) {
            $currentCountQuery = Slot::whereHas('module', fn ($q) => $q->where('category', $category));
            $isReplacingSameCategory = $targetSlot->module && $targetSlot->module->category === $category;

            if (!$isReplacingSameCategory) {
                $countExcludingTarget = $currentCountQuery->where('id', '!=', $targetSlot->id)->count();
                $prospectiveCount = $countExcludingTarget + 1;

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

        // Update Slot
        $targetSlot->update(['module_id' => $newModule->id]);

        return response()->noContent();
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

    private function getModules($category = null)
    {
        return $category
            ? Module::where('category', $category)->get()
            : Module::all();
    }
}
