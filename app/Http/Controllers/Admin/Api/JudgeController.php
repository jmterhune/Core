<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Attorney;
use App\Models\Judge;
use Illuminate\Http\Request;

/**
 * Class AttorneyCrudController
 * @package App\Http\Controllers\Admin
 *
 */
class JudgeController extends Controller
{

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function index(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term)
        {
            $results = Judge::where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = Judge::paginate(10);
        }

        return $results;
    }

}
