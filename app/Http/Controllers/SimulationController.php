<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Slot;

class SimulationController extends Controller
{
    public function index(){
        $slots = $this->getAllSlots();
    return view("sim_dashboard", compact('slots'));
    }

    private function getAllSlots() {
        return Slot::all();
    }
}
