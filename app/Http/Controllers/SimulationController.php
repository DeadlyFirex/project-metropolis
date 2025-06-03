<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Module;
use App\Models\Slot;
use App\Models\Effect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimulationController extends Controller
{
    public function index(Request $request)
    {
        $category     = $request->input('category');
        $all_modules  = $this->getModules();          // naam gewijzigd
        $modules      = $this->getModules($category);
        $categories   = Module::select('category')->distinct()->pluck('category');
        $slots        = Slot::with(['module.effects'])->get();
        $userId = Auth::id();
        $userClock = \App\Models\UserClock::where('user_id', $userId)->first();
        $clockTime = $userClock ? $userClock->time : '00:00:00';

        return view('sim_dashboard', compact(
            'modules',
            'category',
            'categories',
            'slots',
            'all_modules',
            'clockTime'
        ));
    }

    private function getModules($category = null)
    {
        $query = Module::with('effects');
        if ($category) {
            $query->where('category', $category);
        }
        return $query->get();
    }

    public function koppelModule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'slot_id'   => 'required|exists:slots,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $newModule  = Module::findOrFail($request->module_id);
        $targetSlot = Slot::findOrFail($request->slot_id);
        $conditions = app(ConditionsController::class);

        if ($conditions->violatesAdjacencyRule($newModule, $targetSlot)) {
            return response()->json([
                'message' => __('errors.category_incompatible', [
                    'category' => $newModule->category,
                ]),
            ], 422);
        }

        if ($conditions->exceedsCategoryLimit($newModule, $targetSlot)) {
            return response()->json([
                'message' => __('errors.category_limit_reached', [
                    'category' => $newModule->category,
                ]),
            ], 409);
        }

        $targetSlot->update(['module_id' => $newModule->id]);

        return response()->json(['success' => true]);
    }

    public function removeModule(Slot $slot)
    {
        $slot->update(['module_id' => null]);
        return redirect()->back();
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
    public function saveClock(Request $request)
    {
        $request->validate([
            'time' => ['required', 'regex:/^\d{2}:\d{2}:\d{2}$/']
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $time = $request->input('time');
        $userClock = \App\Models\UserClock::updateOrCreate(
            ['user_id' => $userId],
            ['clock_time' => $time]
        );

        return response()->json(['success' => true]);
    }
}
