
@extends(backpack_view('blank'))
<style>
    form{
        font-weight: 800 !important;
    }
    form .form-control{
        font-weight: 800 !important;
    }
</style>
@section('before_styles')

<style>
    @media print {
            body {
                visibility: hidden;
            }
        @page {
            size: auto !important;
        }

        .fc-button-group{
            display: none !important;
        }
        .sidebar{
            display: none !important;
        }
        .printpdf {
            visibility: visible;
            position: relative;
            max-width: 1000px;
            -webkit-print-color-adjust:exact !important;
            print-color-adjust:exact !important;
        }
    }
    @media only print{
        #calendar { visibility: visible;
            position: relative;

        }
    }
    /*  @media print {*/
    /*    body {*/
    /*        visibility: hidden;*/
    /*    }*/
    /*    @page { size: auto !important; }*/
    /*    .printpdf {*/
    /*        margin:0;*/
    /*        padding:0;*/
    /*        visibility: visible;*/
    /*        position: relative;*/
    /*        transform: rotate(-90deg);*/
    /*        -webkit-print-color-adjust:exact !important;*/
    /*        print-color-adjust:exact !important;*/
    /*        left: 0;*/
    /*        right: 0;*/
    /*        top: 0;*/
    /*        bottom: 0;*/
    /*        border: 5px solid red;*/
    /*    }*/

    /*    .printpdf tbody tr {*/
    /*        page-break-after: always;*/
    /*    }*/
    /*}*/
    /*@media only print{*/
    /*    #calendar {*/
    /*        visibility: visible;*/
    /*        position: relative;*/
    /*        width:200%;*/
    /*        height:100%;*/
    /*    }*/
    /*}*/
</style>

@endsection
@section('content')

    <h1 class="d-print-none">{{ $court->description }}</h1>
    <p class="mb-5 d-print-none">
        <a href="{{ backpack_url('court/' . $court->id . '/edit') }}" class="btn btn-primary m-1" id="crudTable_reset_button">
            <span class="ladda-label"><i class="la la-lg  la-edit"></i> Edit</span>
        </a>
        <a href="{{ backpack_url('user_defined_fields/' . $court->id) }}" class="btn btn-primary  m-1" id="crudTable_reset_button">
            <span class="ladda-label"><i class="la la-lg  la-stream"></i> User Defined Fields</span>
        </a>
        <a href="{{ backpack_url('calendar/' . $court->id . '/truncate') }}" class="btn btn-primary  m-1" id="crudTable_reset_button">
            <span class="ladda-label"><i class="la la-lg la-calendar-minus"></i> Truncate</span>
        </a>
        <a href="#" onclick = "return downloadCalendarEvents(<?= $court->id ?>);" class="btn btn-primary  m-1" id="crudTable_reset_button">
            <span class="ladda-label"><i class="la la-lg la-download"></i>iCal export</span>
        </a>
        <a href="#" onclick = "return downloadCalendarEventsPDF(<?= $court->id ?>);" class="btn btn-primary  m-1" id="crudTable_reset_button">
            <span class="ladda-label"><i class="la la-lg la-download"></i>Monthly Export</span>
        </a>


        <input type="hidden" id="cal_from_date" value="">
        <input type="hidden" id="cal_to_date" value="">

        @if($court->auto_extension)
            <a href="{{ backpack_url('calendar/' . $court->id . '/extend') }}" class="btn btn-primary  m-1" id="crudTable_reset_button">
                <span class="ladda-label"><i class="la la-lg la-fast-forward"></i> Extend</span>
            </a>
        @else
            <a href="{{ route('extend_calendar_manual', $court) }}" class="extend-button btn btn-primary  m-1" >
                <span class="ladda-label"><i class="la la-lg la-fast-forward" id="extend-manual"></i> Extend</span>
            </a>
        @endif

        <a href="#" onclick = "return multiDeleteTimeslot();" class="float-right btn btn-secondary  m-1" id="multi_delete">
            <span class=""><i class="la la-lg la-trash"></i>Delete Timeslot(s)</span>
        </a>
        <a href="#" onclick = "return multiCopyTimeslot();" class="float-right btn btn-secondary  m-1" id="copy">
            <span class=""><i class="la la-lg la-copy mr-2"></i>Copy Timeslot(s)</span>
        </a>

        <button type="button" style="display:none;" class="btn btn-primary m-1 printbutton page" onClick="window.print()">Print Calendar View</button>
    </p>

    <div class="{{ $widget['class'] ?? 'alert alert-info' }} d-print-none" role="alert">
        <h4 class="alert-heading">Note</h4>
        <p> Click and drag the mouse over period of time or just click on the day to create a timeslot.</p>
    </div>
    <div class="printpdf ">
        <h3 class="printJudgeName">{{$court->judge->name}}</h3>
    <div id='calendar' ></div>
    </div>

@endsection

