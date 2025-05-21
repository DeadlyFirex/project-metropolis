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
        $condition = Condition::where('category', $newModule->category)->first();
        if (!$condition) return false;

        $blocked = is_string($condition->incompatible)
            ? (json_decode($condition->incompatible, true) ?: [])
            : ($condition->incompatible ?? []);

        if (empty($blocked)) return false;

        $index = $targetSlot->index;
        if ($index === null) return false;

        $adjacent = $this->neighbourIndexes($index);

        $adjacentCats = Slot::whereIn('index', $adjacent)
            ->whereNotNull('module_id')
            ->with('module:id,category')
            ->get()
            ->pluck('module.category')
            ->unique()
            ->all();

        return !empty(array_intersect($adjacentCats, $blocked));
    }

    private function neighbourIndexes(int $index): array
    {
        $x = intdiv($index, self::GRID_HEIGHT);
        $y = $index % self::GRID_HEIGHT;

        $idx = [];
        foreach ([-1, 0, 1] as $dx) {
            foreach ([-1, 0, 1] as $dy) {
                if ($dx === 0 && $dy === 0) continue;

                $nx = $x + $dx;
                $ny = $y + $dy;

                if ($nx < 0 || $nx >= self::GRID_WIDTH) continue;
                if ($ny < 0 || $ny >= self::GRID_HEIGHT) continue;

                $idx[] = $nx * self::GRID_HEIGHT + $ny;
            }
        }
        return $idx;
    }
}

