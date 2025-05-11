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
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        // Sla de module op in de database
        $module = new Module([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'image_path' => 'default-image.png', // standaardwaarde
        ]);

        // Verwerk de afbeelding indien aanwezig
        if ($request->hasFile('image')) {
            $module->image_path = $request->file('image')->store('modules', 'public');
        }

        // Opslaan
        $module->save();

        // Redirect of terug naar de lijst van modules
        return redirect()->route('module.index')->with('success', 'Module toegevoegd!');
    }
}
