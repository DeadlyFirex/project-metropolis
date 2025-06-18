<?php

namespace App\Http\Controllers;

use App\Mail\ModuleAangemaaktMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;

class ModuleHandlerController extends Controller
{
    /**
     * Display the module management page with all modules and categories.
     */
    public function index()
    {
        $modules = $this->getAllModules();
        $categories = $this->getAllCategories();
        return view('handle_module', compact('modules', 'categories'));
    }

    /**
     * Retrieve all modules that are not soft-deleted.
     */
    private function getAllModules()
    {
        return Module::withoutTrashed()->get();
    }

    /**
     * Return predefined list of module categories.
     */
    private function getAllCategories()
    {
        return ['Veiligheid', 'Recreatie', 'Milieukwaliteit', 'Voorzieningen', 'Mobiliteit'];
    }

    /**
     * Soft delete a module.
     */
    public function destroy(Module $module)
    {
        $module->delete();
        return redirect()->route('module.index')->with('success', 'Module succesvol verwijderd.');
    }

    /**
     * Store a new module and send a notification email.
     */
    public function store(Request $request)
    {
        // Validate input data
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:modules,name',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Create the module instance
        $module = new Module([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'image_path' => 'default-image.png', // default image if none is uploaded
        ]);

        // If an image was uploaded, store it
        if ($request->hasFile('image')) {
            $module->image_path = $request->file('image')->store('modules', 'public');
        }

        // Save the module
        $module->save();

        // Send an email (to Mailtrap for dev; replace address in production)
        Mail::to('test@voorbeeld.nl')->send(new ModuleAangemaaktMail($module));

        // Create default "effects" for each type
        $types = ['safety', 'recreation', 'climate', 'facilities', 'infrastructure'];
        foreach ($types as $type) {
            $module->effects()->create([
                'type' => $type,
                'value' => 0
            ]);
        }

        return redirect()->route('module.index')->with('success', 'Module toegevoegd!');
    }

    /**
     * Update an existing module by ID.
     */
    public function update(Request $request, $id)
    {
        // Find the module by ID
        $module = Module::findOrFail($id);

        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update fields
        $module->name = $validated['name'];
        $module->description = $validated['description'];
        $module->category = $validated['category'];

        // Handle image upload if a new one is provided
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('modules', 'public');
            $module->image_path = $imagePath;
        }

        // Save changes
        $module->save();

        return redirect()->route('module.index')->with('success', 'Module bijgewerkt');
    }

    /**
     * Bulk delete modules by their IDs (via AJAX).
     */
    public function bulkDestroy(Request $request)
    {
        // Validate input: must be an array of existing module IDs
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:modules,id',
        ]);

        // Soft delete all modules with the given IDs
        Module::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Modules succesvol verwijderd.'], 200);
    }
}
