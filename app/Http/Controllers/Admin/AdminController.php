<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\Timeslot;
use Carbon\Carbon;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    protected $data = []; // the information we send to the view

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(backpack_middleware());
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        if (backpack_user()->hasRole('Mediator')) {
            return redirect()->route('mediation.create');
        }
        $this->data['title'] = trans('backpack::base.dashboard'); // set the page title
        $this->data['breadcrumbs'] = [
            trans('backpack::crud.admin')     => backpack_url('dashboard'),
            trans('backpack::base.dashboard') => false,
        ];
         $this->data['events'] = Event::whereHas('timeslot.court.judge.JA', function ($query){
            $query->where('user_id', backpack_user()->id);
        })->orderBy('created_at','DESC')->limit(10)->get();

        $this->data['timeslots'] = Timeslot::whereHas('court.judge.JA', function ($query){
            $query->where('user_id', backpack_user()->id);
        })->where('start', '>=', Carbon::now()->startOfDay())->orderBy('start','DESC')->limit(15)->get();

        return view(backpack_view('dashboard'), $this->data);
    }


    /**
     * Redirect to the dashboard.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */

    public function redirect()
    {
        // The '/admin' route is not to be used as a page, because it breaks the menu's active state.
        return redirect(backpack_url('dashboard'));
    }

    /**
     * Show the quickReference document.
     *
     * @return \Illuminate\Http\Response
     */
    public function quickReference()
    {
        $this->data['title'] = 'Quick Refrence'; // set the page title
        $this->data['breadcrumbs'] = [
            trans('backpack::crud.admin')     => backpack_url('quick-reference'),
            'Quick Refrence' => false,
        ];

        return view(backpack_view('quickReference'), $this->data);
    }
}
