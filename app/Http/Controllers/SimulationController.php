<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;

class SimulationController extends Controller
{
    public function index(){
        $modules = $this->getModules();
        return view("sim_dashboard", compact('modules'));
    }

    private function getModules(){
        $modules = Module::all();
        return $modules;
    }
}
