<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Court;
use App\Models\Holiday;
use App\Models\Judge;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Shared\Converter;
use App\Models\UserDefinedFields;


class DocketController extends Controller
{
    public function index(){

        // Determining which courts current logged in user has access to

        if(backpack_user()->hasRole(['System Admin'])){
            $courts = Court::has('judge')->with('judge')->get();
        } else{
            $courts = Court::with('judge')->whereIn('id',Auth::user()->courts())->get();
        }

        $categories = Category::all()->sortBy('description');

        return view('admin.docket', ['courts' => $courts, 'categories' => $categories]);
    }
    public function trimSpcialChar($string){
            //$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

       // return preg_replace('/[^A-Za-z0-9\-\.\@\ ]/', '', $string); // Removes special chars.
      return $cleaned_string = preg_replace('/[^A-Za-z0-9\-\.\@\/ ]/', '', $string);

    }

    public function print(Request $request){
	    $hearings = [];
	     $court = Court::where('id', $request->court)->first();

         $court->category_print = ($request->category_print == 1 ) ? 1 : 0;


//         $court->custom_header = !empty($request->mainHeader) ? $request->mainHeader : '';
//         $court->save();


        /*if(isset($request->to)){
            $timeslots = Court::find($request->court)->timeslots
                ->whereBetween('start',[Carbon::create($request->from)->hour(8), Carbon::create($request->to)->hour(17)]);
        } else{
            $timeslots = Court::find($request->court)->timeslots
                ->whereBetween('start', [Carbon::create($request->from)->hour(8), Carbon::create($request->from)->hour(17)]);
        }
        foreach($timeslots->sortBy('start') as $timeslot){
            if($request->hearing === "addon"){
                $events = $timeslot->events->where('addon',true);
            } elseif($request->hearing === "noaddon"){
                $events = $timeslot->events->where('addon',false);
            } else{
                $events = $timeslot->events;
            }

            if($events->isEmpty()){

                $date = new Carbon($timeslot->date);

                $holiday = Holiday::whereDate('date',$date)->first();

                if($holiday != null){
                    $description = $holiday->name;
                } else{
                    $description = $timeslot->description ?? 'Not Available';
                }

                $hearings[Carbon::create($timeslot->start)->toDateString()][] = [
			        'start_time' => $timeslot->start_time,
			        'end_time' => $timeslot->end_time,
                    'duration' => $timeslot->length,
		            'description' => $description,
		            'blocked' => $timeslot->blocked,
                    'public_block' => $timeslot->public_block ,
                    'block_description' => $timeslot->description
                ];
            } else{
                foreach($events as $event){
                    $hearings[Carbon::create($timeslot->start)->toDateString()][] =
                    [
                        'start_time' => $event->timeslot->start_time,
                        'end_time' => $event->timeslot->end_time,
                        'blocked' => $event->timeslot->blocked,
                        'public_block' => $event->timeslot->public_block,
                        'block_description' => $event->timeslot->description,
                        'duration' => $event->timeslot->length,
                        'case_num' => $event->case_num,
			            'motion' => ($event->motion_id == 221 ? $this->trimSpcialChar($event->custom_motion) :  $this->trimSpcialChar($event->motion->description)) ,
                        'hearing_type' => $event->type->name ?? null,
                        'plaintiff' => $this->trimSpcialChar($event->plaintiff),
                        'defendant' => $this->trimSpcialChar($event->defendant),
                        'plaintiff_attorney' => $event->attorney->name ?? null,
                        'defendant_attorney' => $event->opp_attorney->name ?? null,
                        'plaintiff_attorney_phone' => $event->attorney->phone ?? null,
                        'defendant_attorney_phone' => $event->opp_attorney->phone ?? null,
                        'category' => $event->timeslot->category->description ?? null,
                        'notes' => $event->notes,
                        'user_defined_fields' => json_encode($event->template) ?? null
                    ];

                }
            }

	}*/
	if(isset($request->to)){
            $period = CarbonPeriod::create($request->from, '1 day', $request->to);

        } else{
            $period = CarbonPeriod::create($request->from, '1 day', $request->from);
        }
        foreach ($period as $key => $date){

            if($request->category != 0){
                $timeslots = Court::find($request->court)->timeslots->where('category_id', $request->category)
                    ->whereBetween('start',[Carbon::create($date)->subDays(1)->hour(22), Carbon::create($date)->hour(17)]);
            } else{
                $timeslots = Court::find($request->court)->timeslots
                    ->whereBetween('start',[Carbon::create($date)->subDays(1)->hour(22), Carbon::create($date)->hour(17)]);
            }

            $holiday = Holiday::whereDate('date',$date)->first();
            if($timeslots->isEmpty())
            {
                if($holiday != null){
                    $description = $holiday->name;
                } else{
                    $description = 'Not Available';
                }

                $hearings[Carbon::create($date)->toDateString()][] = [
                    'start_time' => Carbon::create($date)->hour(8)->format('g:i a'),
                    'end_time' => Carbon::create($date)->hour(8)->format('g:i a'),
                    'duration' => "0 min",
                    'description' => $description,
                    'blocked' => 1,
                    'public_block' => 1,
                    'block_description' => $description
                ];
            }
            else{
                foreach($timeslots->sortBy('start') as $timeslot){
                    if($request->hearing === "addon"){
                        $events = $timeslot->events->where('addon',true);
                    } elseif($request->hearing === "noaddon"){
                        $events = $timeslot->events->where('addon',false);
                    } else{
                        $events = $timeslot->events;
                    }

                    if($events->isEmpty()){

                        if($holiday != null){
                            $description = $holiday->name;
                        } else{
                            $description = $timeslot->description ?? 'Not Available';
                        }

                        $hearings[Carbon::create($timeslot->start)->toDateString()][] = [
                            'start_time' => $timeslot->start_time,
                            'end_time' => $timeslot->end_time,
                            'duration' => $timeslot->length,
                            'description' => $description,
                            'blocked' => $timeslot->blocked,
                            'public_block' => $timeslot->public_block ,
                            'block_description' => $timeslot->description
                        ];
                    } else{
                        foreach($events as $event){
                            $hearings[Carbon::create($timeslot->start)->toDateString()][] =
                            [
                                'start_time' => $event->timeslot->start_time,
                                'end_time' => $event->timeslot->end_time,
                                'blocked' => $event->timeslot->blocked,
                                'public_block' => $event->timeslot->public_block,
                                'block_description' => $event->timeslot->description,
                                'duration' => $event->timeslot->length,
                                'case_num' => $event->case_num,
                                'motion' => ($event->motion_id == 221 ? $event->custom_motion :  $event->motion->description) ,
                                'hearing_type' => $event->type->name ?? null,
                                'plaintiff' => $event->plaintiff,
                                'defendant' => $event->defendant,
                                'plaintiff_attorney' => $event->attorney->name ?? null,
                                'defendant_attorney' => $event->opp_attorney->name ?? null,
                                'plaintiff_attorney_phone' => $event->attorney->phone ?? null,
                                'defendant_attorney_phone' => $event->opp_attorney->phone ?? null,
                                'category' => $event->timeslot->category->description ?? null,
                                'notes' => $event->notes,
                                'user_defined_fields' => json_encode($event->template) ?? null
                            ];

                        }
                    }

                }
            }


        }
	// dd($hearings);
	\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->getCompatibility()->setOoxmlVersion(15);
        $headerList = explode("\n", $court->custom_header);

        foreach($hearings as $key => $hearing)
        {

            $section = $phpWord->addSection(['pageSizeW' => Converter::inchToTwip(8.5), 'pageSizeH' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(11)]);
            $header = $section->addHeader();
            // $header->addText('Judicial Automated Calendaring System',['size' => 14, 'bold' => true],['alignment' => 'center']);
            // $header->addText('Judge ' . Court::find($request->court)->judge->name . ' ' . Court::find($request->court)->description,['size' => 14, 'bold' => true],['alignment' => 'center']);
            if(empty($court->custom_header)){
                $header->addText('Judicial Automated Calendaring System',['size' => 14, 'bold' => true],['alignment' => 'center']);
            }else{
                foreach($headerList as $headerText){
                    $header->addText($headerText,['size' => 14, 'bold' => true],['alignment' => 'center']);
                }
            }
            $header->addText(Carbon::create($key)->format('l\\, F j\\, Y'),['size' => 14, 'bold' => true],['alignment' => 'center']);

           // $table = $section->addTable(['alignment' => 'center', 'width' => 5000, 'unit' => 'pct', 'cellMargin' => 100, 'borderTopSize' => 1, 'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED]);
            $lineStyle = array('weight' => 1, 'width' => 0, 'height' => 0, 'color' => '38c172');
            foreach($hearing as $item){
                  $section->addLine($lineStyle);
                $table = $section->addTable(['alignment' => 'center', 'width' => 5000, 'unit' => 'pct', 'cellSpacing' => Converter::inchToTwip(.01), 'cellMargin' => Converter::inchToTwip(.01),'cellPadding' => Converter::inchToTwip(.01) ,'borderTopSize' => 1, 'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED]);

		  if(!empty($item['case_num'])|| $item['public_block'] == 1   ){
                $table->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
                $timeDurationCell = $table->addCell(null,['valign' => 'top', 'cantSplit' => true]);
                $textrun = $timeDurationCell->addTextRun(['keepNext' => true]);


                if(!isset($item['case_num'])){

			  $textrun->addText($item['start_time'],['bold' => true],['alignment' => 'rignt', 'keepNext' => true]);
		//	$textrun->addText($item['start_time'].'-'.$item['end_time'],['bold' => true],['alignment' => 'rignt', 'keepNext' => true]);
                    $textrun->addText('',['italic' => true], ['keepNext' => true]);
                    $timeDurationCell->getStyle()->setGridSpan(3);

                    $table->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);

                    $noHearingCell = $table->addCell(null,['valign' => 'center', 'cantSplit' => true]);
                    $noHearingCell->addText($item['description'],['bold' => true],['alignment' => 'center', 'keepNext' => true]);
                    $noHearingCell->getStyle()->setGridSpan(3);


                }
                else{

                    $textrun->addText($item['start_time'],['bold' => true],['alignment' => 'left', 'spaceAfter' => 2, 'keepNext' => true]);
                //	$textrun->addText($item['start_time'].'-'.$item['end_time'],['bold' => true],['alignment' => 'left', 'spaceAfter' => 2, 'keepNext' => true]);
                    $textrun->addText(' (' . $item['duration'] .')',[],['italic' => true, 'spaceAfter' => 2, 'keepNext' => true]);
                    if($item['public_block'] == 1){
                        $timeDurationCell->addText($item['block_description'],['bold' => true],['alignment' => 'left', 'spaceAfter' => 2, 'keepNext' => true]);
                    }
                    $timeDurationCell->addText($item['hearing_type'],[], ['spaceAfter' => 2, 'keepNext' => true]);

                    $caseNumberCell = $table->addCell(null,['spaceAfter' => 0]);
                    $caseNumberCell->addText('Case',['bold' => true, 'allCaps' => true], ['spaceAfter' => 2, 'keepNext' => true]);
                    $caseNumberCell->addText($item['case_num'],[],['spaceAfter' => 0, 'keepNext' => true]);

                    $motionCell = $table->addCell(null);
                    $motionCell->addText('Motion',['bold' => true, 'allCaps' => true],['spaceAfter' => 2, 'keepNext' => true]);
                    $motionCell->addText($item['motion']);

                    $table->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);

                    $plaintiffCell = $table->addCell();
                    $plaintiffCell->addText($item['plaintiff'],null, ['keepNext' => true]);
                    $plaintiffCell->addText($item['plaintiff_attorney'],[],['spaceAfter' => 2, 'keepNext' => true]);
                   // $plaintiffCell->addText($item['plaintiff_attorney_phone'],null, [ 'cantSplit', 'keepNext' => true]);
                    $plaintiffCell->addText(preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $item['plaintiff_attorney_phone']),null, [ 'cantSplit', 'keepNext' => true]);
                    $vsCell = $table->addCell();
                    $vsCell->addText('vs.', [],['alignment' => 'center', 'keepNext' => true]);

                    $defendantCell = $table->addCell();
                    if($item['defendant'] != null){
                        $defendantCell->addText($item['defendant'],[],['keepNext' => true]);
                    }
                    $defendantCell->addText($item['defendant_attorney'],[],['spaceAfter' => 2, 'keepNext' => true]);
                   // $defendantCell->addText($item['defendant_attorney_phone'],null, [ 'cantSplit', 'keepNext' => true]);
                    $defendantCell->addText(preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $item['defendant_attorney_phone']),null, [ 'cantSplit', 'keepNext' => true]);
                //    $table->addRow(null, ['cantSplit' => true]);
                // $table->addRow(null, ['cantSplit' => true]);
                   if( isset($item['user_defined_fields']) && $item['user_defined_fields']!= null ){
                      $user_defined_data= json_decode(json_decode($item['user_defined_fields']),true);
                       if(is_array($user_defined_data)) {
                           $i = 0;
                           foreach ($user_defined_data as $key => $defined_data) {
                               $field_name =explode("_|", $key)[0];
                            //   $display_on_docket=UserDefinedFields::where('field_name',$field_name)->first(['display_on_docket']);
				$display_on_docket = UserDefinedFields::where('court_id',$request->court)->whereIn('field_name', [$field_name,preg_replace('/[0-9]+$/', '', $field_name)])->first(['display_on_docket', 'field_type']);
			     //  if( isset($item['display_on_docket']) && $display_on_docket->display_on_docket == 1){
			     if($display_on_docket &&  $display_on_docket->display_on_docket == 1){
			       $cleaned_key = $this->trimSpcialChar(explode("_|", $key)[0]);
                                   // $cleaned_key = preg_replace('/[;|&]', '', $cleaned_key);
			     // $cleaned_key = preg_replace('/[;|&]/', '', $cleaned_key);
			    //  $cleaned_key = preg_replace('/[;|&\/]/', '', $cleaned_key);
                              $cleaned_key = preg_replace('/[^\w\s:,\']/', '', $cleaned_key);
			       if(!empty($cleaned_key) && !empty($defined_data)){
                                       if ($i % 3 === 0) {
                                           $table->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
				       }
				 // if ($cleaned_key === 'Date') {
               // $defined_data = str_replace('/', '-', $defined_data);
            //
                                       //    else if($i==$i+){
                                       //     $table->addRow(null, ['cantSplit' => true]);
                                       //    }
                                         $defined_data_value = ($display_on_docket->field_type == 'DATE' && strtotime($defined_data) !== false)
   					 ? Carbon::parse($defined_data)->format('m-d-Y')
  					  : $defined_data;

				       $alignment = "'" . strtolower($this->trimSpcialChar(explode("_|", $key)[1])) . "'";
                                       $plaintiffCell112 = $table->addCell(null, ['valign' => 'center']);
                                       $plaintiffCell112->addText(preg_replace('/[0-9]+$/', '', explode("_|", $key)[0]), ['bold' => true], ['alignment' => $alignment, 'cantSplit' => true, 'keepNext' => true, 'spaceAfter' => 0]);
                                       $plaintiffCell112->addText($defined_data_value, [], ['spaceAfter' => 0, 'cantSplit' => true, 'keepNext' => true]);

                                       $i++;
                                    }
                               }
                           }
                       }
                   }
    //                    $tem=json_decode($hearing[0]['user_defined_fields'],true);

                        // print_r( json_decode($tem,true));exit;
                        // exit;

                    if($request->category_print){
                        if(isset($item['category'])){
                            $table->addRow(null, ['tblHeader' => true,'cantSplit' => true]);

                            $categoryCell = $table->addCell();
                            $categoryCell->addText('category', ['bold' => true, 'allCaps' => true,'alignment'=>'left'],['spaceAfter' => 0, 'keepNext' => true]);
                            $categoryCell->addText($item['category'],'',['spaceAfter' => 0, 'keepNext' => true]);
                            $categoryCell->getStyle()->setGridSpan(3);
                        }
                    }

                    if(isset($item['notes'])) {
                        $cleaned_notes = preg_replace('/[;|&]/', '', $item['notes']);
                        $table->addRow(null, ['tblHeader' => true,'cantSplit' => true]);
                        $notesCell = $table->addCell();
                        $notesCell->addText('Notes', ['bold' => true, 'allCaps' => true, 'alignment' => 'left', 'keepNext' => true]);
                        $notesCell->addText($cleaned_notes,[],['keepNext' => true]);
                        $notesCell->getStyle()->setGridSpan(3);
                    }
                }

             }
	    }

            $table->addRow(null, ['tblHeader' => true,'cantSplit' => true]);

            $bottomBorder = $table->addCell(null,['valign' => 'center']);
            $bottomBorder->getStyle()->setGridSpan(3);
            $bottomBorder->getStyle()->setBorderBottomSize(1);

            $footer = $section->addFooter();
            $footer->addPreserveText("Page {PAGE} of {NUMPAGES}",[],['alignment' => 'center']);
        }

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

        $sanitized_path = str_replace('/','-', Court::find($request->court)->description);

        $objWriter->save(public_path('storage/'. $sanitized_path . "-".date("Y-m-d").'.docx'));

        return Storage::download('public/' . $sanitized_path . "-".date("Y-m-d").'.docx');
    }
}
