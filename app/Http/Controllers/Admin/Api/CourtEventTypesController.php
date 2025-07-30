<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Attorney;
use App\Models\CourtEventTypes;
use App\Models\CourtMotions;
use App\Models\EventType;
use App\Models\Motion;
use Illuminate\Http\Request;

/**
 * Class AttorneyCrudController
 * @package App\Http\Controllers\Admin
 *
 */
class CourtEventTypesController extends Controller
{

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function index(Request $request)
    {
        $search_term = $request->input('q');

        $event_type_ids = CourtEventTypes::where('court_id', $search_term)->select('event_type_id')->get();

        $event_types = EventType::whereIn('id',$event_type_ids)->get();

        return $event_types;
    }

}