@section('before_scripts')
    <!-- Reschedule Modal -->
    <div class="modal fade" id="reschedule" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content " >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Reschedule Hearing</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body col-md-12">
                    <div id='reschedule-calendar'></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="create" tabindex="-1" role="dialog" aria-labelledby="Create Modal" aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" style="max-width: 1000px" role="document">
            <div class="modal-content p-3">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal-title">Create...</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" id="timeslot-nav">
                        <a class="nav-link" id="timeslot-tab-link" data-toggle="tab" href="#timeslot-tab" role="tab" aria-controls="timeslot" aria-selected="false">Timeslot(s)</a>
                    </li>

                    <li class="nav-item" id="event-nav">
                        <a class="nav-link" id="event-tab" data-toggle="tab" href="#event" role="tab" aria-controls="event" aria-selected="true" onclick="get_dynamic_case_number_format_fields({{json_encode($case_format->case_num_format)}});">Create Event</a>
                    </li>


                    <li class="nav-item" id="events-nav">
                        <a class="nav-link" id="events-tab" data-toggle="tab" href="#events" role="tab" aria-controls="events" aria-selected="true">Event(s)</a>
                    </li>

                </ul>

                <div class="modal-body tab-content">
                    <div class="tab-pane fade" id="timeslot-tab" role="tabpanel" aria-labelledby="timeslot-tab">
                        <div id="timeslot-errors"></div>
                        <form id="timeslot" data-action="{{ route('timeslot.store') }}">
                            <input type="hidden" name="court_id" value="{{$court->id}}" />

                            <div class="form-row">
                                <div class="col-md-2 blocking">
                                    <div class="form-group">

                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="blocked" name="blocked" >
                                            <label class="form-check-label" for="blocked">Block</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 public_block">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="public_block" name="public_block" >
                                            <label class="form-check-label" for="public_block">Public Block</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3 block_reason">
                                    <label for="block_reason">Block Reason</label>
                                    <input type="text" class="form-control " name="block_reason" id="block_reason" >
                                </div>

                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-3 cattle-call">
                                    <div class="form-group">
                                        <label for="validationServer02">Concurrent/Consecutive</label>
                                        <div class="form-check ">
                                            <input class="form-check-input" onclick="showQuantity()" type="radio"  name="cattlecall" id="cattlecall_yes" value="1" checked="checked" />
                                            <label class="form-check-label"  for="cattlecall_yes">Yes (Concurrent)</label>
                                        </div>
                                        <div class="form-check ">
                                            <input class="form-check-input" onclick="hideQuantity()" type="radio" name="cattlecall" id="cattlecall_no" value="0" />
                                            <label class="form-check-label" for="cattlecall_no">No (Consecutive)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row time-selection">
                                <div class="col-md-2 mb-3">
                                    <div class="form-group">
                                        <label id="timeslot_start_label" for="timeslot_start">Start Time</label>
                                        <div class="input-group date" id="timeslot_start" data-target-input="nearest">
                                            <input type="text" id="timeslot_start_input" name="timeslot_start" class="form-control datetimepicker-input"  data-target="#timeslot_start"/>
                                        </div>
                                        <input type="hidden" name="t_start" id="t_start" />
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-group">
                                        <label id="timeslot_end_label"  for="validationServer02">End Time</label>
                                        <div class="input-group date" id="timeslot_end" data-target-input="nearest">
                                            <input id="timeslot_end_input" type="text" name="timeslot_end" class="form-control datetimepicker-input" data-target="#timeslot_end"/>

                                        </div>
                                        <input type="hidden" name="t_end" id="t_end"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-group">
                                        <label for="validationServer02">Duration</label>
                                        <select class="form-control" id="duration" name="duration" required>
                                            <option value="">-</option>
                                            <option value="5">5 mins</option>
                                            <option value="10">10 mins</option>
                                            <option value="15">15 mins</option>
                                            <option value="20">20 mins</option>
                                            <option value="30">30 mins</option>
                                            <option value="45">45 mins</option>
                                            <option value="60">1 hour</option>
                                            <option value="90">1.5 hours</option>
                                            <option value="120">2 hours</option>
                                            <option value="150">2.5 hours</option>
                                            <option value="165">2.75 hours</option>
                                            <option value="180">3 hours</option>
                                            <option value="210">3.5 hours</option>
                                            <option value="240">4 hours</option>
                                            <option value="300">5 hours</option>
                                            <option value="360">6 hours</option>
                                            <option value="480">8 hours</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3 quantity-group">
                                    <div class="form-group">
                                        <label for="quantity">Quantity</label>
                                        <label for="quantity"></label><input type="number" name="quantity" id="quantity" class="form-control" required/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="description">Description</label>
                                    <input type="text" class="form-control " name="description" id="description" >
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="category">Category</label>
                                        <select class="form-control" id="category" name="category_id">
                                            <option value=""> - </option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"> {{ $category->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label for="timeslot_motions">Restricted Motions</label>
                                        <select name="timeslot_motions[]" multiple id="timeslot_motions" autocomplete="off">
                                            @foreach($court->motions as $motion)
                                                <option value="{{ $motion->id }}"> {{ $motion->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 text-left ">
                                    <a class="btn btn-danger delete-button" href="#" onclick="deleteTimeslot()">Delete</a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </div>
                        </form>
                    </div>


                    <div class="tab-pane fade" id="event" role="tabpanel" aria-labelledby="event-tab">
                        <div id="form-errors"></div>
                        <form id="newevent" data-action="{{ route('timeslot-events.store') }}">
                            @csrf
                            <fieldset class="past-event">
                                <input type="hidden" name="court_id" value="{{$court->id}}" />
				<div class="form-row">
                                    <div class="form-group col-sm-6">
                                    <div id="last_updated" style="display:none">
                                        <label class="font-weight-normal mb-0" for="addon">Edited By:</label> <span id="updated_by"></span>
                                        <br/>
                                        <label class="font-weight-normal mb-0" for="addon">Updated On:</label> <span id="updated_at"></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6" >
                                </div>

                                    <div class="form-group col-sm-6" >
                                    <input type="hidden"  value="0" name="addon">
                                        <input type="checkbox" id="addon" value="1" name="addon">
                                        <label class="font-weight-normal mb-0" for="addon">Addon</label>
                                    </div>
                                    <div class="form-group col-sm-6" >
                                    <input type="hidden"  value="0" name="reminder">
                                        <input type="checkbox" id="reminder" value="1" name="reminder">
                                        <label class="font-weight-normal mb-0" for="reminder">Reminder</label>
                                    </div>

                                    <div class = "dynamic_case_number_format col-md-12"></div>

                                     <input type="hidden" class="form-control" name="case_num" id="case_num" required value="{{$case_format->case_num_format}}">

                                    <div class="col-md-6 mb-12">
                                        <div class="form-group required">
                                            <label for="validationServer02">Motion</label>
                                            <select class="form-control" id="motion" name="motion_id" required>
                                                @foreach($court->motions->sortby('description') as $motion)
                                                    <option value="{{ $motion->id }}"> {{ $motion->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-12">
                                        <div class="form-group required">
                                            <label for="validationServer02">Type</label>
                                            <select class="form-control" id="event_type" name="type_id" required>
                                                @foreach($event_types->sortby('name') as $type)
                                                    <option value="{{ $type->id }}"> {{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6 mb-12" id="otherMotionShow" style="display:none;">
                                        <div class="form-group required">
                                            <label for="otherMotion">Other Motion</label>
                                            <input type="text" class="form-control" name="otherMotion" id="otherMotion" value="" maxlength="255"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group @if($court->plaintiff_attorney_required) required @endif">
                                            <label for="attorney_id">Attorney</label>
                                            <select name="attorney_id" id="attorney" autocomplete="off"  @if($court->plaintiff_attorney_required) required @endif></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group @if($court->defendant_attorney_required) required @endif">
                                            <label for="opp_attorney">Opposing Attorney</label>
                                            <select name="opp_attorney_id" id="opp_attorney" autocomplete="off" @if($court->defendant_attorney_required) required @endif></select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group @if($court->plaintiff_required) required @endif">
                                            <label for="plaintiff" class="plaintiff_label">Plaintiff</label>
                                            <input type="text" class="form-control" name="plaintiff" id="plaintiff" value="{{ $court->plaintiff }}" @if($court->plaintiff_required) required @endif/>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group @if($court->defendant_required) required @endif">
                                            <label for="defendant" class="defendant_label">Defendant</label>
                                            <input type="text" class="form-control" name="defendant" id="defendant" value="{{ $court->defendant }}" @if($court->defendant_required) required @endif/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="plaintiff" id="plaintiff_email_label"  class="plaintiff_email_label">Plaintiff Email</label>
                                            <input type="text" class="form-control" name="plaintiff_email" id="plaintiff_email" value="{{ $court->plaintiff_email }}"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="defendant" id="defendant_email_label" class="defendant_email_label">Defendant Email</label>
                                            <input type="text" class="form-control" name="defendant_email" id="defendant_email" value="{{ $court->defendant_email }}"/>
                                        </div>
                                    </div>
                                </div>
                                <!-- templete dispaly -->
                                <div class="form-row">
                                    @foreach($court_templates as $index => $court_template)
                                    <div class="col-md-4 mb-3">
                                        @if($court_template->field_type=="yes_no")
                                            <div class="form-group ">
                                                <label for="plaintiff">{{$court_template->field_name}}</label>
                                                <div style="">
                                                <label style="margin-left: 30px;">


                                       <input type="radio" id = "user_customer_field{{$index}}" value="yes" class="form-check-input" name="templates_data[{{$court_template->field_name.$index}}_|{{$court_template->alignment}}_|{{$court_template->field_type}}]" >Yes

                                                </label>
                                                <label style="margin-left:30px">


                                       <input type="radio" id = "user_customer_field{{$index}}" value="no" class="form-check-input" name="templates_data[{{$court_template->field_name.$index}}_|{{$court_template->alignment}}_|{{$court_template->field_type}}]" >No


                                                </label>
                                                </div>
                                                </div>
                                                @else
                                                <div class="form-group ">

                                                <label for="plaintiff">{{$court_template->field_name}}</label>


                                        <input type="{{$court_template->field_type}}" class="form-control" name="templates_data[{{$court_template->field_name}}_|{{$court_template->alignment}}_|{{$court_template->field_type}}]" id = "{{  preg_replace('/[^A-Za-z0-9-]/', '',$court_template->field_name.'_|'.$court_template->alignment.'_|'.$court_template->field_type )}}" value="{{empty($court_template->default_value)?'':$court_template->default_value}}"  />

                                            </div>
                                        @endif



                                    </div>
                                    @endforeach
                                </div>
                                <!-- templete dispaly End -->
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea class="form-control" name="notes" id="notes"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 text-left">
                                        <a class="btn btn-danger" href="#" id="event-delete" onclick="">Cancel Hearing</a>
                                        <button type="button" id="reschedule_button" class="btn btn-primary mr-4" >Re-Schedule</button>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
                    <button class="btn btn-danger btn-md" style="display:none;" id="event_delete_btn" onclick="commonDelete()"><i class="fa fa-trash" aria-hidden="true" onclick=""></i>&nbsp;Delete</button>
                        <table class="table table-hover" id="events_table">
                            <thead>
                            <tr>
                                <th scope="col"><input type="checkbox" class="inline-checkbox" name="multiAction" id="multiAction"/></th>
                                <th scope="col">Case #</th>
                                <th scope="col">Motion</th>
                                <th scope="col">Attorney</th>
                                <th scope="col">Plaintiff</th>
                                <th scope="col">Opposing Attorney</th>
                                <th scope="col">Defendant</th>
                                <th scope="col">Actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('after_styles')
    <link rel="stylesheet" href="/css/tempusdominus-bootstrap-4.min.css"  />
    <link rel="stylesheet" href="/css/tom-select.css" />

    <script>
        // $(document).ready(function()
        // {
        //     var selected_value = $('input[type="radio"]:checked').val();
        //     console.log(selected_value);

        //     //jquery cookie asssgin
        //     //cookie check weathere data is there or not
        //     // case format input field shoulb be load based 0n
        // });
        let calendar = null;
        let newday = null;
        let dragEvents = [];

        document.addEventListener('DOMContentLoaded', function() {
            $('#start').datetimepicker({ format: 'LT'});
            $('#end').datetimepicker({ format: 'LT'});
            $('#timeslot_start').datetimepicker({ format: 'LT'});
            $('#t_start').datetimepicker();
            $('#timeslot_end').datetimepicker({ format: 'LT'});
            $('#t_end').datetimepicker();
            let calendarEl = document.getElementById('calendar');

            $('.extend-button').click(function(){
                document.getElementById("extend-manual").classList.remove('la-fast-forward');
                document.getElementById("extend-manual").classList.add('la-spinner');
                document.getElementById("extend-manual").classList.add('la-spin');
                document.getElementsByClassName("extend-button").prop('disabled', true);
            });

            // Full Calendar IO
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                navLinkDayClick: function(date, jsEvent) {
                    var source = calendar.getEventSources();

                    source[0].remove();
                    calendar.addEventSource('{!! route('court-timeslots.show', $court->id)  !!}')
                    calendar.changeView('timeGridDay', date);
                },
                navLinks: true,
                selectable: {{ $editable }},
                weekends: false,
                editable: {{ $editable }},
                slotDuration: '00:05:00',
                slotMinTime: '08:00:00',
                slotMaxTime: '17:30:00',
                fixedWeekCount: false,
                showNonCurrentDates: false,
                selectMirror: true,
                events: '{!! route('court-timeslots.show', $court->id)  !!}',
                select: function(info) {
                    setupModal(info);
                },
                {{--selectAllow: function(info){--}}
                {{--    let blocked_events = @json($blocked_timeslots);--}}

                {{--    let day = new Date(info.startStr);--}}
                {{--    return blocked_events.find(o => o.date === day.toISOString().split('T')[0]) == null;--}}
                {{--},--}}
                eventConstraint: {
                    startTime: '07:00',
                    endTime: '17:30',
                    daysOfWeek: [ 1, 2, 3, 4, 5 ]
                },
                eventClick: function(info) {
                    const checkbox = info.el.getElementsByClassName('m-1 float-right')[0];

                    if(info.jsEvent.ctrlKey){
                        if(checkbox != null){
                            checkbox.checked = !checkbox.checked;

                            if(checkbox.checked){
                                multi_timeslots.push(info.event.id)
                            } else{
                                const index = multi_timeslots.indexOf(info.event.id);
                                multi_timeslots.splice(index, 1);
                            }
                        }
                    } else {
                        if({{ $editable }}){
                            editTimeslot(info);
                        }
                    }
                },
                eventResize: function(info) {
                    updateTimeslot(info);
                },
                eventContent: function(arg) {
                    if(arg.view.type === "listMonth"){
                        let italicEl = document.createElement('span')
                        italicEl.innerHTML = arg.event.title

                        let arrayOfDomNodes = [ italicEl ]
                        return { domNodes: arrayOfDomNodes }
                    } else{
                        let italicEl = document.createElement('div')
                        let something = document.createElement('br')
                        let test = document.createElement('span')


                       if(arg.event.extendedProps.events != null && arg.event.extendedProps.events.length === 0){
                            test.innerHTML = arg.timeText + '<input style="top: .8rem;width: .95rem;height: .95rem;" class="m-1 float-right" disabled type="checkbox" id="cb' + arg.event.id + '"  value="' + arg.event.id + '"/>'
                        } else{
                            test.innerHTML = arg.timeText
                        }

                        italicEl.innerHTML = null;

                        let arrayOfDomNodes = null;

                        if(arg.event.extendedProps.total_length === "5 minutes"){
                            italicEl.innerHTML = ' -- ' + arg.event.title
                            arrayOfDomNodes = [ test, italicEl ]
                        } else{
                            italicEl.innerHTML = arg.event.title
                            arrayOfDomNodes = [ test, italicEl ]
                        }

                        return { domNodes: arrayOfDomNodes }
                    }
                },
                eventDrop: function (info){
                    newday = info.event.start.getDay();
                    let old_time = moment(info.oldEvent.start);
                    let difference = moment(info.event.start).diff(old_time);

                    dragEvents.forEach((element) => {

                        let event = calendar.getEventById(element);
                        let newstart = moment(event.start).add(difference);
                        let newend = moment(event.end).add(difference);

                        //Validation to make sure timeslot doesn't fall off calendar
                        if(newstart.day() > 5 || newend.day() > 5){
                            newstart = newstart.day(5);
                            newend = newend.day(5);
                        }
                        if(newstart.day() < 1 || newend.day() < 1){
                            newstart = newstart.day(1);
                            newend = newend.day(1);
                        }
                        if(newstart.hour() < 8 ){
                            newstart = newstart.hour(8).minute(0);
                        }
                        if(newend.hour() > 17){
                            newend = newend.hour(17).minute(0);
                        }

                        event.setDates(newstart.format() ,newend.format());
                        updateMoveTimeslot(event);
                    })

                    updateTimeslot(info);
                },
                eventRemove: function (info){
                    deleteTimeslot(info)
                },
                eventDragStop: function(info){

                    $('#calendar input:checked').each(function () {
                        dragEvents.push($(this).val())
                    });
                },
                eventOrder: "order"


            });
            calendar.render();

            var cdate = calendar.view;
            $("#cal_from_date").val(cdate.currentStart);
            $("#cal_to_date").val(cdate.currentEnd);


            $('.fc-timeGridWeek-button').click(function(){
               var cdate = calendar.view;
               $("#cal_from_date").val(cdate.currentStart);
                    $("#cal_to_date").val(cdate.currentEnd);

                var source = calendar.getEventSources();
                source[0].remove();
                calendar.addEventSource('{!! route('court-timeslots.show', $court->id)  !!}')

            });
            $('.fc-prev-button').click(function(){
               var cdate = calendar.view;
               $("#cal_from_date").val(cdate.currentStart);
                    $("#cal_to_date").val(cdate.currentEnd);

            });
            $('.fc-next-button').click(function(){
               var cdate = calendar.view;
               $("#cal_from_date").val(cdate.currentStart);
                    $("#cal_to_date").val(cdate.currentEnd);

            });
            $('.fc-dayGridMonth-button').click(function(){
               var cdate = calendar.view;
               $("#cal_from_date").val(cdate.currentStart);
               $("#cal_to_date").val(cdate.currentEnd);

                var source = calendar.getEventSources();

                source[0].remove();
                calendar.addEventSource('{!! route('court-timeslots.month', $court->id)  !!}')
                $(".printbutton").show();

            });

            $('.fc-timeGridDay-button').click(function(){
                var cdate = calendar.view;
                $("#cal_from_date").val(cdate.currentStart);
                $("#cal_to_date").val(cdate.currentEnd);

                var source = calendar.getEventSources();

                source[0].remove();
                calendar.addEventSource('{!! route('court-timeslots.show', $court->id)  !!}')

                $(".printbutton").hide();
            });
            $('.fc-timeGridWeek-button').click(function(){
                $(".printbutton").hide();
            });
            $('.fc-listMonth-button').click(function(){
                var cdate = calendar.view;
                $("#cal_from_date").val(cdate.currentStart);
                $("#cal_to_date").val(cdate.currentEnd);

                var source = calendar.getEventSources();
                source[0].remove();
                calendar.addEventSource('{!! route('court-timeslots.show', $court->id)  !!}')
                $(".printbutton").hide();
            });
              const urlParams = new URLSearchParams(location.search);
            for (const [key, timeslotId] of urlParams) {
                if(key == "create_event")
                {
                    editTimeslot(timeslotId);
                }
            }
    });



    </script>
@endsection
@section('after_scripts')
    <script src="/js/moment.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js'></script>
{{--    <script src="/js/fc-main.min.js"></script>--}}
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    <script src="/js/tom-select.complete.min.js"></script>

    <script src="/js/tempusdominus-bootstrap-4.min.js" ></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $('#create').on('shown.bs.modal', function() {
            $(document).off('focusin.modal');
        });
        let modal ='#create';
        let events = null;
        let multi_timeslots = [];

        $("#blocked").change(function() {
            if(this.checked) {
                $('.public_block').show();
                $('.block_reason').show();
            } else{
                $('.public_block').hide();
                $('#public_block').prop('checked', false)
                $('.block_reason').hide();
                $('#block_reason').val('');
            }
        });

        // Timeslot motions select
        let timeslotmotions_select = new TomSelect("#timeslot_motions",{
            persist: false,
            plugins: {
                remove_button:{
                    title:'Remove this item',
                },
            },
        })

        // Javascript Attorney Fetch
        let attorney_select = new TomSelect("#attorney",{
            valueField: 'id',
            labelField: 'name',
            plugins: ['clear_button'],
            placeholder: 'Type Bar Number',
            searchField: ['name','bar_num'],
            load: function(query, callback) {
                var url = '{{ env('APP_URL')  }}/api/attorney?q=' + encodeURIComponent(query);
                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        callback(json.data);
                    }).catch(()=>{
                    callback();
                });
            },
            render: {
                option: function(item) {
                    return `<div> ${ item.name } - ${ item.bar_num} </div>`;
                },
                item: function(item) {
                    return `<div> ${ item.name } - ${ item.bar_num} </div>`;
                }
            },
            sortField: {
                field: "text",
                direction: "asc",
            }

        })

        // Javascript Opposing Attorney Fetch
        let opp_attorney_select = new TomSelect("#opp_attorney",{
            valueField: 'id',
            labelField: 'name',
            plugins: ['clear_button'],
            placeholder: 'Type Bar Number',
            searchField: ['name','bar_num'],
            load: function(query, callback) {
                var url = '{{ env('APP_URL')  }}/api/attorney?q=' + encodeURIComponent(query);
                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        callback(json.data);
                    }).catch(()=>{
                    callback();
                });
            },
            render: {
                option: function(item) {
                    return `<div> ${ item.name } - ${ item.bar_num} </div>`;
                },
                item: function(item) {
                    return `<div> ${ item.name } - ${ item.bar_num} </div>`;
                }
            },
            sortField: {
                field: "text",
                direction: "asc",
            }
        });

        // Event Form Submit
        let event_form = '#newevent';
        $(event_form).on('submit', function(event){
            event.preventDefault();
            $(this).find(':input[type=submit]').prop('disabled', true);

            let url = $(this).attr('data-action');

            $.ajax({
                url: url,
                method: 'POST',
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success:function(response)
                {
                    $(event_form).trigger("reset");
                    $(modal).modal('hide');
                    $('#newevent input[name=id]').remove()
                    $('#newevent input[name=timeslot_id]').remove()
                    $(event_form).find(':input[type=submit]').prop('disabled', false);
                    var source = calendar.getEventSources();
                    source[0].refetch();
                },
                error: function(response) {
                    $(event_form).find(':input[type=submit]').prop('disabled', false);
                    var errors = response.responseJSON;

                    console.log(errors.errors);
                    errorsHtml = '<div class="alert alert-danger"><ul>';

                    $.each( errors.errors, function( key, value ) {
                        if(key.includes('plaintiff')){
                            $('#plaintiff_email').addClass('is-invalid');
                            $('#plaintiff_email_label').addClass('text-danger');
                        }
                        if(key.includes('defendant')){
                            $('#defendant_email').addClass('is-invalid');
                            $('#defendant_email_label').addClass('text-danger');
                        }
                        errorsHtml += '<li>'+ value[0] + '</li>'; //showing only the first error.
                    });
                    errorsHtml += '</ul></div>';

                    $( '#form-errors' ).html( errorsHtml );
                }
            });

        });

        // Timeslot Form Submit Timeslot from Submit
        let timeslot_form = '#timeslot';
        $(timeslot_form).on('submit', function(event){
            event.preventDefault();

            var url = $(this).attr('data-action');

            $.ajax({
                url: url,
                method: 'POST',
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success:function(response)
                {
                    $(timeslot_form).trigger("reset");
                    $(modal).modal('hide');
                    var source = calendar.getEventSources();
                    source[0].refetch();
                },
                error: function(response) {
                    var errors = response.responseJSON;

                    console.log(errors.errors);
                    errorsHtml = '<div class="alert alert-danger"><ul>';

                    $.each( errors.errors, function( key, value ) {
                        if(key.includes('t_start')){
                            $('#timeslot_start_input').addClass('is-invalid');
                            $('#timeslot_start_label').addClass('text-danger');
                        }
                        if(key.includes('t_end')){
                            $('#timeslot_end_input').addClass('is-invalid');
                            $('#timeslot_end_label').addClass('text-danger');
                        }
                        errorsHtml += '<li>'+ value[0] + '</li>'; //showing only the first error.
                    });
                    errorsHtml += '</ul></div>';

                    $( '#timeslot-errors' ).html( errorsHtml );
                }
            });
        });

        function downloadCalendarEvents(courtId) {
            const link = document.createElement('a');
            link.setAttribute('href', "<?= env('APP_URL') ?>/court/event/calendar/download/"+courtId+"/"+dateConvert($("#cal_from_date").val())+"/"+dateConvert($("#cal_to_date").val()));
            link.setAttribute('target',"_blank");
            link.click();
        }

        function downloadCalendarEventsPDF(courtId) {

            const link = document.createElement('a');
            link.setAttribute('href', "<?= env('APP_URL') ?>/court-timeslots/print/"+courtId+"/"+dateConvert($("#cal_from_date").val())+"/"+dateConvert($("#cal_to_date").val()));
            link.setAttribute('target',"_blank");
            link.click();
        }

        function commonDelete() {
            var listarray = new Array();
            var casearray = new Array();
            $('input[name="multiple[]"]:checked').each(function () {
                listarray.push($(this).val());
                casearray.push($(this).attr("data-id")+"  ");
            });
            var checklist = "" + listarray;

            var caseList = casearray.join('<br>');

            if (checklist != '') {
                $("#futureEvents").modal('hide');
            swal.fire({
                        title: 'Are you sure?',
                        html: "You won't be able to revert this!<br/>"+caseList,
                        icon: 'warning',
                        input: 'textarea',
                        inputLabel: 'Cancellation Reason',
                        inputPlaceholder: 'Type your message here...',
                        inputAttributes: {
                            'aria-label': 'Type your message here'
                        },
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, cancel it!',
                        customClass: {
                            validationMessage: 'my-validation-message'
                        },
                        preConfirm: (value) => {
                            if (!value) {
                                Swal.showValidationMessage(
                                    '<i class="fa fa-info-circle"></i> Cancellation reason is required!'
                                )
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                dataType: 'JSON',
                                type: 'post',
                                data: { 'updatelist': checklist,'cancellation_reason':result.value},
                                url: "{{ env('APP_URL') }}/event/future/bulk-delete",
                                success: function()
                                {
                                    $(".inline-checkbox:checked").closest('tr').remove();
                                    Swal.fire(
                                        'Cancelled!',
                                        'Your hearing has now been cancelled.',
                                        'success'
                                    ).then( () => {
                                        let source = calendar.getEventSources();
                                        source[0].refetch();
                                    })
                                },
                                error: function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Something went wrong!',
                                    })
                                }
                            });
                        }
                    })
                }
             }

        $(function () {
            $('#multiAction').click(function () {
                if ($('#multiAction').is(':checked')) {

                    $('#multiAction').prop('checked', true);
                    $('[name="multiple[]"]').prop('checked', true);
                } else {

                    $('#multiAction').prop('checked', false);
                    $('[name="multiple[]"]').prop('checked', false);

                }
             });

             $('.inline-checkbox').click(function () {
                var checkedNum = $('input[name="multiple[]"]:checked').length;
                if(checkedNum > 0){
                    $('#event_delete_btn').show();
                }else{
                    $('#event_delete_btn').hide();
                }
             });
        });

        function dateConvert(str) {
          var date = new Date(str),
            mnth = ("0" + (date.getMonth() + 1)).slice(-2),
            day = ("0" + date.getDate()).slice(-2);
          return [date.getFullYear(), mnth, day].join("-");
        }

        // Update timeslot time and duration when dragged
        function updateTimeslot(e){
            var url = e.event.extendedProps.update_url;

            $.ajax({
                url: url,
                method: 'PUT',
                data: JSON.stringify({ start: e.event.start, end: e.event.end, court_id: {{ $court->id }} }),
                dataType: 'JSON',
                contentType: "application/json; charset=utf-8",
                cache: false,
                processData: false,
                success:function(response)
                {
                    var source = calendar.getEventSources();
                    source[0].refetch();
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        }

        // Update other timeslots on multi move
        function updateMoveTimeslot(event){
            var url = event.extendedProps.update_url;

            $.ajax({
                url: url,
                method: 'PUT',
                data: JSON.stringify({ start: event.start, end: event.end, court_id: {{ $court->id }} }),
                dataType: 'JSON',
                contentType: "application/json; charset=utf-8",
                cache: false,
                processData: false,
                success:function(response)
                {
                    multi_timeslots = [];
                    dragEvents = [];
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        }

        function editTimeslot(event){
            var url = "";
            var e = "";
            if(!isNaN(event)) {
                e = {
                    event: {
                        id: event,
                        extendedProps: {
                            edit_url:'{{ env('APP_URL') }}/timeslot/' + event + "/edit",
                            update_url:'{{ env('APP_URL') }}/timeslot/' + event
                        }
                    }
                };
                url = e.event.extendedProps.edit_url;
            }
            else{
                e = event;
                url = event.event.extendedProps.edit_url;
            }

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'JSON',
                success:function(data)
                {
                    $('#newevent input[name=timeslot_id]').remove();
                    let start_formatted = moment(data["start"]);
                    let end_formatted = moment(data["end"]);
                    events = data['events'];

                    // Formatting Modal for new data
                    $('.public_block').hide();
                    $('.block_reason').hide();
                    $('.time-selection').show();
                    $('.cattle-call').hide();
                    $('.delete-button').show();
                    $('#modal-title').text(moment(data["start"]).format('ddd MMM D, h:mm a') + ' - ' + moment(data["end"]).format('h:mm a'));
                    $('#timeslot_start').datetimepicker('date', start_formatted.format('h:mm a'));
                    $('#timeslot_end').datetimepicker('date', end_formatted.format('h:mm a'));

                    // Setting Timeslot data within Modal
                    $("#quantity").val(data["quantity"]);
                    $('#t_start').val(data["start"]);
                    $("#t_end").val(data["end"]);
                    $("#description").attr('value', data["description"]);
                    $("#block_reason").attr('value', data["block_reason"]);
                    $('#category option[value="' + data["category_id"] +'"]').prop('selected', true);


                    if(jQuery.inArray(data["duration"], [5, 10, 15,20,30,45,60,90,120,150,165,180,210,240,300,360,480]) == -1){
                        $('#duration').append($('<option>', {
                            value: data["duration"],
                            text: 'Other (' +  data["duration"] + ' mins)'
                        }));
                    }

                    $('#duration option[value="' + data["duration"] +'"]').prop('selected', true);

                    data["motions"].forEach(element => {
                        if(element.timeslotable_type == "App\\Models\\Timeslot"){
                            timeslotmotions_select.addItem(element.motion_id);
                        }
                    })

                    if(data["blocked"]){
                        $('#blocked').prop('checked', 'checked')
                    }
                    if(data["public_block"]){
                        $('#public_block').prop('checked', 'checked')
                    }


                    $('.delete-button').attr('onclick', 'deleteTimeslot(' + e.event.id + ')');

                    if(data["events"].length !== 0){
                        $('.delete-button').hide();
                    }

                    if(data["allDay"]){
                        $('.cattle-call').hide();
                        $('.time-selection').hide();
                        $('#duration').removeAttr('required');
                        $('#quantity').removeAttr('required');
                    }
                    $('#timeslot-tab-link').tab('show');

                    // Setting background data for Event creation
                    $('#timeslot').attr('data-action', e.event.extendedProps.update_url);
                    $('#timeslot').append('<input type="hidden" id="method" name="_method" value="PUT" />');
                    $('#newevent').append('<input type="hidden" name="timeslot_id" value="' + data["id"] +'" />')


                    // Singleton event check
                    if(data['quantity'] === 1){
                        $('#event-nav').show();
                        $('#events-nav').hide();
                        $('#event-delete').hide();
                        $('#reschedule_button').hide();

                        // If event exist, only show event tab in modal
                        if(data['events'].length !== 0){

                            editEvent(0, end_formatted);

                            $('#events-tab').css('display','none');
                            //$('#timeslot-tab-link').css('display','none');
                        }

                    } else{
                        $('#event-nav').show();
                        $('#event-delete').hide();
                        $('#reschedule_button').hide();

                        // Fill in table of Events
                        if(data["events"].length !== 0){
                            $('#events-nav').show();
                            // Populating Events tab if there are any
                            let table = document.getElementById("events_table").getElementsByTagName('tbody')[0];
                            data['events'].forEach( function (element, index) {
                                if(element.status_id != 1){
                                var row = table.insertRow(0);
                                var cell0 = row.insertCell(0);
                                var cell1 = row.insertCell(1);
                                var cell2 = row.insertCell(2);
                                var cell3 = row.insertCell(3);
                                var cell4 = row.insertCell(4);
                                var cell5 = row.insertCell(5);
                                var cell6 = row.insertCell(6);
                                var cell7 = row.insertCell(7);

                                cell0.innerHTML = '<input type="checkbox"  name="multiple[]" class="inline-checkbox" data-id="'+ element.case_num+'" value="'+ element.id+'">';
                                cell1.innerHTML = element.case_num;
                                cell2.innerHTML = element.motion.description;

                                cell3.innerHTML = element.attorney ? element.attorney.name : '';
                                cell4.innerHTML = element.plaintiff;
                                cell5.innerHTML = element.opp_attorney ? element.opp_attorney.name : '';
                                cell6.innerHTML = element.defendant;
                                cell7.innerHTML = '<a href="#" onclick="editEvent('+ index +',' + end_formatted +')"><i class="las la-edit"></i></a>'
                                }
                            })

                            $('.inline-checkbox').click(function () {
                                var checkedNum = $('input[name="multiple[]"]:checked').length;
                                if(checkedNum > 0){
                                    $('#event_delete_btn').show();
                                }else{
                                    $('#event_delete_btn').hide();
                                }
                            });
                        }
                    }

                    @isset($court->def_attorney_id)
                        attorney_select.addOption({
                            id: {{ $court->def_attorney_id }},
                            name: '{{ App\Models\Attorney::find($court->def_attorney_id)->name }}',
                            bar_num: '{{ App\Models\Attorney::find($court->def_attorney_id)->bar_num }}',
                        });
                        attorney_select.setValue({{ $court->def_attorney_id }});
                    @endisset

                    @isset($court->opp_attorney_id)
                        opp_attorney_select.addOption({
                            id: {{ $court->opp_attorney_id }},
                            name: '{{ App\Models\Attorney::find($court->opp_attorney_id)->name }}',
                            bar_num: '{{ App\Models\Attorney::find($court->opp_attorney_id)->bar_num }}',
                        });
                        opp_attorney_select.setValue({{ $court->opp_attorney_id }});
                    @endisset

                    $("#create").modal('show');
                    if(end_formatted >= moment()){
                        if(!{{$editable}})
                        {
                            $('.past-event').prop('disabled',true);
                        }else{
                            $('.past-event').prop('disabled',false);
                        }
                    } else{
                        $('.past-event').prop('disabled',true);
                    }

                },
                error: function(response) {
                    console.log('Error');
                }
            });
        }

        // Reschedule Event Function
        function rescheduleEvent(e){

            let url = '{{ env('APP_URL') }}/timeslot-events/' + e.event.id;

            let date = dayjs(e.event.start).format('MM/DD/YYYY - h:mm a');

            let event_id = $('#newevent input[name=id]').val();

            let old_timeslot_id = $('#newevent input[name=timeslot_id]').val();

            Swal.fire({
                title: 'Are you sure?',
                html: "This hearing at <strong> "+ $('#create #modal-title').html() +" </strong> is about to be rescheduled to <strong> " + date + " </strong>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reschedule it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'PUT',
                        data: JSON.stringify({ old_timeslot_id: old_timeslot_id, event_id: event_id }),
                        dataType: 'JSON',
                        contentType: "application/json; charset=utf-8",
                        cache: false,
                        processData: false,
                        success:function()
                        {
                            Swal.fire(
                                'Reschedule!',
                                'Your hearing has now been rescheduled.',
                                'success'
                            ).then(() => {
                                $('#newevent input[name=id]').remove()
                                $('#newevent input[name=timeslot_id]').remove()
                                $('#reschedule').modal('hide');
                                var source = calendar.getEventSources();
                                source[0].refetch();
                            })
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            })
                        }
                    });
                }
            })
        }

        // Display modal on timeslot selection
        function setupModal(info){

            $('.quantity-group').show();
            $('.cattle-call').show();
            $('.time-selection').show();
            $('.public_block').hide();
            $('.block_reason').hide();

            $('#cattlecall_yes').prop('checked','checked');


            $('.delete-button').hide();

            $('#event-nav').hide();
            $('#events-nav').hide();


            $("#duration").attr("required", true);
            $("#quantity").attr("required", true);

            $('#modal-title').text(moment(info.start).format('ddd MMM D, h:mm a') + ' - ' + moment(info.end).format('h:mm a'));
            $('#timeslot_start').datetimepicker('date', info.start);
            $('#t_start').val(moment(info.start).format('YYYY-MM-DD HH:mm:ss'));
            $('#timeslot_end').datetimepicker('date', info.end);
            $('#t_end').val(moment(info.end).format('YYYY-MM-DD HH:mm:ss'));

            $('#timeslot').attr('data-action', '{{ route('timeslot.store') }}');


            if(info.allDay){
                $('.blocking').css('display','');
                $('.cattle-call').css('display','none');
                $('.time-selection').css('display','none');
                $('#duration').removeAttr('required');
                $('#quantity').removeAttr('required');
            }

            // Finally display Modal and set Timesolt as default
            $("#create").modal('show');
            $('#timeslot-tab-link').tab('show');
        }

        // AJAX Timeslot Deletion Function
        function deleteTimeslot(e){
            let url = '{{ env('APP_URL')  }}/timeslot/' + e
            $.ajax({
                url: url,
                method: 'DELETE',
                dataType: 'JSON',
                success: function(data)
                {
                    $("#create").modal('hide');
                    let source = calendar.getEventSources();
                    source[0].refetch();
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        }

        // AJAX Timeslot Deletion Function
        function multiDeleteTimeslot(){
            let url = '{{ env('APP_URL')  }}/timeslot/multi'
            $.ajax({
                url: url,
                method: 'DELETE',
                data: JSON.stringify(multi_timeslots),
                contentType: "application/json; charset=utf-8",
                cache: false,
                processData: false,

                dataType: 'JSON',
                success: function(data)
                {
                    $("#create").modal('hide');
                    let source = calendar.getEventSources();
                    source[0].refetch();
                    multi_timeslots = [];
                    dragEvents = [];
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        }

        function multiCopyTimeslot(){
            let url = '{{ env('APP_URL')  }}/timeslot/copy'
            $.ajax({
                url: url,
                method: 'POST',
                data: JSON.stringify(multi_timeslots),
                contentType: "application/json; charset=utf-8",
                cache: false,
                processData: false,

                dataType: 'JSON',
                success: function(data)
                {
                    $("#create").modal('hide');
                    let source = calendar.getEventSources();
                    source[0].refetch();
                    multi_timeslots = [];
                    dragEvents = [];
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        }

        // AJAX Event Deletion Function
        function deleteEvent(e){
            let url = '{{ env('APP_URL')  }}/event/' + e

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'Cancellation Reason',
                inputPlaceholder: 'Type your message here...',
                inputAttributes: {
                    'aria-label': 'Type your message here'
                },
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, cancel it!',
                customClass: {
                    validationMessage: 'my-validation-message'
                },
                preConfirm: (value) => {
                    if (!value) {
                        Swal.showValidationMessage(
                            '<i class="fa fa-info-circle"></i> Cancellation reason is required!'
                        )
                    }
                    if (value.length > 255) {
                        Swal.showValidationMessage(
                            '<i class="fa fa-info-circle"></i> Cancellation reason is length is too long!'
                        )
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        dataType: 'JSON',
                        data: result,
                        success: function()
                        {
                            Swal.fire(
                                'Cancelled!',
                                'Your hearing has now been cancelled.',
                                'success'
                            ).then( () => {
                                $('#newevent input[name=id]').remove()
                                $('#newevent input[name=timeslot_id]').remove()
                                $("#create").modal('hide');
                                let source = calendar.getEventSources();
                                source[0].refetch();
                            })
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            })
                        }
                    });
                }
            })
        }

        // Reset Form values when modal is Hidden
        $('#create').on('hidden.bs.modal', function () {
            $('#timeslot').trigger("reset");
            $('#notes').val('');
            $("#description").attr('value','');
            $("#block_reason").attr('value','');

            $('#plaintiff_email').removeClass('is-invalid');
            $('#plaintiff_email_label').removeClass('text-danger');

            $('#defendant_email').removeClass('is-invalid');
            $('#defendant_email_email_label').removeClass('text-danger');

            $('#timeslot_start_input').removeClass('is-invalid');
            $('#timeslot_start_label').removeClass('text-danger');

            $('#timeslot_end_input').removeClass('is-invalid');
            $('#timeslot_end_label').removeClass('text-danger');
            $('#timeslot-errors').hide();

            $('#timeslot #method').remove();
            $("#events_table tbody tr").remove();
            $('#timeslot-tab-link').tab('show')
            $('#event-tab').text('Create Event');
            $('#events-tab').css('display','');
            $('#timeslot-tab-link').css('display','');
            $(event_form).trigger("reset");
            attorney_select.clear();
            opp_attorney_select.clear();
            timeslotmotions_select.clear();

            $( '#form-errors .alert' ).remove()
            $('#newevent #method').remove()
            $('#newevent #id').remove()

	        $('#otherMotionShow').hide();
	        $("#last_updated").hide();
            $('#newevent').attr('data-action', '{{ env('APP_URL')  }}/timeslot-events');
        })

        // Remove Quantity if Cattle call is consecutive
        function hideQuantity(){
            $('.quantity-group').hide();
        }
        function showQuantity(){
            $('.quantity-group').show();
        }

        // Timeslot Automation
        let quantity = $('input[type=number][name=quantity]');
        let start = $('input[type=text][name=timeslot_start]');
        let end = $('input[type=text][name=timeslot_end]');
        let duration =  $('select[name=duration]');

        end.change(function (){
            updateForm();
        });

        duration.change(function (){
            updateForm();
        });

        function updateForm(e){
            let to_time = moment(end.val(),'HH:mm A');
            let from_time = moment(start.val(), 'HH:mm A');
            let total_hours = to_time.diff(from_time, 'minutes');

            if(duration.val() !== ''){
                quantity.val(Math.floor(total_hours/duration.val()));
            }

        }

        // Updating hidden Datetime input for server side
        $('#timeslot_start').on("change.datetimepicker",function(e){
            let time = moment($('#t_start').val());
            let change = moment(e.date);
            time.hour(change.hour());
            time.minutes(change.minutes());
            $('#t_start').val(time.format('YYYY-MM-DD HH:mm:ss'));
        })

        // Updating hidden Datetime input for server side
        $('#timeslot_end').on("change.datetimepicker",function(e){
            let time = moment($('#t_end').val());
            let change = moment(e.date);
            time.hour(change.hour());
            time.minutes(change.minutes());
            $('#t_end').val(time.format('YYYY-MM-DD HH:mm:ss'));
        })

        // AJAX Edit Event (Hearing)
        function editEvent(id, time){
            $('#event-tab').tab('show').text('Edit Event');
            $("#case_num").val(events[id]['case_num']);

            var j = 1;
            get_dynamic_case_number_format_fields(events[id]['case_num']);
            $.each(events[id]['case_num'].split('-'),function(index,casevalue){
                if($("#case_num_format_multiple"+j).is("select"))
                {
                    $("#case_num_format_multiple"+j+ " select").val(casevalue).change();
                }
                else if($("#case_num_format_multiple"+j).is("input"))
                {
                    $("#case_num_format_multiple"+j).val(casevalue);
                }
                j++;
            })
            $("#notes").val(events[id]['notes']);
            // $("#custom_email_body").html(events[id]['custom_email_body']);
            $("#plaintiff").val(events[id]['plaintiff']);
            $("#otherMotion").val(events[id]['custom_motion']);
            $("#plaintiff_email").val(events[id]['plaintiff_email']);
            $("#defendant").val(events[id]['defendant']);
            $("#plaintiff_email").val(events[id]['plaintiff_email']);
            $("#defendant_email").val(events[id]['defendant_email']);

	        $("#last_updated").show();
            var updatedDate = new Date(events[id]['updated_at']);
            var formattedDate = (updatedDate.getMonth() + 1) + '/' + updatedDate.getDate() + '/' + updatedDate.getFullYear();
            var formattedTime = updatedDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', timeZone: 'America/New_York' });
             $("#updated_at").html(formattedDate + ' ' + formattedTime);


            if(events[id]['ownerable'] !== null) {
                $("#updated_by").html(events[id]['ownerable']['name']);
            }

	        var j = 0;
            let court_templates = <?php echo $court_templates;?>;
            $.each(court_templates,function(index,court_template){

                var key = "";
                var template = "";
                $.each(JSON.parse(events[id]['template']),function(index1,value1){
                    if(index1 === (court_template.field_name+index+"_|"+court_template.alignment+"_|"+court_template.field_type) && court_template.field_type == "yes_no"){
                        key = index1;
                        template = value1;
                        return true;
                    }else if(index1 === (court_template.field_name+"_|"+court_template.alignment+"_|"+court_template.field_type)){
                        key = index1;
                        template = value1;
                        return true;
                    }
                });
                if(key != "") {
                    let stringArray=key.split("_|");
                    if(stringArray[2] =="yes_no") {
                        $('input[id=user_customer_field'+index+']').removeAttr("checked");
                            $('input[id=user_customer_field'+index+'][value='+template+']').attr('checked',true);
                    }else{
                        $("#"+ key.replace(/([^A-Za-z0-9-])/ig, "")).val(template);
                    }
                }
                j++;
            })


            $('#motion option[value="' + events[id]['motion_id'] +'"]').prop('selected', true);
            $('#event_type option[value="' + events[id]['type_id'] +'"]').prop('selected', true);

            // Cancelling/Rescheduling should only be possible if timeslot is greater than or equal to the current time
            if(time >= moment()){
                $('.past-event').prop('disabled',false);
                $('#event-delete').attr('onclick', 'deleteEvent(' + events[id]['id'] + ')');
                $('#event-delete').show();
                $('#reschedule_button').show();
            } else{
                $('.past-event').prop('disabled',true);
            }

            $('#otherMotionShow').hide();

            if(events[id]['motion_id']  == 221){
                $('#otherMotionShow').show();
            }

            if(events[id]['attorney'] != null){
                attorney_select.addOption({
                    id: events[id]['attorney_id'],
                    name:events[id]['attorney']['name'],
                    bar_num: events[id]['attorney']['bar_num'],
                });
                attorney_select.setValue(events[id]['attorney_id']);
            }

            if(events[id]['addon']){
                $("#addon").prop('checked', true);
            }

            if(events[id]['reminder']){
                $("#reminder").prop('checked', true);
            }

            if(events[id]['opp_attorney'] != null){
                opp_attorney_select.addOption({
                    id: events[id]['opp_attorney_id'],
                    name:events[id]['opp_attorney']['name'],
                    bar_num: events[id]['opp_attorney']['bar_num'],
                });
                opp_attorney_select.setValue(events[id]['opp_attorney_id']);
            }

            $('#newevent').attr('data-action', '{{ env('APP_URL')  }}/event/' + events[id]['id']);
            $('#newevent').append('<input type="hidden" id="method" name="_method" value="PUT" />');

            if($('input[name="id"]').val() != null){
                $('input[name="id"]').val(events[id]['id'])
            } else {
                $('#newevent').append('<input type="hidden" name="id" value="' + events[id]['id'] +'" />')
            }

        }

        $(document).on("change",".case_num_format_multiple", function(){
            $case_num = [];
            var allemptylength=$(".case_num_format_multiple").filter(function() {
                $case_num.push(this.value);
                return this.value.length !== 0;
            })
            // console.log("case_num_format_multiple",allemptylength.length)
            if($('.case_num_format_multiple').length== allemptylength.length){
                $.ajax({
                    data: { case_number: $case_num.join('-')},
                    url: "{{ env('APP_URL') }}/event/casenum",
                    method: 'POST',
                    success:function(response)
                    {
                        if(response != null) {
                            $("#motion").val(response.motion_id);
                            $("#event_type").val(response.type_id);
                            $("#plaintiff").val(response.plaintiff);
                            $("#plaintiff_email").val(response.plaintiff_email);
                            $("#defendant").val(response.defendant);
                            $("#defendant_email").val(response.defendant_email);
                            $("#notes").val(response.notes);
			                var i = 0;
                            $.each(JSON.parse(response.template),function(index,template){
                                let stringArray=index.split("_|");
                                if(stringArray[2] =="yes_no") {
                                    $('input[id=user_customer_field'+i+'][value='+template+']').attr('checked',true);
                                }else{
                                    $("#"+ index.replace(/[^A-Z0-9]/ig, "")).val(template);
				                }
				                i++;
                            })

                            $('#otherMotionShow').hide();

                            if(response.motion_id  == 221){
                                $('#otherMotionShow').show();
                            }

                            if(response.attorney != null){
                                attorney_select.addOption({
                                    id: response.attorney_id,
                                    name:response.attorney.name,
                                    bar_num: response.attorney.bar_num,
                                });
                                attorney_select.setValue(response.attorney_id);
                            }
                            if(response.addon){
                                $("#addon").prop('checked', true);
                            }
                            if(response.reminder){
                                $("#reminder").prop('checked', true);
                            }

                            if(response.opp_attorney != null){
                                opp_attorney_select.addOption({
                                    id:response.opp_attorney_id,
                                    name:response.opp_attorney.name,
                                    bar_num: response.opp_attorney.bar_num,
                                });
                                opp_attorney_select.setValue(response.opp_attorney_id);
                            }
                        }
                    },
                    error: function(response) {
                        console.log('Error');
                    }
                });
            }
        });

        $('#motion').change(function() {
            $('#otherMotionShow').hide();

            if($(this).val() == 221 ){
                $('#otherMotionShow').show();
            }
        });

        $('#reschedule_button').on('click', function() {

            let calendar_reschedule = document.getElementById('reschedule-calendar');
            // Reschedule Calendar IO
            reschedule = new FullCalendar.Calendar(calendar_reschedule, {
                initialView: 'listMonth',
                height: 500,
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: ''
                },
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                navLinks: true,
                weekends: false,
                slotDuration: '00:05:00',
                slotMinTime: '09:00:00',
                slotMaxTime: '17:00:00',

                selectMirror: true,
                events: '{!! route('court-timeslots.show', $court->id)  !!}',
                eventConstraint: {
                    startTime: '09:00',
                    endTime: '17:00',
                    daysOfWeek: [ 1, 2, 3, 4, 5 ]
                },
                eventContent: function(arg){
                    return { html: '<div class="fc-event-main-frame"><div class="fc-event-time"> ' + arg.timeText + '</div><div class="fc-event-title-container"> '  + arg.event.title + '</div> </div>'  }
                },
                eventClick: function(info) {
                    rescheduleEvent(info);
                },

            });

            let filtered = '{!! route('available-timeslots.show', $court->id)  !!}?';

            filtered = filtered.concat('&duration=' + $('#timeslot #duration').val());

            filtered = filtered.concat('&motion=' + $('#newevent #motion').val());

            var source = reschedule.getEventSources();
            source[0].remove();
            reschedule.addEventSource(filtered)

            $('#create').modal('hide');

            $('#reschedule').modal('show');
            reschedule.render();

        });

        function evaluateformfields($changedfield) {

           var case_format_val = [];
           var case_num = '<?php echo $case_format->case_num_format ?> ';
           var valTokens = case_num.split("-");

           for(var i=1; i<=valTokens.length; i++) {
              var value = $("#case_num_format_multiple"+i).val();
              case_format_val.push(value);

            }
            $("#case_num").val(case_format_val.join('-'));
        }

        function changeLabel(courtType) {
            if(courtType == "GA") {
                document.getElementsByClassName("plaintiff_label")[0].innerHTML = "Ward";
                document.getElementsByClassName("plaintiff_email_label")[0].innerHTML = "Ward Email";
                document.getElementsByClassName("defendant_label")[0].innerHTML = "Petitioner";
                document.getElementsByClassName("defendant_email_label")[0].innerHTML = "Petitioner Email";
            }
            else if(courtType == "DR") {
                document.getElementsByClassName("plaintiff_label")[0].innerHTML = "Petitioner";
                document.getElementsByClassName("plaintiff_email_label")[0].innerHTML = "Petitioner Email";
                document.getElementsByClassName("defendant_label")[0].innerHTML = "Respondent";
                document.getElementsByClassName("defendant_email_label")[0].innerHTML = "Respondent Email";
            }
            else if(courtType == "MH") {
                document.getElementsByClassName("plaintiff_label")[0].innerHTML = "Petitioner";
                document.getElementsByClassName("plaintiff_email_label")[0].innerHTML = "Petitioner Email";
                document.getElementsByClassName("defendant_label")[0].innerHTML = "Patient";
                document.getElementsByClassName("defendant_email_label")[0].innerHTML = "Patient Email";
            }
            else{
                document.getElementsByClassName("plaintiff_label")[0].innerHTML = "Plaintiff";
                document.getElementsByClassName("plaintiff_email_label")[0].innerHTML = "Plaintiff Email";
                document.getElementsByClassName("defendant_label")[0].innerHTML = "Defendant";
                document.getElementsByClassName("defendant_email_label")[0].innerHTML = "Defendant Email";
            }
       }


        function get_dynamic_case_number_format_fields(case_num_format) {
            var fields = '';
            if(case_num_format != null) {
                var format = case_num_format;
                var split_format = format.split('-');
                if(split_format.length == 1) {
                    fields = '<label for="case_num">Case Number</label>'+

                    '<div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">'+

                        '<div class="col-md-12 mb-3">'+
                            '<label for="case_num"></label>'+
                            '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple1" required value="'+split_format[0]+'">'+
                            '<div class="valid-feedback">'+
                                'Looks good!'+
                                '</div>'+
                            '</div>';

                }
                else if(split_format.length == 2){

                    fields = '<label for="case_num">Case Number</label>'+
                    '<div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">'+
                        '<div class="col-md-4 mb-4">'+
                            '<label for="case_num"></label>'+
                            '<input type="text" class="form-control case_num_format_multiple" maxlength="4" id="case_num_format_multiple1" required value="'+split_format[0]+'">'+
                            '<div class="valid-feedback">'+
                                'Looks good!'+
                                '</div>'+
                            '</div>'+
                        '<div class="col-md-4 mb-4">'+
                            '<label for="case_num"></label>'+
                            '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple2" maxlength="7" required value="'+split_format[1]+'">'+
                            '<div class="valid-feedback">'+
                                'Looks good!'+
                                '</div>'+
                            '</div>'+
                        '</div>';
                }
                else if(split_format.length == 3){

                    if(split_format[1].length == 2 || split_format[1]==0 ){
                    fields = '<label for="case_num">Case Number</label>'+
                    '<div class="form-row col-md-12 case-format-row" style="margin:0px 0px 0px -20px;">'+
                        '<div class="col-md-2 mb-2">'+

                            '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple1"  maxlength="4" required value="'+split_format[0]+'">'+
                            '<div class="valid-feedback">'+
                                ' Looks good!'+
                                '</div>'+
                            '</div>'+
                        '<div class="col-md-2 mb-2">'+
                            '<select class="form-control col-md-12 case_num_format_multiple court_type_change_label" id="case_num_format_multiple2" required onChange="changeLabel(this.value);">';
                                var court_types = @php echo json_encode($court_types) @endphp;
                                $.each(court_types,function(key,court_type){
                                    var selected = (court_type["old_id"]==split_format[1]) ? "selected" : "";
                                    fields += '<option value="'+court_type["old_id"]+'"'+ selected +'>'+court_type["old_id"]+'</option>';
                                })

                                fields += '</select>'+
                            '</div>'+
                        '<div class="col-md-2 mb-2">'+
                            '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple3"  maxlength="7" required value="'+split_format[2]+'">'+
                            '<div class="valid-feedback">'+
                                'Looks good!'+
                                '</div>'+
                            '</div>'+
                        '</div>';
                    }
                    else{
                        fields = '<label for="case_num">Case Number</label>'+
                        '<div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">'+

                            '<div class="col-md-3 mb-3">'+
                                '<label for="case_num"></label>'+
                                ' <input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple1" maxlength="4" required value="'+split_format[0]+'">'+
                                ' <div class="valid-feedback">'+
                                    ' Looks good!'+
                                    ' </div>'+
                                ' </div>'+

                            ' <div class="col-md-3 mb-3">'+
                                ' <label for="case_num"></label>'+
                                ' <input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple2" maxlength="7" required value="'+split_format[1]+'">'+
                                ' <div class="valid-feedback">'+
                                    ' Looks good!'+
                                    ' </div>'+
                                ' </div>'+

                            ' <div class="col-md-3 mb-3">'+
                                '<label for="case_num"></label>'+
                                '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple3"   maxlength="4" required value="'+split_format[2]+'">'+
                                '<div class="valid-feedback">'+
                                    ' Looks good!'+
                                    ' </div>'+
                                '</div>'+
                            ' </div>';
                    }
                }
                else if(split_format.length == 6 || split_format.length == 5 || split_format.length == 4){
                   var input_type= (split_format.length == 6) ?  "hidden" : "text";
                    fields = '<label for="case_num">Case Number</label>'+
                    '<div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">'+

                        '<div class="col-md-1 mb-1">'+
                            '<label for="case_num"></label>'+
                            ' <input type= "'+input_type+'"  class="form-control case_num_format_multiple" id="case_num_format_multiple1" maxlength="2" required value="'+split_format[0]+'">'+
                            ' <div class="valid-feedback">'+
                                ' Looks good!'+
                                ' </div>'+
                            '</div>'+
                        '<div class="col-md-2 mb-2">'+
                            ' <label for="case_num"></label>'+
                            ' <input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple2"  maxlength="4" required value="'+split_format[1]+'" placeholder="Complete Year" style="font-weight: bold">'+
                            ' <div class="valid-feedback">'+
                                ' Looks good!'+
                                ' </div>'+
                            '</div>'+
                        ' <div class="col-md-2 mb-2">'+
                            ' <label for="case_num"></label>'+
                            '<select class="form-control col-md-12 case_num_format_multiple court_type_change_label" id="case_num_format_multiple3" required onChange="changeLabel(this.value);">';
                            var selected = (split_format[2]== 0) ? "selected" : "";
                            var court_types = @php echo json_encode($court_types) @endphp;
                            fields += '<option value=""'+ selected +'>'+'</option>';
                                $.each(court_types,function(key,court_type){
                                      selected = (court_type["old_id"]==split_format[2]) ? "selected" : "";
                                    fields += '<option value="'+court_type["old_id"]+'"'+ selected +'>'+court_type["old_id"]+'</option>';
                                })

                                fields += ' </select>'+
                            '</div>'+

                        '<div class="col-md-2 mb-2">'+
                            '<label for="case_num"></label>'+
                            '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple4" maxlength="6" required value="'+split_format[3]+'" placeholder="Case Number" style="font-weight: bold;">'+
                            ' <div class="valid-feedback">'+
                                'Looks good!'+
                                '</div>'+
                            '</div>'+
                        ' <div class="col-md-2 mb-2">'+
                            '<label for="case_num"></label>'+
                            '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple5" maxlength="4" required value="'+split_format[4]+'">'+
                            '<div class="valid-feedback">'+
                                ' Looks good!'+
                                '</div>'+
                            ' </div>'+
                        ' <div class="col-md-1 mb-1">'+
                            '<label for="case_num"></label>'+
                            '<input type= "'+input_type+'" class="form-control case_num_format_multiple" id="case_num_format_multiple6"  maxlength="2" required value="'+split_format[5]+'">'+
                            '<div class="valid-feedback">'+
                                ' Looks good!'+
                                '</div>'+
                            '</div>'+
                        '</div>';
                    }
            }
            else{
                fields ='<label for="case_num">Case Number</label>'+
                ' <div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">'+
                    '<div class="col-md-4 mb-3">'+

                        '<input type="text" class="form-control case_num_format_multiple" required>'+
                        '<div class="valid-feedback">'+
                            ' Looks good!'+
                            '</div>'+
                        '</div>'+
                    '</div>';
            }

            $(".dynamic_case_number_format").html(fields);

            changeLabel(document.getElementsByClassName("court_type_change_label")[0].value);

            $('.case_num_format_multiple').keyup(function() {
               evaluateformfields($(this));
            });

            $('.case_num_format_multiple').change(function() {
               evaluateformfields($(this));
            });
        }
    </script>
@endsection
