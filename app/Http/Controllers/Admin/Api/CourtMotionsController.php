<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Attorney;
use App\Models\CourtMotions;
use App\Models\Motion;
use Illuminate\Http\Request;

/**
 * Class AttorneyCrudController
 * @package App\Http\Controllers\Admin
 *
 */
class CourtMotionsController extends Controller
{

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function index(Request $request)
    {
        $search_term = $request->input('q');

        $motion_ids = CourtMotions::where('court_id', $search_term)->select('motion_id')->get();

        $motions = Motion::whereIn('id',$motion_ids)->get();

        return $motions;
    }

}
