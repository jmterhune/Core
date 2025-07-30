<?php

namespace App\Console\Commands;

use App\Models\Attorney;
use App\Models\Category;
use App\Models\Court;
use App\Models\CourtEventTypes;
use App\Models\CourtMotions;
use App\Models\CourtPermission;
use App\Models\CourtTemplateOrder;
use App\Models\CourtTimeslot;
use App\Models\Email;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Judge;
use App\Models\Motion;
use App\Models\Template;
use App\Models\TemplateTimeslot;
use App\Models\Timeslot;
use App\Models\TimeslotEvent;
use App\Models\User;
use App\Models\UserDefinedFields;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JacsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jacs:import
                            {--A|all : Import All}
                            {--c|courts : Import Courts}
                            {--t|templates : Import Templates}
                            {--o|templates_order : Import Templates Order}
                            {--a|attorneys : Import Attorneys}
                            {--T|timeslots : Import Timeslots}
                            {--f|block_reason : Fix Block Reason}
                            {--j|judges : Import Judges}
                            {--m|motions : Import Motions}
                            {--e|events : Import Events}
                            {--C|categories : Import Categories}
                            {--u|user_defined_fields : Import User Defined Fields}
                            {--p|permissions : Import Court Permissions}
                            {--y|fix_motion : Fix Other Motions}
                            {--z|cleanup : Clean up Emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Legacy JACS Data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if($this->option('categories') || $this->option('all')){
            $this->import_categories();
        }

        if($this->option('motions') || $this->option('all')){
            $this->import_motions();
        }

        if($this->option('attorneys')){
            $this->import_attorneys();
        }

        if($this->option('courts') || $this->option('all')){
            $this->import_courts();
        }

        if($this->option('judges') || $this->option('all')){
            $this->import_judges();
        }

        if($this->option('templates') || $this->option('all')){
            $this->import_templates();
        }

        if($this->option('templates_order') || $this->option('all')){
            $this->import_templates_order();
        }

        if($this->option('timeslots') || $this->option('all')){
            $this->import_timeslots();
        }

        if($this->option('user_defined_fields') || $this->option('all')){
            $this->import_user_defined_fields();
        }

        if($this->option('events') || $this->option('all')){
            $this->import_events();
        }

        if($this->option('permissions') || $this->option('all')){
            $this->import_court_permissions();
        }

        if($this->option('cleanup') || $this->option('all')){
            $this->email_cleanup();
        }

        if($this->option('block_reason')){
            $this->fix_block_reason();
        }

        if($this->option('fix_motion')){
            $this->fix_other_motion();
        }


        return Command::SUCCESS;
    }

    public function import_attorneys(){
        $attorneys = DB::connection('sqlsrv')->table('jacs.TBATTORNEYS')
            ->get();

        $this->newLine();
        $this->info('Importing Legacy JACS Attorneys...');

        $attorneys_bar = $this->output->createProgressBar(count($attorneys));

        foreach ($attorneys as $attorney){

            $new_attorney = Attorney::firstOrNew(['bar_num' => $attorney->BARNUM],
                [
                'name' => $attorney->NAME,
                'bar_num' => $attorney->BARNUM,
                'enabled' => $attorney->ACTIVE == 'Y' && $attorney->EMAIL != '',
                'scheduling' => $attorney->WEBDISABLED != 'N', //TODO: Probably need to be removed, as once account is enabled, there is no option for scheduling
                'phone' => $attorney->PHONENUM ?? null,
                'notes' => $attorney->NOTES,
                //'email' => $attorney->EMAIL == '' ? null : $attorney->EMAIL,
            ]);

            $new_attorney->password = Hash::make($attorney->PASSWORD);

            $new_attorney->save();

            if($attorney->EMAIL != ''){
                $space_str = ["&#61443",":", ";"];
                $repalce_srt   = ["",",",","];
                $attorneyEmail= str_replace($space_str, $repalce_srt, $attorney->EMAIL);

                foreach (explode(',', $attorneyEmail) as $attorney_email){

                    $email = str_replace(',','.', $attorney_email);

                    if($email != ''){

                        Email::create([
                            'email' => ltrim(strtolower($attorney_email)),
                            'emailable_id' => $new_attorney->id,
                            'emailable_type' => 'App\Models\Attorney'
                        ]);

                    }
                }
            }

            $attorneys_bar->advance();
        }

        $attorneys_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported OLD JACS Attorneys!');
    }

    public function import_judges(){

        $judges = DB::connection('sqlsrv')->table('jacs.TBJUDGES')->get();

        $this->newLine();
        $this->info('Importing Legacy JACS Judges...');

        $judges_bar = $this->output->createProgressBar(count($judges));

        foreach ($judges as $judge){

            $title = match ($judge->TITLEID) {
                '1', '0' => 'Judge',
                '2' => 'Mediator',
                '3' => 'Magistrate',
                '4' => 'Case Manager',
            };

            $court = Court::where('old_id', $judge->ASSIGNEDCOURTCODE)->first();

            $new_judge = Judge::firstOrCreate([
                'name' => $judge->JUDGENAME,
                'phone' => $judge->PHONENUM,
                'title' => $title,
                'old_id' => $judge->USERID,
                'court_id' => $court != null ? $court->id : null
            ]);


            if($court != null){

                // Importing Judge's Rules from Legacy JACS
                $judge_rules = DB::connection('sqlsrv')->table('jacs.TBJUDGERULES')
                    ->where('USERID', $new_judge->old_id)->first();

                $court->calendar_weeks = $judge_rules->CALEXTPERIOD;
                $court->auto_extension = $judge_rules->CALTEMPLATE == 'R';
                $court->scheduling = $judge_rules->WEBENABLED =='Y';
                $court->email_confirmations = $judge_rules->EMAILCONFIRM =='Y';
                $court->public_timeslot = $judge_rules->SHOWTIME =='Y';
                $court->public_docket = $judge_rules->SHOWDOCKET =='Y';
                $court->public_docket_days = $judge_rules->DOCKETDAYS;
                $court->lagtime = $judge_rules->MINLAG4FAX;

                $court->save();

                // Importing Judge's Motions

                $judge_motions = DB::connection('sqlsrv')->table('jacs.TBJUDGEMOTIONS')
                    ->where('USERID', $new_judge->old_id)->get();

                foreach ($judge_motions as $motion){

                    $new_motion = Motion::where('old_id', $motion->MOTIONCODE)->first();
                    CourtMotions::firstOrCreate([
                        'court_id' => $court->id,
                        'motion_id' => $new_motion->id
                    ]);
                }
            }

            $judges_bar->advance();
        }

        $judges_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported OLD JACS Judges!');

    }

    public function import_timeslots(){

        $today = Carbon::now()->startOfDay();

        $timeslots = DB::connection('sqlsrv')->table('jacs.TBCOURTCALENDAR')
            ->wherenull('CASENUM')
            ->where('CALDATE', '>=', '2023-05-01')
            ->where('TIMESLOTNUM', 1)
            ->orderBy('CALDATE','desc')
            ->get();

        $this->newLine();
        $this->info('Importing Legacy JACS Timeslots...');

        $days = $timeslots->groupBy(['COURTCODE','CALDATE','TIMETO']);

        $timeslots_bar = $this->output->createProgressBar(count($days));

        foreach($days as $key => $day){
            foreach($day as $times){
                foreach($times as $time){
                    $timeslot_quantity = count($time);

                    if($timeslot_quantity > 1){
                        $category = Category::where('old_id', $time[0]->courtroomid)->first();
                        $template = Template::where('old_id', $time[0]->TEMPLATEID)->first();
                        $court = Court::where('old_id', $time[0]->COURTCODE)->first();

                        $new_timeslot = Timeslot::create(
                            [
                                'end' => date('Y-m-d',strtotime($time[0]->CALDATE))." ".preg_replace('/.{2}(?!$)/', '$0:', $time[0]->TIMETO),
                                'start' => date('Y-m-d',strtotime($time[0]->CALDATE))." ".preg_replace('/.{2}(?!$)/', '$0:', $time[0]->TIMEFROM),
                                'description' => $time[0]->templatedesc != null ? $time[0]->templatedesc : null,
                                'duration' => $time[0]->DURATION,
                                'quantity' => $timeslot_quantity,
                                'blocked' => !($time[0]->BLOCKEDFLAG == "N"),
                                'public_block' => $time[0]->PublicBlock == "Y",
                                'category_id' => $category != null ? $category->id : null,
                                'template_id' => $template != null ? $template->id : null,
                                'block_reason' => $time[0]->BLOCKREASON,
                            ]);

                        CourtTimeslot::firstOrCreate([
                            'court_id' => $court->id,
                            'timeslot_id' => $new_timeslot->id
                        ]);
                    } else{
                        $category = Category::where('old_id', $time[0]->courtroomid)->first();
                        $template = Template::where('old_id', $time[0]->TEMPLATEID)->first();
                        $court = Court::where('old_id', $time[0]->COURTCODE)->first();

                        $new_timeslot = Timeslot::create(
                            [
                                'end' => date('Y-m-d',strtotime($time[0]->CALDATE))." ".preg_replace('/.{2}(?!$)/', '$0:', $time[0]->TIMETO),
                                'start' => date('Y-m-d',strtotime($time[0]->CALDATE))." ".preg_replace('/.{2}(?!$)/', '$0:', $time[0]->TIMEFROM),
                                'description' => $time[0]->templatedesc != null ? $time[0]->templatedesc : null,
                                'duration' => $time[0]->DURATION,
                                'quantity' => 1,
                                'blocked' => !($time[0]->BLOCKEDFLAG == "N"),
                                'public_block' => $time[0]->PublicBlock == "Y",
                                'category_id' => $category != null ? $category->id : null,
                                'template_id' => $template != null ? $template->id : null,
                                'block_reason' => $time[0]->BLOCKREASON,
                            ]);

                        CourtTimeslot::firstOrCreate([
                            'court_id' => $court->id,
                            'timeslot_id' => $new_timeslot->id
                        ]);
                    }
                }
            }
            $timeslots_bar->advance();
        }

        $timeslots_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported Legacy JACS Timeslots!');

    }

    public function import_courts(){

        $courts = DB::connection('sqlsrv')->table('jacs.TBCOURTS')->get();

        $this->newLine();
        $this->info('Importing Legacy JACS Courts...');

        $courts_bar = $this->output->createProgressBar(count($courts));

        foreach ($courts as $court){

            $web_policy = DB::connection('sqlsrv')->table('jacs.TBPolicies')->where('CourtCode',$court->COURTCODE)->first();

            $new_court = Court::firstorCreate([
                'old_id' => $court->COURTCODE,
                'description' => trim(explode('-',$court->COURTNAME)[0]),
                'case_num_format' => $court->DEFCASEFORMAT,
                'plaintiff' => $court->DEFPLAINTIFF,
                'county_id' => 1, //TODO: Needs manual touch after
                'def_attorney_id' => $courts[0]->DEFATTORNEY != '' ? Attorney::where('bar_num', $court->DEFATTORNEY)->first()->id : null,
                'opp_attorney_id' => $courts[0]->DEFOPPATTORNEY != '' ? Attorney::where('bar_num', $court->DEFOPPATTORNEY)->first()->id : null,
                'web_policy' => $web_policy?->PolicyText
            ]);

            // Add all Hearing/Event types as Default for each court
            foreach (EventType::all() as $event_type){
                CourtEventTypes::firstOrCreate([
                    'court_id' => $new_court->id,
                    'event_type_id' => $event_type->id
                ]);
            }

            $courts_bar->advance();
        }

        $courts_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported OLD JACS Courts!');
    }

    public function import_templates(){

        $templates = DB::connection('sqlsrv')->table('jacs.TBTEMPLATES')
            ->whereNot('DESCRIPTION', 'BLANK')
            ->orderBy('LUPDDATE','desc')->get();

        $this->newLine();
        $this->info('Importing OLD JACS Templates...');

        $templates_bar = $this->output->createProgressBar(count($templates));

        foreach ($templates as $template){
            //Find Court by Judge OLDID to create new Court Template
            $judge = Judge::where('old_id', $template->JUDGEID)->first();

            if($judge != null && $judge->court != null){
                $new_template = Template::firstOrCreate([
                    'court_id' => $judge->court->id,
                    'old_id' => $template->ID,
                    'name' => $template->DESCRIPTION
                ]);

                // Find all timeslots that belong to template in OLD JACS
                $template_timeslots = DB::connection('sqlsrv')->table('jacs.TBJUDGETEMPLATE')
                    ->where('TEMPLATEID', $template->ID)
                    ->get();

                foreach ($template_timeslots as $timeslot){
                    $day = $timeslot->DAYOFWEEK[0] + 1;
                    $start = Carbon::create('2021-11-0' . $day . $timeslot->TIMEFROM);
                    $end = Carbon::create('2021-11-0' . $day . $timeslot->TIMETO);

                    TemplateTimeslot::firstOrcreate([
                        'start' => $start,
                        'end' => $end,
                        'day' => $day,
                        'court_template_id' => $new_template->id,
                        'duration' => $timeslot->DURATION,
                        'quantity' => $timeslot->MAXNUMHEARINGS,
                        'description' => $timeslot->TEMPLATEDESC,
                        'category_id' => Category::where('old_id', $timeslot->CourtroomId)->first()->id ?? null,
                        'blocked' => $timeslot->BLOCKEDFLAG == 'Y',
                        'public_block' => $timeslot->PublicBlock == 'Y',
                        'block_reason' => $timeslot->BlockReason,
                    ]);
                }

            }
            $templates_bar->advance();
        }

        $templates_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported OLD JACS Templates!');
    }

    public function import_motions()
    {
        $motions = DB::connection('sqlsrv')->table('jacs.TBMOTIONS')->get();

        $this->newLine();
        $this->info('Importing Legacy JACS Motions...');

        $motions_bar = $this->output->createProgressBar(count($motions));

        foreach ($motions as $motion){

            Motion::firstorcreate([
                'old_id' => $motion->MOTIONCODE,
                'description' => $motion->DESCRIPTION
            ]);

            $motions_bar->advance();
        }

        $motions_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported Legacy JACS Motions!');
    }

    public function import_events()
    {
        $events = DB::connection('sqlsrv')->table('jacs.TBCOURTCALENDAR')
            ->where('LUPDDATE',  '>', '2023-01-19')
            ->where('COURTCODE', 'CIVIL_I')
            ->whereNotNull('CASENUM')
            ->get();


        $this->newLine();
        $this->info('Importing Legacy JACS Events...');

        $days = $events->groupBy(['COURTCODE','CALDATE','TIMETO']);

        $events_bar = $this->output->createProgressBar(count($days));

        foreach($days as $day){
            foreach($day as $times){
                foreach($times as $time){
                    $timeslot_quantity = count($time);

                    if($timeslot_quantity > 1){
                        $category = Category::where('old_id', $time[0]->courtroomid)->first();
                        $template = Template::where('old_id', $time[0]->TEMPLATEID)->first();
                        $court = Court::where('old_id', $time[0]->COURTCODE)->first();

                        $new_timeslot = Timeslot::create(
                            [
                                'end' => date('Y-m-d',strtotime($time[0]->CALDATE))." ".preg_replace('/.{2}(?!$)/', '$0:', $time[0]->TIMETO),
                                'start' => date('Y-m-d',strtotime($time[0]->CALDATE))." ".preg_replace('/.{2}(?!$)/', '$0:', $time[0]->TIMEFROM),
                                'description' => $time[0]->templatedesc != null ? $time[0]->templatedesc : null,
                                'duration' => $time[0]->DURATION,
                                'quantity' => $timeslot_quantity,
                                'blocked' => !($time[0]->BLOCKEDFLAG == "N"),
                                'public_block' => $time[0]->PublicBlock == "Y",
                                'category_id' => $category != null ? $category->id : null,
                                'template_id' => $template != null ? $template->id : null,
                                'block_reason' => $time[0]->BLOCKREASON,
                            ]);

                        foreach ($time as $event){

                            $motion = Motion::where('old_id', $event->MOTIONCODE)->first();
                            $attorney = Attorney::where('bar_num', $event->BARNUM)->first();
                            $opp_attorney = Attorney::where('bar_num', $event->OPPOSINGBARNUM)->first();
                            $udr_lookup = UserDefinedFields::where('court_id', $court->id)->get();

                            $templetejsone=[];

                            for($i=1; $i < 13; $i++){
                                $key="FIELDID".$i;
                                $value="FIELDID".$i."DATA";
                                if($i==1 || $i==4 || $i== 7 || $i== 10){
                                    $type='LEFT';
                                }
                                if($i==2 || $i==5 || $i== 8 || $i== 11){
                                    $type='CENTER';
                                }
                                if($i==3 || $i==6 || $i== 9 || $i== 12){
                                    $type='RIGHT';
                                }
                                if($event->$key !=""){
                                    $udf = $udr_lookup->where('old_id', $event->$key)->first();
                                    if($udf != null){
                                        $templetejsone[$udf->field_name . '_|' . $type .'_|' . $udf->field_type] = $event->$value;
                                    }
                                }
                            }

                            $new_event = $this->getEvent($event, $motion, $templetejsone, $attorney, $opp_attorney, $new_timeslot);

                        }

                        CourtTimeslot::firstOrCreate([
                            'court_id' => $court->id,
                            'timeslot_id' => $new_timeslot->id
                        ]);
                    } else{
                        foreach ($time as $item){

                            $motion = Motion::where('old_id', $item->MOTIONCODE)->first();
                            $attorney = Attorney::where('bar_num', $item->BARNUM)->first();
                            $opp_attorney = Attorney::where('bar_num', $item->OPPOSINGBARNUM)->first();
                            $category = Category::where('old_id', $item->courtroomid)->first();
                            $template = Template::where('old_id', $item->TEMPLATEID)->first();
                            $court = Court::where('old_id', $item->COURTCODE)->first();
                            $udr_lookup = UserDefinedFields::where('court_id', $court->id)->get();

                            $templetejsone=[];

                            for($i=1; $i < 13; $i++){
                                $key="FIELDID".$i;
                                $value="FIELDID".$i."DATA";
                                if($i==1 || $i==4 || $i== 7 || $i== 10){
                                    $type='LEFT';
                                }
                                if($i==2 || $i==5 || $i== 8 || $i== 11){
                                    $type='CENTER';
                                }
                                if($i==3 || $i==6 || $i== 9 || $i== 12){
                                    $type='RIGHT';
                                }
                                if($item->$key !=""){
                                    $udf = $udr_lookup->where('old_id', $item->$key)->first();
                                    if($udf != null){
                                        $templetejsone[$udf->field_name . '_|' . $type .'_|' . $udf->field_type] = $item->$value;
                                    }
                                }
                            }

                            $new_timeslot = Timeslot::create(
                                [
                                    'end' => date('Y-m-d',strtotime($item->CALDATE))." ".preg_replace('/.{2}(?!$)/', '$0:', $item->TIMETO),
                                    'start' => date('Y-m-d',strtotime($item->CALDATE))." ".preg_replace('/.{2}(?!$)/', '$0:', $item->TIMEFROM),
                                    'description' => $item->templatedesc != null ? $item->templatedesc : null,
                                    'duration' => $item->DURATION,
                                    'quantity' => $timeslot_quantity,
                                    'blocked' => !($time[0]->BLOCKEDFLAG == "N"),
                                    'category_id' => $category != null ? $category->id : null,
                                    'template_id' => $template != null ? $template->id : null,
                                    'block_reason' => $item->BLOCKREASON,
                                ]);

                            $new_event = $this->getEvent($item, $motion, $templetejsone, $attorney, $opp_attorney, $new_timeslot);

                            CourtTimeslot::firstOrCreate([
                                'court_id' => $court->id,
                                'timeslot_id' => $new_timeslot->id
                            ]);
                        }
                    }

                }
            }
            $events_bar->advance();
        }

        $events_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported Legacy JACS Events!');
    }

    public function import_categories()
    {
        $categories = DB::connection('sqlsrv')->table('jacs.TBCourtrooms')->get();

        $this->newLine();
        $this->info('Importing Legacy JACS Categories...');

        $categories_bar = $this->output->createProgressBar(count($categories));

        foreach ($categories as $category){

            Category::firstOrCreate([
                'old_id' => $category->Courtroom_id,
                'description' => $category->description,
            ]);

            $categories_bar->advance();
        }

        $categories_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported Legacy JACS Categories!');

    }

    public function import_court_permissions()
    {
        $permissions = DB::connection('sqlsrv')->table('jacs.TBCALPERMISSIONS')->get();

        $this->newLine();
        $this->info('Importing Legacy JACS Court Permissions...');

        $permission_bar = $this->output->createProgressBar(count($permissions));

        foreach ($permissions as $permission){

            $users = User::all();

            $user = $users->where('old_id', $permission->JaID)->first();
            $judge = Judge::where('old_id', $permission->JudgeID)->first();

            if($user != null && ($judge != null && $judge->court != null)){

               CourtPermission::create([
                   'user_id' => $user->id,
                   'judge_id' => $judge->court->id,
                   'active' => $permission->Active == 'Y',
                   'editable' => $permission->Permissions == 'E'
               ]);
            }

            $permission_bar->advance();
        }

        $permission_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported Legacy Court Permissions!');

    }

    public function import_templates_order()
    {
        $templates_order = DB::connection('sqlsrv')->table('jacs.TBCALTEMPLATES')
            ->whereNotNull('SEQUENCE')
            ->get();

        $this->newLine();
        $this->info('Importing OLD JACS Auto Templates Order...');

        $auto_templates = $templates_order->groupBy('JUDGEID');

        $templates_order_bar = $this->output->createProgressBar(count($auto_templates));

        foreach ($auto_templates as $auto_template){

            foreach ($auto_template as $item){

                $judge = Judge::where('old_id', $item->JUDGEID)->first();

                if($judge != null && $judge->court != null){
                    CourtTemplateOrder::firstOrCreate([
                        'court_id' => $judge->court->id,
                        'order' => $item->SEQUENCE,
                        'template_id' => Template::where('old_id', $item->TEMPLATEID)->where('court_id', $judge->court->id)->first()->id,
                        'auto' => 1
                    ]);
                }
            }

            $templates_order_bar->advance();
        }

        $templates_order_bar->finish();

        // Manual Templates
        $templates_order = DB::connection('sqlsrv')->table('jacs.TBCALTEMPLATES')
            ->whereNull('SEQUENCE')
            ->get();

        $this->newLine();
        $this->info('Importing OLD JACS Manual Templates Order...');

        $manual_templates = $templates_order->groupBy('JUDGEID');

        $templates_order_bar = $this->output->createProgressBar(count($manual_templates));

        foreach ($manual_templates as $manual){

            foreach ($manual as $item){

                $judge = Judge::where('old_id', $item->JUDGEID)->first();
                $date = Carbon::create($item->WDATE);

                if($judge != null && $judge->court != null){
                    CourtTemplateOrder::create([
                        'court_id' => $judge->court->id,
                        'date' => $date,
                        'template_id' => Template::where('old_id', $item->TEMPLATEID)->where('court_id', $judge->court->id)->first()->id ?? null,
                        'auto' => 0
                    ]);
                }
            }

            $templates_order_bar->advance();
        }

        $templates_order_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported OLD JACS Templates Order!');

    }

    private function import_user_defined_fields()
    {
        $user_defined_fields = DB::connection('sqlsrv')->table('jacs.TBCUSTFIELDS')
            ->whereNotIn('FIELDID',['F13','F14','F15'])
            ->get();

        $this->newLine();
        $this->info('Importing Legacy JACS User Defined Fields...');

        $udf_bar = $this->output->createProgressBar(count($user_defined_fields));

        foreach ($user_defined_fields as $field){

            $judge = Judge::where('old_id', $field->JUDGEID)->first();

            $alignment = '';

            switch($field->FIELDID){
                case 'F01':
                case 'F04':
                case 'F07':
                case 'F10':
                    $alignment = 'LEFT';
                    break;
                case 'F02':
                case 'F05':
                case 'F08':
                case 'F11':
                    $alignment = 'CENTER';
                    break;

                case 'F03':
                case 'F06':
                case 'F09':
                case 'F12':
                    $alignment = 'RIGHT';
                    break;
            }

            if($judge != null && $judge->court != null){
                UserDefinedFields::firstOrCreate([
                    'court_id' => $judge->court->id,
                    'field_name' => $field->FIELDNAME ?? $field->FIELDID,
                    'field_type' => $field->FIELDTYPE,
                    'alignment' => $alignment,
                    'old_id' => $field->FIELDID,
                    'default_value' => $field->DEFAULTVALUE,
                    'required' => $field->REQUIRED == 'Y',
                    'yes_answer_required' => $field->YESNOREQUIRED == 'Y',
                    'display_on_docket' => $field->ACTIVEDOCKET == 'Y',
                    'display_on_schedule' => $field->ACTIVESCHED == 'Y',
                    'use_in_attorany_scheduling' => $field->ATTORNEYSCHED == 'Y'

                ]);
            }

            $udf_bar->advance();
        }

        $udf_bar->finish();

        $this->newLine();
        $this->info('Successfully Imported OLD JACS Attorneys!');
    }

    /**
     * @param $event
     * @param $motion
     * @param array $templetejsone
     * @param $attorney
     * @param $opp_attorney
     * @param $new_timeslot
     * @return mixed
     */
    public function getEvent($event, $motion, array $templetejsone, $attorney, $opp_attorney, $new_timeslot)
    {
        switch ($event->DriverType){
            case 'NA':
            default:
                $owner = null;
                $owner_type = null;
                break;
            case 'PA':
            case 'DA':
                $attorney_owner = Attorney::where('bar_num', $event->Driver)->first();
                if($attorney_owner != null){
                    $owner = $attorney_owner->id;
                    $owner_type = 'App\Models\Attorney';
                } else{
                    $owner = null;
                    $owner_type = null;
                }
                break;
            case 'JA':
                $users = User::all();
                $user = $users->where('old_id', $event->Driver);

                if($user->isNotEmpty()){
                    $owner = $user->first()->id;
                    $owner_type = 'App\Models\User';
                } else{
                    $owner = null;
                    $owner_type = null;
                }
                break;
        }


        $new_event = Event::create([
            'case_num' => $event->CASENUM,
            'motion_id' => $motion != null ? $motion->id : null,
            'template' => json_encode($templetejsone),
            'notes' => $event->FIELDID13DATA,
            'plaintiff' => $event->PLAINTIFF,
            'defendant' => $event->DEFENDANT,
            'custom_motion' => $event->MOTIONDESC,
            'attorney_id' => $attorney != null ? $attorney->id : null,
            'type_id' => 2, //TODO: This is for Remote, In Person, or Telephone. Default to In Person
            'status_id' => $new_timeslot->end < Carbon::now() ? 4 : 3, //TODO: Default Scheduled
            'opp_attorney_id' => $opp_attorney != null ? $opp_attorney->id : null,
            'owner_id' => $owner, //TODO: Correct based on DiverType from Old JACS
            'owner_type' => $owner_type,
        ]);

        TimeslotEvent::firstOrCreate([
            'event_id' => $new_event->id,
            'timeslot_id' => $new_timeslot->id
        ]);
        return $new_event;
    }

    public function email_cleanup()
    {
        $this->newLine();
        $this->info('Cleaning up Emails...');

        $emails = Email::all();
        $email_bar = $this->output->createProgressBar(count($emails));
        $bad_domains = [];

        foreach ($emails as $email){

            $domain = explode('@', $email->email)[1] ?? null;

            if(in_array($domain, $bad_domains)){
                $email->attorney->enabled = false;
                $email->attorney->save();
                $email->delete();
            } else{
                $validator = Validator::make(['email' => $email->email], [
                    'email' => 'email:rfc,dns',
                ]);

                if ($validator->fails()) {
                    $this->newLine();
                    $this->info('Bad Email: ' . $email->email);
                    $ford = $this->ask('Fix or Delete?', 'd');
                    if($ford == 'd'){
                        $bad_domains[] .= $domain;
                        $email->attorney->enabled = false;
                        $email->attorney->save();
                        $email->delete();
                    } else{
                        $email->email = $ford;
                        $email->save();
                    }
                }
            }

            $email_bar->advance();
        }

        $email_bar->finish();
        $this->newLine();
        $this->info('Successfully Cleaned up Emails!');
    }

    public function fix_block_reason(){
        $blocked_timeslots = Timeslot::where('blocked',true)->where('block_reason', null)->get();

        $this->newLine();
        $this->info('Fixing Blocked Reason...');

        $progress_bar = $this->output->createProgressBar(count($blocked_timeslots));


        foreach ($blocked_timeslots as $timeslot) {

            $date = Carbon::create($timeslot->start);
            $timeto = Carbon::create($timeslot->end);
            $timefrom = Carbon::create($timeslot->start);

            $result = DB::connection('sqlsrv')->table('jacs.TBCOURTCALENDAR')
                ->where('CALDATE', '=', $date->format('Y-m-d'))
                ->where('COURTCODE', $timeslot->court->old_id)
                ->where('BLOCKEDFLAG', 'Y')
                ->where('TIMETO', $timeto->format('Hi'))
                ->where('TIMEFROM', $timefrom->format('Hi'))
                ->get();

            if($result->isNotEmpty()){
                $timeslot->block_reason = $result[0]->BLOCKREASON;
                $timeslot->save();
            }

            $progress_bar->advance();
        }
        $progress_bar->finish();
    }

    public function fix_other_motion(){


        $other_motion_events = Event::where('motion_id',221)->where('custom_motion', null)->orwhere('custom_motion', '')->get();

        foreach ($other_motion_events as $event){
            if($event->timeslot->court->id == 26){
                $date = Carbon::create($event->timeslot->start);
                $timeto = Carbon::create($event->timeslot->end);
                $timefrom = Carbon::create($event->timeslot->start);

                $result = DB::connection('sqlsrv')->table('jacs.TBCOURTCALENDAR')
                    ->where('CALDATE', '=', $date->format('Y-m-d'))
                    ->where('COURTCODE', $event->timeslot->court->old_id)
                    ->where('TIMETO', $timeto->format('Hi'))
                    ->where('BARNUM', $event->attorney->bar_num ?? null)
                    ->where('OPPOSINGBARNUM', $event->opp_attorney->bar_num ?? null)
                    ->where('TIMEFROM', $timefrom->format('Hi'))
                    ->where('MOTIONCODE', 'ZZ')
                    ->first();

                if($result != null){
                    if($result->MOTIONDESC != ''){
                        $event->custom_motion = $result->MOTIONDESC;
                        $event->save();
                        dump($event->case_num . ' - ' . $result->MOTIONDESC);
                    }
                }

            }
        }

        $this->newLine();
        $this->info('Fixing Other Motions...');

//        $progress_bar = $this->output->createProgressBar(count($blocked_timeslots));
//
//
//        foreach ($blocked_timeslots as $timeslot) {
//
//            $date = Carbon::create($timeslot->start);
//            $timeto = Carbon::create($timeslot->end);
//            $timefrom = Carbon::create($timeslot->start);
//
//            $result = DB::connection('sqlsrv')->table('jacs.TBCOURTCALENDAR')
//                ->where('CALDATE', '=', $date->format('Y-m-d'))
//                ->where('COURTCODE', $timeslot->court->old_id)
//                ->where('BLOCKEDFLAG', 'Y')
//                ->where('TIMETO', $timeto->format('Hi'))
//                ->where('TIMEFROM', $timefrom->format('Hi'))
//                ->get();
//
//            if($result->isNotEmpty()){
//                $timeslot->block_reason = $result[0]->BLOCKREASON;
//                $timeslot->save();
//            }
//
//            $progress_bar->advance();
//        }
//        $progress_bar->finish();
    }
}
