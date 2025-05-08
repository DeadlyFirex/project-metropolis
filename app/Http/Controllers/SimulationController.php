<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Module;
use App\Models\Slot;

class SimulationController extends Controller
{
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
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $newModule  = Module::findOrFail($request->module_id);
        $targetSlot = Slot::findOrFail($request->slot_id);
        $category   = $newModule->category;

        $categoryLimits = [
            'Care'         => 1,
            'Residential'  => 3,
            'Public Space' => 2,
            'Education'    => 1,
        ];

        if (isset($categoryLimits[$category])) {
            $currentCount = Slot::whereHas(
                'module',
                fn ($q) => $q->where('category', $category)
            )->count();

            if (
                !$targetSlot->module ||
                $targetSlot->module->category !== $category
            ) {
                if ($currentCount >= $categoryLimits[$category]) {
                    return response()->json([
                        'message' => __('errors.category_limit_reached', [
                            'limit'    => $categoryLimits[$category],
                            'category' => $category,
                        ]),
                    ], 422);
                }
            }
        }

        $targetSlot->update(['module_id' => $newModule->id]);

    }

    public function removeModule(Slot $slot)
    {
        $slot->update(['module_id' => null]);
        return redirect()->back();
    }

    private function getAllSlots()
    {
        return Slot::all();
    }

    private function getModules($category = null)
    {
        return $category
            ? Module::where('category', $category)->get()
            : Module::all();
    }
}
