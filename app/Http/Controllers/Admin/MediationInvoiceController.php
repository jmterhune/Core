<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MediationCaseConfirmation;
use App\Mail\MediationEmailInstructions;
use App\Models\Category;
use App\Models\County;
use App\Models\Court;
use App\Models\MediationCases;
use App\Models\MediationEvents;
use App\Models\MediationInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use Prologue\Alerts\Facades\Alert;

class MediationInvoiceController extends Controller
{


    public function generateInvoice(Request $request){

        $invoice = new TemplateProcessor(public_path('storage/blank_invoice.docx'));

        $mediation_event = MediationEvents::find($request->event_id);

        $mediation_case = $mediation_event->case;

        $county = substr($mediation_case->c_caseno,0,2) == '59' ? 'Seminole' : 'Brevard';

        $case_type = $mediation_case->form_type == 'f-form' ? 'family' : 'civil';

        $first_party = $mediation_case->parties->whereIn('type',['plaintiff', 'petitioner']);
        $second_party = $mediation_case->parties->wherein('type',['defendant', 'respondent']);


        $invoice->setValues([
            'case_num' => $mediation_case->c_caseno,
            'plaintiff' => implode(',', $first_party->pluck('name')->all()),
            'defendant' => implode(',', $second_party->pluck('name')->all()),
            'type' => Str::title($case_type),
            'date' => $mediation_event->e_sch_datetime,
            'fee' => $mediation_event->e_med_fee/2,
            'credit_payment_instructions' => 'Please call the phone center of the Brevard County Clerk of Court at 321-637-5413, select prompt “3” for civil, and then listen to prompts for next available deputy clerk.  They will assist you in processing your payment. Please keep in mind that an additional processing fee will be charged if you utilize this method.',
            'county' => Str::upper($county),
            'county_payment_mail_address' => 'Brevard County Civil Mediation <w:br/> Accounts Receivable - 2nd Floor. <w:br/> 2825 Judge Fran Jamieson Way <w:br/> Viera, FL  32940-8006',
            'phone' => '321-635-5065'
        ]);

        $sanitized_path = str_replace('/','-', 'Test Invoice');

        $pathToSave = public_path('storage/'. $sanitized_path . "-".date("Y-m-d").'.docx');

        $invoice->saveAs($pathToSave);

        return Storage::download('public/' . $sanitized_path . "-".date("Y-m-d").'.docx');

        //dd($request->all());

        //return redirect()->route('mediation.edit', $case->id);
    }
}
