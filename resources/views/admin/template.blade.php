@extends(backpack_view('blank'))



@section('content')

    <h1>{{ $court->description }} - {{ $template->name }}</h1>
    <a href="#" onclick = "return multiDeleteTimeslot();" class="btn btn-secondary  m-1" id="multi_delete">
        <span class=""><i class="la la-lg la-trash mr-2"></i>Delete Timeslot(s)</span>
    </a>
    <a href="#" onclick = "return multiCopyTimeslot();" class="btn btn-secondary  m-1" id="copy">
        <span class=""><i class="la la-lg la-copy mr-2"></i>Copy Timeslot(s)</span>
    </a>
    <div id='calendar'></div>

@endsection

@section('before_scripts')
    <div class="modal fade" id="create" tabindex="-1" role="dialog" aria-labelledby="Create Modal" aria-hidden="true">

        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">

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
                </ul>

                <div class="modal-body tab-content">
                    <div class="tab-pane fade" id="timeslot-tab" role="tabpanel" aria-labelledby="timeslot-tab">
                        <form id="timeslot" data-action="{{ route('court_template.store') }}">
                            <input type="hidden" name="court_id" value="{{$court->id}}" />
                            <input type="hidden" name="template_id" value="{{$template->id}}" />

                            <div class="form-row">
                                <div class="col-md-2 blocking">
                                    <div class="form-group">

                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="blocked" name="blocked" >
                                            <label class="form-check-label" for="blocked">Block</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 public_block" style="display: none">
                                    <div class="form-group">

                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="public_block" name="public_block" >
                                            <label class="form-check-label" for="public_block">Public Block</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3 block_reason" style="display: none">
                                    <label for="block_reason">Block Reason</label>
                                    <input type="text" class="form-control " name="block_reason" id="block_reason" >
                                </div>

                            </div>
                            <div class="form-row">

                                <div class="col-md-6 mb-3 cattle-call">
                                    <label for="validationServer02">Calendar Call?</label>
                                    <div class="form-group">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" onclick="showQuantity()" type="radio"  name="cattlecall" id="cattlecall_yes" value="1" checked="checked" />
                                            <label class="form-check-label"  for="cattlecall_yes">Yes (Concurrent)</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" onclick="hideQuantity()" type="radio" name="cattlecall" id="cattlecall_no" value="0" />
                                            <label class="form-check-label" for="cattlecall_no">No (Consecutive)</label>
                                        </div>
                                    </div>
                                </div>

                            </div>



                            <div class="form-row time-selection">
                                <div class="col-md-2 mb-3">
                                    <div class="form-group">
                                        <label for="timeslot_start">Start Time</label>
                                        <div class="input-group date" id="timeslot_start" data-target-input="nearest">
                                            <input type="text" name="timeslot_start" class="form-control datetimepicker-input"  data-target="#timeslot_start"/>
                                        </div>
                                        <input type="hidden" name="t_start" id="t_start" />
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-group">
                                        <label for="validationServer02">End Time</label>
                                        <div class="input-group date" id="timeslot_end" data-target-input="nearest">
                                            <input type="text" name="timeslot_end" class="form-control datetimepicker-input" data-target="#timeslot_end"/>

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
                                        <label for="validationServer02">Quantity</label>
                                        <input type="number" name="quantity" id="quantity" class="form-control" required/>
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
                            <div class="form-row restricted">
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
                                <div class="col-md-6 text-left">
                                    <a class="btn btn-danger delete-button" href="#" onclick="deleteTimeslot()">Delete</a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('after_styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

    <style>
        .fc-event-main{
            overflow: hidden;
        }
    </style>

    <script>

        let calendar = null;
        let multi_timeslots = [];
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

            // Full Calendar IO
            calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: { left: '', center: '', right: 'timeGridWeekCustom,listMonthCustom' },
                initialView: 'timeGridWeekCustom',
                initialDate: '2021-11-01',
                views: {
                    timeGridWeekCustom: { // name of view
                        type: 'timeGridWeek',
                        dayHeaderFormat: { weekday: 'long', omitCommas: true}
                    },
                    listMonthCustom: { // name of view
                        type: 'listMonth',
                        listDayFormat: { weekday: 'long', omitCommas: true},
                        listDaySideFormat: false
                    },

                },
                editable: true,
                selectable: true,
                events: '{!! route('court_template.show', $template->id)  !!}',
                weekends: false,
                slotDuration: '00:05:00',
                slotMinTime: '08:00:00',
                slotMaxTime: '17:30:00',
                selectMirror: true,
                select: function(info) {
                    setupModal(info);
                },

                eventConstraint: {
                    startTime: '08:00',
                    endTime: '17:30',
                    daysOfWeek: [ 1, 2, 3, 4, 5 ]
                },
                eventContent: function(arg){
                    let italicEl = document.createElement('div')
                    let something = document.createElement('br')
                    let test = document.createElement('span')

                   test.innerHTML = arg.timeText + '<input style="top: .8rem;width: .95rem;height: .95rem;" class="m-1 float-right" disabled type="checkbox" id="cb' + arg.event.id + '"  value="' + arg.event.id + '"/>'

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
                        editTimeslot(info);
                    }
                },
                eventResize: function(info) {
                    updateTimeslot(info);
                },
                eventDrop: function (info){
                    newday = info.event.start.getDay();
                    let old_time = moment(info.oldEvent.start);
                    let difference = moment(info.event.start).diff(old_time);

                    const index = dragEvents.indexOf(info.event.id);
                    dragEvents.splice(index, 1);

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
                }


            });
            calendar.render();

        });
    </script>
