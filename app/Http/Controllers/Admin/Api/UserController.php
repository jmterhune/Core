<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Attorney;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Class AttorneyCrudController
 * @package App\Http\Controllers\Admin
 *
 */
class UserController extends Controller
{

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function index(Request $request)
    {
        $search_term = $request->input('q');

        $results = User::where('name', 'LIKE', '%'.$search_term.'%')->get()->pluck('name','id');

        return $results;
    }

}
