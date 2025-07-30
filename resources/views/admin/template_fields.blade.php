<div class="row" id="education_fields">
          @if($isType == 'edit')
            @foreach($court_templates as $index => $court_template)
            <div class="col-4 basic_grid removeclass{{$index+1}}">
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <div class="input-group-btn" style="float:right">
                            @if(count($court_templates) == ($index+1))
                            <button style="" class="btn btn-success" type="button" onclick="education_fields();"> <span
                                    class="bi bi-1-square" aria-hidden="true"></span> + </button>
                                    @else
                            <button style="" class="btn btn-danger" type="button" onclick="delete_court_template({{$court_template->id}});">
                            <span class="bi bi-1-square" aria-hidden="true"></span> - </button>
                            @endif
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <labe>Field Name:</labe>
                        <input type="hidden" name="templated_id[{{$index+1}}]" value="{{$court_template->id}}">
                        <input type="text" class="form-control" id="" name="field_name[{{$index+1}}]" value="{{$court_template->field_name}}"
                            placeholder="Field  Name">
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <labe>Field Type:</labe>
                        <select class="form-control" id="educationDate" name="field_type[{{$index+1}}]" onchange="getval(this,{{$index+1}});">
                            <option value="DATE" {{ $court_template->field_type == "DATE" ? 'selected' : ''}} >DATE</option>
                            <option value="EMAIL" {{ $court_template->field_type == "EMAIL" ? 'selected' : ''}}>EMAIL</option>
                            <option value="TEXT" {{ $court_template->field_type == "TEXT" ? 'selected' : ''}}>TEXT</option>
                            <option value="yes_no" {{ $court_template->field_type == "yes_no" ? 'selected' : ''}}>YES/NO</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <labe>Alignment:</labe>
                        <select class="form-control" id="alignment" name="alignment[{{$index+1}}]">
                            <option value="LEFT" {{ $court_template->alignment == "LEFT" ? 'selected' : ''}}>LEFT</option>
                            <option value="CENTER" {{ $court_template->alignment == "CENTER" ? 'selected' : ''}}>CENTER</option>
                            <option value="RIGHT" {{ $court_template->alignment == "RIGHT" ? 'selected' : ''}}>RIGHT</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <labe>Default Value:</labe>
                        <input type="text" class="form-control" id="default_{{$index+1}}" name="default_value[{{$index+1}}]" value="{{$court_template->default_value}}"
                            placeholder="Default Value">
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="col-md-6 custome_fields_cls_6">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" value="1" id="required_{{$index+1}}"  name="required[{{$index+1}}]" {{ $court_template->required == "1" ? 'checked' : ''}}>Required
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 custome_fields_cls_6">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input yesno" value="1" name="yes_answer_required[{{$index+1}}]"
                                    id="yes_no_chk_{{$index+1}}" {{ $court_template->yes_answer_required == "1" ? 'checked' : ''}}>'Yes' Answer Required
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="col-md-6 custome_fields_cls_6">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" value="1" name="display_on_docket[{{$index+1}}]" {{ $court_template->display_on_docket == "1" ? 'checked' : ''}}> Display On Docket
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 custome_fields_cls_6">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" value="1" name="display_on_schedule[{{$index+1}}]" {{ $court_template->display_on_schedule == "1" ? 'checked' : ''}}>Display On Schedule
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 nopadding custome_fields_cls_6" style="padding: 0px 30px;">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" value="1"  name="use_in_attorany_scheduling[{{$index+1}}]" {{ $court_template->use_in_attorany_scheduling == "1" ? 'checked' : ''}}> Use in Attorney
                            Scheduling
                        </label>
                    </div>
                </div>
            </div>
            @endforeach
        @else
        <div class="col-4 basic_grid">
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <div class="input-group-btn" style="float:right">
                            <button style="" class="btn btn-success" type="button" onclick="education_fields();"> <span
                                    class="bi bi-1-square" aria-hidden="true"></span> + </button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <labe>Field Name:</labe>
                        <input type="text" class="form-control" id="" name="field_name[1]" value=""
                            placeholder="Field  Name">
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <labe>Field Type:</labe>
                        <select class="form-control" id="educationDate" name="field_type[1]" onchange="getval(this,1);">
                            <option value="DATE">DATE</option>
                            <option value="EMAIL">EMAIL</option>
                            <option value="TEXT" selected>TEXT</option>
                            <option value="yes_no">YES/NO</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <labe>Alignment:</labe>
                        <select class="form-control" id="alignment" name="alignment[1]">
                            <option value="LEFT">LEFT</option>
                            <option value="CENTER">CENTER</option>
                            <option value="RIGHT">RIGHT</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="form-group custome_filed_cls">
                        <labe>Default Value:</labe>
                        <input type="text" class="form-control" id="default_1" name="default_value[1]" value=""
                            placeholder="Default Value">
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="col-md-6 custome_fields_cls_6">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox"   class="form-check-input" value="1" name="required[1]">Required
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 custome_fields_cls_6">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input yesno" value="1" name="yes_answer_required[1]"
                                    id="yes_no_chk_1">'Yes' Answer Required
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 nopadding">
                    <div class="col-md-6 custome_fields_cls_6">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" value="1" name="display_on_docket[1]"> Display On Docket
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 custome_fields_cls_6">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" value="1" name="display_on_schedule[1]">Display On Schedule
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 nopadding custome_fields_cls_6" style="padding: 0px 30px;">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" value="1"  name="use_in_attorany_scheduling[1]"> Use in Attorney
                            Scheduling
                        </label>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
