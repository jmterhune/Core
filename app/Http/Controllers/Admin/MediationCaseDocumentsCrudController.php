<?php

namespace App\Http\Controllers\Admin;
use App\Models\MediationCases;
use App\Models\MediationDocuments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Storage;

class MediationCaseDocumentsCrudController extends Controller
{
    public function index($caseId)
    {
        $case = MediationCases::with(['events.medmaster', 'events.outcome'])
            ->where('id', $caseId)
            ->first();

        $documents = MediationDocuments::get();

        $dir = storage_path('app/mediation/documents/');
        $files =  scandir ($dir);
        $match = $case->c_caseno;
        $case_documents = array();
        foreach ($files as $file) 
        {
            if(stripos($file, $match) !== false)
            {
                $case_documents[]=$file;
            }
        }
        return view('admin.case_documents',["case"=>$case,"documents" => $documents,"case_documents" => $case_documents]);
    }

    public function buildCaseDocument(Request $request)
    {
        $document = MediationDocuments::find($request->documentId);
        // dd(storage_path('app/mediation/documents/'.$document->d_original));exit;
        $rawData = IOFactory::load(storage_path('app/mediation/documents/'.$document->d_original));
        $caseDetails = MediationCases::where('id', $request->caseId)
                        ->first();

                        
        $phpword = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('app/mediation/documents/'.$document->d_original));
        foreach($caseDetails as $key=>$value)
        {
            $phpword->setValue('${'.$key.'}',$value);
        }

        $phpword->saveAs(storage_path('app/mediation/documents/'.$caseDetails->c_caseno."_".$document->d_fname.'.'.$document->d_ext));
        

        return true;

    }

    public function deleteCaseDocuments(Request $request)
    {
        $caseDetails = MediationCases::select("c_caseno")->where('id', $request->caseId)->first();
        $dir = storage_path('app/mediation/documents/');
        $files =  scandir ($dir);
        $match = $caseDetails->c_caseno;
        $case_documents = array();
        foreach ($files as $file) 
        {
            if(stripos($file, $match) !== false)
            {
                $case_documents[]=storage_path('app/mediation/documents/').$file;
                unlink(storage_path('app/mediation/documents/').$file);
            }
        }
        return true;
    }

}

