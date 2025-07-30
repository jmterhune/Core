<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\UserDefinedFields;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserDefinedFieldsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($courtId)
    {

        //$court_templates=CourtTemplates::where(['court_id'=>$courtId])->get();

        $court_templates = UserDefinedFields::where(['court_id'=>$courtId])->get()->count();
        if(backpack_user()->hasRole([ 'System Admin'])){
            return view('admin.tem',
                [
                   // 'court_templates' =>  $court_templates,
                    'court' => $courtId,
                    'court_templates_count' => $court_templates,
                    // 'categories' => Category::all()->sortBy('description'),
                    // 'timeslots' => $calendar->timeslots,
                    // 'blocked_timeslots' => $court->timeslots->where('blocked',1)->merge($holidays)
                ]);
        }

    }

    public function fields(Request $request)
    {

        $court_templates=UserDefinedFields::where(['court_id'=>$request->court_id])->get();
        if(backpack_user()->hasRole([ 'System Admin'])){
            return view('admin.template_fields',
                [
                   'court_templates' =>  $court_templates,
                    'court' => $request->court_id,
                    'isType' => ($court_templates->count() > 0) ? "edit" : "new",
                    // 'categories' => Category::all()->sortBy('description'),
                    // 'timeslots' => $calendar->timeslots,
                    // 'blocked_timeslots' => $court->timeslots->where('blocked',1)->merge($holidays)
                ]);
        }



    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // echo count($request->field_name) ;exit;
        //  echo json_encode($request->all());exit;
        for($i=1;$i<=count($request->field_name) ;$i++){
            if($request->field_name[$i]!=null && isset($request->templated_id[$i])){
                $templetes = UserDefinedFields::where('id',$request->templated_id[$i])->first()->update([
                    'court_id' =>$request->court_id,
                    'field_name' =>$request->field_name[$i],
                    'field_type' =>$request->field_type[$i],
                    'alignment' =>$request->alignment[$i],
                    'default_value' =>isset($request->default_value[$i])?$request->default_value[$i]:"",
                    'required' =>isset($request->required[$i])?$request->required[$i]:0,
                    'yes_answer_required' =>isset($request->yes_answer_required[$i])?$request->yes_answer_required[$i]:0,
                    'display_on_docket' =>isset($request->display_on_docket[$i])?$request->display_on_docket[$i]:0,
                    'display_on_schedule' =>isset($request->display_on_schedule[$i])?$request->display_on_schedule[$i]:0,
                    'use_in_attorany_scheduling' =>isset($request->use_in_attorany_scheduling[$i])?$request->use_in_attorany_scheduling[$i]:0
                ]);

            }
            else if($request->field_name[$i]!=null){

                $templetes = UserDefinedFields::create([
                    'court_id' =>$request->court_id,
                    'field_name' =>$request->field_name[$i],
                    'field_type' =>$request->field_type[$i],
                    'alignment' =>$request->alignment[$i],
                    'default_value' => isset($request->default_value[$i])?$request->default_value[$i]:"",
                    'required' =>isset($request->required[$i])?$request->required[$i]:0,
                    'yes_answer_required' =>isset($request->yes_answer_required[$i])?$request->yes_answer_required[$i]:0,
                    'display_on_docket' =>isset($request->display_on_docket[$i])?$request->display_on_docket[$i]:0,
                    'display_on_schedule' =>isset($request->display_on_schedule[$i])?$request->display_on_schedule[$i]:0,
                    'use_in_attorany_scheduling' =>isset($request->use_in_attorany_scheduling[$i])?$request->use_in_attorany_scheduling[$i]:0
                ]);

            }else{
                continue;
            }
        }

        return true;

}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        dd($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request ,$id)
    {

        UserDefinedFields::destroy($request->id);
        return true;
    }
}
