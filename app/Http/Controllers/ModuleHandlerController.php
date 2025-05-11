<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;

class ModuleHandlerController extends Controller
{
    public function index(){
        $modules = $this->getAllModules();
        return view('handle_module', compact('modules'));
    }
    private function getAllModules(){
        return Module::all();
    }
}
