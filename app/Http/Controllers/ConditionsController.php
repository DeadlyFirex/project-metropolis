<?php

namespace App\Http\Controllers;

use App\Models\Condition;
use App\Models\Module;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConditionsController extends Controller
{
    private const GRID_WIDTH  = 3;
    private const GRID_HEIGHT = 4;

    /* ───────── Dashboard ───────── */

    public function index()
    {
        return view('conditions_dashboard', [
            'conditions' => Condition::orderBy('category')->get(),
        ]);
    }

    /* ───────── CRUD ───────── */

    /** nieuw of eerste keer opslaan */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category'        => 'required|string|max:50|unique:conditions,category',
            'max'             => 'required|integer|between:1,12',
            'incompatible'    => 'nullable|array',
            'incompatible.*'  => [
                'string',
                Rule::notIn([$request->input('category')]),
            ],
        ]);

        Condition::create([
            'category'     => $data['category'],
            'max'          => (int) $data['max'],
            'incompatible' => $data['incompatible'] ?? [],
        ]);

        return back()->with('status', 'Categorie toegevoegd');
    }

    /** aanpassen bestaande regel */
    public function update(Request $request, Condition $condition)
    {
        // alleen valideren wat werkelijk binnenkomt
        $rules = [];
        if ($request->has('max')) {
            $rules['max'] = 'integer|between:1,12';
        }
        if ($request->has('incompatible')) {
            $rules['incompatible']    = 'array';
            $rules['incompatible.*']  = [
                'string',
                Rule::notIn([$condition->category]),
            ];
        }

        $data = $request->validate($rules);

        /* alleen de velden die werkelijk aanwezig zijn bijwerken */
        $updates = [];
        if (array_key_exists('max', $data)) {
            $updates['max'] = (int) $data['max'];
        }
        if (array_key_exists('incompatible', $data)) {
            $updates['incompatible'] = $data['incompatible'];
        }

        if ($updates) {
            $condition->update($updates);
        }

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
        if (!$condition) return false;

        $limit = $condition->max;

        $count = Slot::whereHas('module',
            fn ($q) => $q->where('category', $newModule->category))
            ->where('id', '!=', $targetSlot->id)
            ->count();

        $replacingSame =
            $targetSlot->module_id &&
            $targetSlot->module &&
            $targetSlot->module->category === $newModule->category;

        return !$replacingSame && ($count + 1) > $limit;
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

        $adjacentCats = Slot::query()
            ->join('modules', 'modules.id', '=', 'slots.module_id')
            ->whereIn('slots.index', $adjacent)
            ->pluck('modules.category')
            ->unique()
            ->all();

        return !empty(array_intersect($adjacentCats, $blocked));
    }


    /* ───────── Helpers ───────── */

    private function neighbourIndexes(int $index): array
    {
        $x = intdiv($index, self::GRID_HEIGHT);
        $y =  $index % self::GRID_HEIGHT;

        $idx = [];
        foreach ([-1,0,1] as $dx) {
            foreach ([-1,0,1] as $dy) {
                if ($dx === 0 && $dy === 0) continue;
                $nx = $x + $dx;
                $ny = $y + $dy;
                if ($nx < 0 || $nx >= self::GRID_WIDTH)  continue;
                if ($ny < 0 || $ny >= self::GRID_HEIGHT) continue;
                $idx[] = $nx * self::GRID_HEIGHT + $ny;
            }
        }
        return $idx;
    }
}
