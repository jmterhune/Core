<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Holiday;

class CourtBlockedTimeslotsController extends Controller
{
    public function index(Court $court){
        $holidays = Holiday::all();
        $holidays->appends=[];

        return $court->timeslots->where('blocked',1)->merge($holidays);
    }

}
