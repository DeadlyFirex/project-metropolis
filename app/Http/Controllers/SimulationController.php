<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;

class SimulationController extends Controller
{
    public function index(Request $request){
        $category = $request->input('category');
        $modules = $this->getModules($category);
        $categories = Module::select('category')->distinct()->pluck('category');

        return view("sim_dashboard", compact('modules', 'category', 'categories'));
    }

    private function getModules($category = null){
        if ($category) {
            return Module::where('category', $category)->get();
        }
        return Module::all();
    }
}
