<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;

class ModuleHandlerController extends Controller
{
    public function index()
    {
        $modules = $this->getAllModules();
        $categories = $this->getAllCategories();
        return view('handle_module', compact('modules', 'categories'));
    }
    private function getAllModules()
    {
        return Module::all();
    }
    private function getAllCategories()
    {
        return $categories = ['Veiligheid', 'Recreatie', 'Milieukwaliteit', 'Voorzieningen', 'Mobiliteit'];
    }
    public function destroy(Module $module)
    {
        $module->delete();
        return redirect()->route('module.index')->with('success', 'Module succesvol verwijderd.');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:modules,name',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $module = new Module([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'image_path' => 'default-image.png',
        ]);

        if ($request->hasFile('image')) {
            $module->image_path = $request->file('image')->store('modules', 'public');
        }

        $module->save();

        
        $types = ['safety', 'recreation', 'climate', 'facilities', 'infrastructure'];
        foreach ($types as $type) {
            $module->effects()->create([
                'type' => $type,
                'value' => 0
            ]);
        }

        return redirect()->route('module.index')->with('success', 'Module toegevoegd!');
    }


    public function update(Request $request, $id)
    {
        // Vind de module op basis van het ID
        $module = Module::findOrFail($id);

        // Valideer de gegevens
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'image' => 'nullable|image|max:1024', // je kunt de validatie aanpassen op basis van je vereisten
        ]);

        // Werk de module bij
        $module->name = $validated['name'];
        $module->description = $validated['description'];
        $module->category = $validated['category'];

        // Als er een nieuwe afbeelding is, upload deze dan
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('modules', 'public');
            $module->image_path = $imagePath;
        }

        // Sla de wijzigingen op
        $module->save();

        // Redirect naar de lijst met modules of waar je naartoe wilt gaan
        return redirect()->route('module.index')->with('success', 'Module bijgewerkt');
    }
}
