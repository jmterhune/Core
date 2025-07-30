<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Attorney;
use Illuminate\Http\Request;

/**
 * Class AttorneyCrudController
 * @package App\Http\Controllers\Admin
 *
 */
class AttorneyController extends Controller
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
            $results = Attorney::where('name', 'LIKE', '%'.$search_term.'%')
                ->orWhere('bar_num', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = Attorney::paginate(10);
        }

        return $results;
    }

}
