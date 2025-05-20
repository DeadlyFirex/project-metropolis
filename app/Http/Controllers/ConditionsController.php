<?php

namespace App\Http\Controllers;

use App\Models\Condition;
use App\Models\Module;
use App\Models\Slot;
use Illuminate\Http\Request;

class ConditionsController extends Controller
{
    private const GRID_WIDTH = 3;
    private const GRID_HEIGHT = 4;

    /* ───────── Dashboard ───────── */

    public function index()
    {
        return view('conditions_dashboard', [
            'conditions' => Condition::orderBy('category')->get(),
        ]);
    }

    /* ───────── CRUD ───────── */

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string|max:50|unique:conditions,category',
            'max' => ['required', 'regex:/^(\d+|-)$/'],
            'incompatible' => 'nullable|array',
            'incompatible.*' => 'string',
        ]);

        Condition::create([
            'category' => $data['category'],
            'max' => $data['max'] === '-' ? null : (int)$data['max'],
            'incompatible' => $data['incompatible'] ?? [],
        ]);

        return back()->with('status', 'Categorie toegevoegd');
    }

    public function update(Request $request, Condition $condition)
    {
        $data = $request->validate([
            'max' => ['nullable', 'regex:/^(\d+|-)$/'],
            'incompatible' => 'nullable|array',
            'incompatible.*' => 'string',
        ]);

        $condition->update([
            'max' => $request->has('max')
                ? ($data['max'] === '-' ? null : (int)$data['max'])
                : $condition->max,
            'incompatible' => $data['incompatible'] ?? [],
        ]);

        return back()->with('status', 'Regel aangepast');
    }

    public function destroy(Condition $condition)
    {
        $condition->delete();
        return back()->with('status', 'Categorie verwijderd');
    }

    /* ───────── Spelregels ───────── */

    public function exceedsCategoryLimit(Module $newModule, Slot $targetSlot): bool
    {
        $condition = Condition::where('category', $newModule->category)->first();
        if (!$condition) {
            return false;
        }

        $limit = $condition->max;

        if ($limit === null) {                      // ∞
            return false;
        }

        $count = Slot::whereHas(
            'module',
            fn($q) => $q->where('category', $newModule->category)
        )
            ->where('id', '!=', $targetSlot->id)
            ->count();

        $replacingSame =
            $targetSlot->module_id !== null &&
            $targetSlot->module &&
            $targetSlot->module->category === $newModule->category;

        $exceeds = $replacingSame ? false : ($count + 1) > $limit;

        return $exceeds;
    }

    public function violatesAdjacencyRule(Module $newModule, Slot $targetSlot): bool
    {
        $conditionRecord = Condition::where('category', $newModule->category)->first();
        if (!$conditionRecord) {
            return false;
        }

        $blocked = $conditionRecord->incompatible;

        if (is_string($blocked)) {
            $blocked = json_decode($blocked, true) ?: [];
        }

        if (empty($blocked)) {
            return false;
        }

        $pos = $targetSlot->index;
        if ($pos === null) {
            return false;
        }

        $x = $pos % self::GRID_WIDTH;
        $y = intdiv($pos, self::GRID_WIDTH);

        $adjacentIdx = [];
        foreach ([-1, 0, 1] as $dx) {
            foreach ([-1, 0, 1] as $dy) {
                if ($dx === 0 && $dy === 0) continue;
                $nx = $x + $dx;
                $ny = $y + $dy;
                if ($nx < 0 || $nx >= self::GRID_WIDTH) continue;
                if ($ny < 0 || $ny >= self::GRID_HEIGHT) continue;
                $adjacentIdx[] = $ny * self::GRID_WIDTH + $nx;
            }
        }

        $adjacentSlots = Slot::whereIn('index', $adjacentIdx)
            ->whereHas('module')
            ->with('module:id,category')
            ->get();

        $adjacentCats = $adjacentSlots->pluck('module.category')->unique()->values()->all();

        $conflicts = array_intersect($adjacentCats, $blocked);

        if (!empty($conflicts)) {
            return true;
        }

        return false;
    }
}
