<?php

namespace App\Http\Controllers;

use App\Models\Condition;
use App\Models\Module;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Controller voor het beheren van categorievoorwaarden (Conditions),
 * inclusief CRUD-acties en spelregels rondom plaatsing van modules.
 */
class ConditionsController extends Controller
{
    // Afmetingen van het grid (voor buurcontrole)
    private const GRID_WIDTH  = 3;
    private const GRID_HEIGHT = 4;

    /* ───────── Dashboard ───────── */

    /**
     * Toon het dashboard met alle condities.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('conditions_dashboard', [
            'conditions' => Condition::orderBy('category')->get(),
        ]);
    }

    /* ───────── CRUD ───────── */

    /**
     * Sla een nieuwe conditie op.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category'        => 'required|string|max:50|unique:conditions,category',
            'max'             => 'required|integer|between:1,12',
            'incompatible'    => 'nullable|array',
            'incompatible.*'  => [
                'string',
                Rule::notIn([$request->input('category')]), // Mag niet zichzelf blokkeren
            ],
        ]);

        Condition::create([
            'category'     => $data['category'],
            'max'          => (int) $data['max'],
            'incompatible' => $data['incompatible'] ?? [],
        ]);

        return back()->with('status', 'Categorie toegevoegd');
    }

    /**
     * Werk een bestaande conditie bij.
     *
     * @param  Request  $request
     * @param  Condition  $condition
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Condition $condition)
    {
        $rules = [];

        // Alleen valideren wat aanwezig is
        if ($request->has('max')) {
            $rules['max'] = 'integer|between:1,12';
        }

        if ($request->has('incompatible')) {
            $rules['incompatible']    = 'array';
            $rules['incompatible.*']  = [
                'string',
                Rule::notIn([$condition->category]), // Geen zelfblokkade
            ];
        }

        $data = $request->validate($rules);

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

    /**
     * Verwijder een conditie.
     *
     * @param  Condition  $condition
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Condition $condition)
    {
        $condition->delete();
        return back()->with('status', 'Categorie verwijderd');
    }

    /* ───────── Spelregels ───────── */

    /**
     * Controleert of het maximum aantal modules van een categorie overschreden wordt.
     *
     * @param  Module  $newModule
     * @param  Slot  $targetSlot
     * @return bool
     */
    public function exceedsCategoryLimit(Module $newModule, Slot $targetSlot): bool
    {
        $condition = Condition::where('category', $newModule->category)->first();
        if (!$condition) return false;

        $limit = $condition->max;

        // Tel bestaande modules van deze categorie, behalve op targetSlot
        $count = Slot::whereHas(
            'module',
            fn($q) => $q->where('category', $newModule->category)
        )
            ->where('id', '!=', $targetSlot->id)
            ->count();

        // Bestaande module op dezelfde plek van dezelfde categorie overschrijft niet
        $replacingSame =
            $targetSlot->module_id &&
            $targetSlot->module &&
            $targetSlot->module->category === $newModule->category;

        return !$replacingSame && ($count + 1) > $limit;
    }

    /**
     * Controleert of een module naast een incompatibele categorie wordt geplaatst.
     *
     * @param  Module  $newModule
     * @param  Slot  $targetSlot
     * @return bool
     */
    public function violatesAdjacencyRule(Module $newModule, Slot $targetSlot): bool
    {
        $condition = Condition::where('category', $newModule->category)->first();
        if (!$condition) return false;

        // Decodeer JSON string of pak array
        $blocked = is_string($condition->incompatible)
            ? (json_decode($condition->incompatible, true) ?: [])
            : ($condition->incompatible ?? []);

        if (empty($blocked)) return false;

        $index = $targetSlot->index;
        if ($index === null) return false;

        $adjacent = $this->neighbourIndexes($index);

        // Haal de categorieën van aangrenzende slots op
        $adjacentCats = Slot::query()
            ->join('modules', 'modules.id', '=', 'slots.module_id')
            ->whereIn('slots.index', $adjacent)
            ->pluck('modules.category')
            ->unique()
            ->all();

        // Kijk of er een conflict is met incompatibele categorieën
        return !empty(array_intersect($adjacentCats, $blocked));
    }

    /* ───────── Helpers ───────── */

    /**
     * Bepaal welke indexes aangrenzend zijn aan een bepaalde index in het grid.
     *
     * @param  int  $index
     * @return array
     */
    protected function neighbourIndexes(int $index): array
    {
        $x = intdiv($index, self::GRID_HEIGHT);
        $y =  $index % self::GRID_HEIGHT;

        $idx = [];

        foreach ([-1, 0, 1] as $dx) {
            foreach ([-1, 0, 1] as $dy) {
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