@endsection
@section('after_scripts')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.27.0/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-3JRrEUwaCkFUBLK1N8HehwQgu8e23jTH4np5NHOmQOobuC4ROQxFwFgBLTnhcnQRMs84muMh0PnnwXlPq5MGjg==" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-k6/Bkb8Fxf/c1Tkyl39yJwcOZ1P4cRrJu77p83zJjN2Z55prbFHxPs9vN7q3l3+tSMGPDdoH51AEU8Vgo1cgAA==" crossorigin="anonymous"></script>

    <script>
        let modal ='#create';
        let events = null;

        $("#blocked").change(function() {
            if(this.checked) {
               $('.public_block').show();
               $('.block_reason').show();
            } else{
                $('.public_block').hide();
                document.getElementById("public_block").checked = false;
                $('.block_reason').hide();
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

        // Timeslot Form Submit
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
                    console.log('Error');
                }
            });
        });

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
                    multi_timeslots = [];
                    dragEvents = [];
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        }

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

        function editTimeslot(e){
            let url = e.event.extendedProps.edit_url;
            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'JSON',
                success:function(data)
                {
                    let start_formatted = moment(data["start"]);
                    let end_formatted = moment(data["end"]);
                    events = data['events'];

                    // Formatting Modal for new data
                    $('.time-selection').show();
                    $('.cattle-call').hide();
                    $('.delete-button').show();
                    $('.restricted').show();
                    $('#modal-title').text(moment(data["start"]).format('ddd, h:mm a') + ' - ' + moment(data["end"]).format('h:mm a'));
                    $('#timeslot_start').datetimepicker('date', start_formatted.format('h:mm a'));
                    $('#timeslot_end').datetimepicker('date', end_formatted.format('h:mm a'));

                    // Setting Timeslot data within Modal
                    $("#quantity").val(data["quantity"]);
                    $('#t_start').val(data["start"]);
                    $("#t_end").val(data["end"]);
                    $("#description").attr('value', data["description"]);
                    $("#block_reason").attr('value', data["block_reason"]);
                    $('#category option[value="' + data["category_id"] +'"]').prop('selected', true);
                    $('#duration option[value="' + data["duration"] +'"]').prop('selected', true);

                    data["motions"].forEach(element => {
                        timeslotmotions_select.addItem(element.motion_id);
                    })

                    if(data["blocked"]){
                        $('#blocked').prop('checked', 'checked')
                    }
                    if(data["public_block"]){
                        $('#public_block').prop('checked', 'checked')
                    }

                    if(document.getElementById("blocked").checked) {
                        $('.public_block').show();
                        $('.block_reason').show();
                    } else{
                        $('.public_block').hide();
                        document.getElementById("public_block").checked = false;
                        $('.block_reason').hide();
                    }

                    $('.delete-button').attr('onclick', 'deleteTimeslot(' + e.event.id + ')');
                    if(data["allDay"]){
                        $('.cattle-call').hide();
                        $('.time-selection').hide();
                        $('.restricted').hide();
                        $('#duration').removeAttr('required');
                        $('#quantity').removeAttr('required');

                    }
                    $('#timeslot-tab-link').tab('show');

                    // Setting background data for Event creation
                    $('#timeslot').attr('data-action', e.event.extendedProps.update_url);
                    $('#timeslot').append('<input type="hidden" id="method" name="_method" value="PUT" />');
                    $('#newevent').append('<input type="hidden" name="timeslot_id" value="' + data["id"] +'" />')


                    $("#create").modal('show');
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        }

        // Display modal on timeslot selection
        function setupModal(info){

            $('.quantity-group').show();
            $('.cattle-call').show();
            $('.time-selection').show();
            $('.restricted').show();
            $('.public_block').hide();
            $('.block_reason').hide();

            $('#cattlecall_yes').prop('checked','checked');

            // $('.blocking').hide();
            $('.delete-button').hide();
            $('#event-nav').hide();
            $('#events-nav').hide();


            $("#duration").attr("required", true);
            $("#quantity").attr("required", true);

            $('#modal-title').text(moment(info.start).format('dddd @ h:mm a') + ' - ' + moment(info.end).format('h:mm a'));
            $('#timeslot_start').datetimepicker('date', info.start);
            $('#t_start').val(moment(info.start).format('YYYY-MM-DD HH:mm:ss'));
            $('#timeslot_end').datetimepicker('date', info.end);
            $('#t_end').val(moment(info.end).format('YYYY-MM-DD HH:mm:ss'));

            $('#timeslot').attr('data-action', '{{ route('court_template.store') }}');

            if(info.allDay){
                $('.blocking').css('display','');
                $('.cattle-call').css('display','none');
                $('.time-selection').css('display','none');
                $('.restricted').hide();
                $('#duration').removeAttr('required');
                $('#quantity').removeAttr('required');
            }


            // Finally display Modal and set Timesolt as default
            $("#create").modal('show');
            $('#timeslot-tab-link').tab('show');
        }

        // AJAX Timeslot Deletion Function
        function deleteTimeslot(e){
            let url = '{{ env('APP_URL')  }}/court_template/' + e

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

        // Reset Form values when modal is Hidden
        $('#create').on('hidden.bs.modal', function () {
            $('#timeslot').trigger("reset");
            $("#description").attr('value','');
            $("#block_reason").attr('value','');
            $('#timeslot #method').remove();
            $('#timeslot-tab-link').tab('show')
            $('#timeslot-tab-link').css('display','');
            timeslotmotions_select.clear();
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


        function multiDeleteTimeslot(){
            let url = '{{ env('APP_URL')  }}/timeslot/temp_multi'
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
            let url = '{{ env('APP_URL')  }}/timeslot/temp_copy'
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

        $('#create').on('hidden.bs.modal', function () {

            $('.public_block').hide();
            $('.block_reason').hide();
        });



    </script>
@endsection