<script>
    $(document).ready(function() {
    $(".yesno").prop('disabled', true);
});
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
@if($isType == 'edit')
var room = <?=$court_templates->count();?>;
@else
var room = 1;
@endif


function education_fields() {

    room++;
    var mySecondDiv = $('<div class="col-4 basic_grid removeclass' + room + '">' +
        '<div class="col-sm-12 nopadding">' +
        '<div class="form-group custome_filed_cls">' +
        '<div class="input-group-btn" style="float:right">' +
        '<button style="" class="btn btn-danger" type="button" onclick="remove_education_fields(' + room +
        ');"> <span class="bi bi-1-square" aria-hidden="true"></span> - </button>' +
        '</div>' +
        '<div class="clearfix"></div>' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-12 nopadding">' +
        '<div class="form-group custome_filed_cls">' +
        '<labe>Field Name:</labe>' +
        '<input type="text" class="form-control" id="" name="field_name[' + room +']" value="" placeholder="Field  Name">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-12 nopadding">' +
        '<div class="form-group custome_filed_cls">' +
        '<labe>Field Type:</labe>' +
        '<select class="form-control" id="educationDate" name="field_type[' + room +']" onchange="getval(this,' + room +
        ');">' +
        '<option value="DATE">DATE</option>' +
        '<option value="EMAIL">EMAIL</option>' +
        '<option value="TEXT" selected>TEXT</option>' +
        '<option value="yes_no">YES/NO</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-12 nopadding">' +
        '<div class="form-group custome_filed_cls">' +
        '<labe>Alignment:</labe>' +
        '<select class="form-control" id="alignment" name="alignment[' + room +']">' +
        '<option value="LEFT">LEFT</option>' +
        '<option value="CENTER">CENTER</option>' +
        '<option value="RIGHT">RIGHT</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-12 nopadding">' +
        '<div class="form-group custome_filed_cls">' +
        '<labe>Default Value:</labe>' +
        '<input type="text" class="form-control" id="default_' + room +
        '" name="default_value[' + room +']" value="" placeholder="Default Value">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-12 nopadding">' +
        '<div class="col-md-6 custome_fields_cls_6">' +
        '<div class="form-check">' +
        '<label class="form-check-label">' +
        '<input type="checkbox" class="form-check-input" value="1" id = "required_' + room +'" name="required[' + room +']">Required' +
        '</label>' +
        '</div>' +
        '</div>' +
        '<div class="col-md-6 custome_fields_cls_6">' +
        '<div class="form-check">' +
        '<label class="form-check-label">' +
        '<input type="checkbox" class="form-check-input yesno" value="1"  id="yes_no_chk_' + room +
        '" name="yes_answer_required[' + room +']"> "Yes" Answer Required' +
        '</label>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-12 nopadding">' +
        '<div class="col-md-6 custome_fields_cls_6">' +
        '<div class="form-check">' +
        ' <label class="form-check-label">' +
        '<input type="checkbox" class="form-check-input" value="1" name="display_on_docket[' + room +']"> Display On Docket' +
        '</label>' +
        '</div>' +
        '</div>' +
        '<div class="col-md-6 custome_fields_cls_6">' +
        '<div class="form-check">' +
        '<label class="form-check-label">' +
        '<input type="checkbox" class="form-check-input" value="1" name="display_on_schedule[' + room +']">Display On Schedule' +
        '</label>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-12 nopadding custome_fields_cls_6" style="padding: 0px 30px;">' +
        '<div class="form-check">' +
        '<label class="form-check-label">' +
        '<input type="checkbox" class="form-check-input"  value="1" name="use_in_attorany_scheduling[' + room +']"> Use in Attorney Scheduling' +
        '</label>' +
        '</div>' +
        '</div>' +
        '</div>'
    );
    $("#education_fields").append(mySecondDiv)
    $(".yesno").prop('disabled', true);
}
// alert($court_templates_count);
function remove_education_fields(rid) {
    $('.removeclass' + rid).remove();
}


</script>
