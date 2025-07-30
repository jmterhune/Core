<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Court;
use App\Models\CourtTimeslots;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;


class CourtEventsController extends Controller
{
    public function index(){
        return view('admin.cal');
    }

    public function show(Court $court_event){
        return $court_event->events->toJson();
    }

    public function store(Request $request){


    }

    public function create(){

    }
}
