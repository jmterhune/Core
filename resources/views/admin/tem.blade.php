@extends(backpack_view('blank'))

@php

    $court_display = \App\Models\Court::find($court);
    $breadcrumbs = [
        'Admin' => backpack_url('dashboard'),
        'Court' => backpack_url('court'),
        $court_display->description => route('calendar.show', $court_display->id),
        'User Defined Fields' => false,
    ];


@endphp

@section('content')
<style>
.basic_grid {
    border: 2px solid #9b97973d;
    padding: 10px 7px;
    margin: 1px 0px;
}

.custome_filed_cls {
    margin-bottom: 0.5rem !important;
}

.custome_fields_cls_6 {
    float: left;
    margin: 8px 0px;
}

input[type=checkbox] {
    /* Double-sized Checkboxes */
    -ms-transform: scale(2);
    /* IE */
    -moz-transform: scale(2);
    /* FF */
    -webkit-transform: scale(2);
    /* Safari and Chrome */
    -o-transform: scale(2);
    /* Opera */
    padding: 10px;
}
</style>

<h2>
   <a class="text-primary" href="{{ route('calendar.show', $court_display->id) }}"> {{ $court_display->description }}'s</a> User Defined Fields
</h2>

<div class="{{ $widget['class'] ?? 'alert alert-info' }}" role="alert">
    <h4 class="alert-heading">Note</h4>
    <p>
        This sections allows a user to add custom fields to be displayed on the schedule, docket, or attorney scheduling.
    </p>
</div>

<div class="container-fluid">
    <form id="templetes" data-action="{{ route('user_defined_fields.store') }}">

        @csrf
        <input type="hidden" name="court_id" value="{{$court}}">
        <div id="template_fields">
        </div>
        <div class="col-md-12 text-left" style="padding:20px;">
            <!-- This makes sure that all field assets are loaded. -->
            <div id="saveActions" class="form-group">

                <button type="submit" class="btn btn-success">
                    <span class="la la-save" role="presentation" aria-hidden="true"></span> &nbsp;
                    <span data-value="save_and_edit">Save</span>
                </button>


                <a href="{{ route('calendar.show', $court) }}" class="btn btn-default"><span class="la la-ban"></span> &nbsp;Cancel</a>
            </div>
        </div>
    </form>
</div>


@endsection

@section('before_scripts')


@endsection
@section('after_styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">


@endsection
@section('after_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.27.0/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css"
    integrity="sha512-3JRrEUwaCkFUBLK1N8HehwQgu8e23jTH4np5NHOmQOobuC4ROQxFwFgBLTnhcnQRMs84muMh0PnnwXlPq5MGjg=="
    crossorigin="anonymous" />
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"
    integrity="sha512-k6/Bkb8Fxf/c1Tkyl39yJwcOZ1P4cRrJu77p83zJjN2Z55prbFHxPs9vN7q3l3+tSMGPDdoH51AEU8Vgo1cgAA=="
    crossorigin="anonymous"></script>
<script>

function getval(sel, rid) {
    if (sel.value == "CHARGE" || sel.value == "yes_no") {
        $("#default_" + rid).prop('disabled', true);
    } else {
        $("#default_" + rid).prop('disabled', false);
    }
    if (sel.value == "yes_no") {
	    $("#yes_no_chk_" + rid).prop('disabled', false);
	    $("#required_" + rid).prop('disabled', true);
    } else {
	    $("#yes_no_chk_" + rid).prop('disabled', true);
	    $("#required_" + rid).prop('disabled', false);
    }
    // alert(sel.value);
}
$(document).ready(function() {
    AjaxfieldsLoad();
});

let templetes_form = '#templetes';
$(templetes_form).on('submit', function(event) {
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
        success: function(response) {
            $(templetes_form).trigger("reset");
            //location.reload();
            AjaxfieldsLoad(true);

        },
        error: function(response) {
            console.log('Error');
        }
    });
});

function delete_court_template(id)
{
    $.ajax({
    url:'',
            method: 'DELETE',
            datatype: 'JSON',
            data: {'_token':"{{ csrf_token() }}",'id':id},
            success: function (res) {
                AjaxfieldsLoad();
            }
    });


    // $.ajaxSetup({
    //     headers: {
    //         'X-CSRF-TOKEN': "{{ csrf_token() }}"
    //     }
    // });
    // $.ajax({
    //     url: "delete",
    //     type: 'DELETE',
    //     data: {"id": id},
    //     dataType: 'JSON',
    //     contentType: false,
    //     cache: false,
    //     processData: false,
    //     success: function(response) {
    //         $('.removeclass' + id).remove();
    //     },
    //     error: function(response) {
    //         console.log('Error');
    //     }
    // });
}

function AjaxfieldsLoad(flash = false){
    $.ajax({
    url:'<?= route('user_defined.fields')?>',
            type: 'get',
            datatype: 'html',
            data: {'_token':"{{ csrf_token() }}",'court_id':"{{ $court }}"},
            success: function (res) {
            $('#template_fields').html(res);
            if(flash === true)
            {
                new Noty({
                        type: "success",
                        text: 'User Defined Fields added/updated successfully',
                    }).show();
            }
            }
    });
}
</script>

@endsection
