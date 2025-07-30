<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
// use App\Http\Requests\TimeslotRequest;
use App\Models\MediationCaseEventPayments;
use App\Models\MediationCases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MediationCaseEventPaymentsController extends Controller
{
    public function index($caseNo)
    {
        $case = MediationCases::with(['PltfAttroney','DefAttroney','events','payments.event'])
        ->where('id',$caseNo)->first();
        // echo json_encode($case->payments[0]->event->e_sch_datetime);exit;
        return view('admin.payments',
                [
                    "case" => $case
                ]);
    }

    public function addPayment(Request $request)
    {
        $payment = new MediationCaseEventPayments;
        $payment->p_c_id = $request->p_c_id;
        $payment->p_e_id = $request->p_e_id;
        $payment->amount_paid = $request->amount_paid;
        $payment->paid_by = $request->paid_by;
        $payment->paid_on = date('Y-m-d', strtotime($request->paid_on));
        $payment->save();
        Session::flash('message', 'Payment Added Successfully!'); 
        Session::flash('alert-class', 'alert-success'); 
        return true;
    }

    public function editPayment($paymentId)
    {
        $payment = MediationCaseEventPayments::find($paymentId);
        return $payment;
    }

    public function updatePayment(Request $request)
    {
        $payment = MediationCaseEventPayments::find($request->payment_id);
        $payment->p_e_id = $request->p_e_id;
        $payment->amount_paid = $request->amount_paid;
        $payment->paid_by = $request->paid_by;
        $payment->paid_on = date('Y-m-d', strtotime($request->paid_on));
        $payment->save();
        
        Session::flash('message', 'Payment updated Successfully!'); 
        Session::flash('alert-class', 'alert-success'); 
        return true;
    }

    public function paymentDelete(Request $request)
    {
        $payment = MediationCaseEventPayments::find($request->payment_id);
        $payment->delete();
        Session::flash('message', 'Payment deleted Successfully!'); 
        Session::flash('alert-class', 'alert-success'); 
        return true;
    }

    //Family case payments

    public function familyindex($caseNo)
    {
        $case = MediationCases::with(['PltfAttroney','DefAttroney','events','payments.event'])
        ->where('id',$caseNo)->first();
        // echo json_encode($case->payments[0]->event->e_sch_datetime);exit;
        return view('admin.familypayments',
                [
                    "case" => $case
                ]);
    }
}
