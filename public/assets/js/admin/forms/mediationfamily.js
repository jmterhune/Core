var server_url= "https://jacs-admin.flcourts18.org";
$(document).ready(function() {
    // commaSeparated();
 $("form").hide();
const urlParams = new URLSearchParams(window.location.search);
if(urlParams.get('case_number')){
    $("input[name='c_caseno']").val(urlParams.get('case_number'));
     $("form").show();
}
else if(window.location.href.indexOf("edit") > -1)
{
    $("form").show();
}

if (urlParams.get('event_id')) {
    editEventSchedule(urlParams.get('event_id'));
}
    // $("form").addClass("form-control form-control-sm")
    $('select[name=Dd_time] option:eq(1)').attr('selected', 'selected');
    searchEvents();
    $("select[name='c_Pltf_a_id']").on('change', function() {
        $.ajax({
            url: server_url+'/attorney/details/'+this.value,
            method: 'GET',
            dataType: 'JSON',
            async:false,
            success:function(response)
            {
                $("input[name='p_a_phone']").val(response.phone);
                if(response.email.length > 0)
                {
                    $("input[name='p_a_email']").val(response.email[0].email);
                }
            }
        });
    });

    $("select[name='c_def_a_id']").on('change', function() {
    $.ajax({
        url: server_url+'/attorney/details/'+this.value,
        method: 'GET',
        dataType: 'JSON',
        async:false,
        success:function(response)
        {
            $("input[name='d_a_phone']").val(response.phone);
            if(response.email.length > 0)
            {
                $("input[name='d_a_email']").val(response.email[0].email);
            }
        }
    });
    });



    //0 - 50,000 annual combined income is $60
    //50,001 - 100,000 annual combined income is $120
    $('.petcalculation input').on('change', function() {

        feeCalculation();

    });

    $('.rescalculation input').on('change', function() {
        feeCalculation();

    });

    $("#other_matters").on('change', function() {
        console.log("====");
        if(this.checked)
        {
            $(".f_issues_other_notes_dummy").show();
        }
        else
        {
            $(".f_issues_other_notes_dummy").hide();
        }
    });

    $(".previous input").on('change', function() {
        if(this.value == 1)
        {
            $(".previousecase").show();
        }
        else
        {
            $(".previousecase").hide();
        }
    });
});
$(window).on('load', function() {
   // console.log("comma");
    var numberFields = document.querySelectorAll('.comma-separated input');
    numberFields.forEach(function(field) {
        field.addEventListener('input', function() {
            var value = this.value.replace(/,/g, ''); // Remove existing commas
            this.value = (value != "") ? parseFloat(value).toLocaleString() : 0; // Format with commas
        });
        // console.log(field.name);
        let currvalue = field.value.replace(/,/g, ''); // Remove existing commas
        currvalue = (currvalue != "") ? parseFloat(currvalue).toLocaleString() : 0; // Format with commas
        $('input[name="'+field.name+'"]').val(currvalue);
    });
});
$(".f_issues_other_notes_dummy").on('change', function() {
    console.log(this.value);
    f_issues_other_notes(this.value);
});

function f_issues_other_notes(value){
    console.log("=="+value);
    $("input[name='f_issues_other_notes']").val(value);
}

function feeCalculation()
{
    let pltf_annl_chg = ($('input[name="e_pltf_annl_chg"]').val() != "") ? parseFloat(($('input[name="e_pltf_annl_chg"]').val()).replace(/,/g, '')) : 0;
    let def_annl_chg = ($('input[name="e_def_annl_chg"]').val() != "") ? parseFloat(($('input[name="e_def_annl_chg"]').val()).replace(/,/g, '')) : 0;
    let e_pltf_annl_chg = parseInt(pltf_annl_chg) + parseInt(def_annl_chg);
        console.log(e_pltf_annl_chg);
        if(e_pltf_annl_chg >= 0 && e_pltf_annl_chg <= 50000)
        {
            $('input[name="e_pltf_chg"]').val(60);
            $('input[name="e_def_chg"]').val(60);
        }
        else{
            $('input[name="e_pltf_chg"]').val(120);
            $('input[name="e_def_chg"]').val(120);
        }

        if($('.petcalculation input[type="checkbox"]').is(":checked"))
        {
            $('input[name="e_pltf_chg"]').val(0);
        }

        if($('.rescalculation input[type="checkbox"]').is(":checked"))
        {
            $('input[name="e_def_chg"]').val(0);
        }
}
function searchCaseNumber(){
    let url = server_url+'/mediationfamily/case/search';
    if($("#case_number_search").val() != ""){
        $.ajax({
            url: url,
            method: 'POST',
            data: {case_number: $("#case_number_search").val()},
            dataType: 'JSON',
            success:function(response)
            {
                //console.log(response)
                if(response == null)
                {
                    window.location.replace(server_url+"/mediationfamily/create?case_number="+$("#case_number_search").val());
                }
                else{
                    window.location.replace(server_url+"/mediationfamily/"+response.id+"/edit");
                    // $.each(response, function (key,value){

                    //     if($('textarea[name="'+key+'"]').attr('data-ms-editor'))
                    //     {
                    //         $('textarea[name="'+key+'"]').text(value);
                    //     }
                    //     else if($('input[name="'+key+'"]').attr('type') === 'checkbox' && value == 1)
                    //     {
                    //         $('input[name="'+key+'"]').prop("checked",true);
                    //     }
                    //     else if($('input[name="'+key+'"]').attr('type') === 'radio')
                    //     {
                    //         $('input[type="radio"][value='+value+']').prop("checked",true);
                    //     }
                    //     else{
                    //         $('input[name="'+key+'"]').val(value);
                    //     }
                    // });

                    // //change form action to perform update
                    // $("form").attr('action', $("form").attr("action")+"/"+$('input[name="id"]').val());
                    // // $("form").attr('method', "PUT");
                    // $('input[name="_save_action"]').val("save_and_back");
                    // $('span[data-value="save_and_edit"]').data('value', 'save_and_back');
                    // //$('span[data-value="save_and_edit"]').attr('data-value','save_and_back');
                    // // $("input[name='_http_referrer']").attr('action', $("form").attr("action")+"/"+$('input[name="id"]').val()+"/edit")
                    // $("input[name='_http_referrer']").after('<input type="hidden" name="_method" value="PUT">');
                }
            },
            error: function(error) {
                window.location.replace(server_url+"/mediationfamily/create?case_number="+$("#case_number_search").val());
            }
        });
    }
}


function otherNotes(other)
{
    if(other.checked){
        $("#f_issues_other_notes").show();
    }
    else
    {
        $("#f_issues_other_notes").hide();
    }
}
function searchEvents()
{
    if($('input[name="Tb_sch_date"]').val() == "" || $('select[name="Dd_time"]').val() == "")
    {
        alert("Please select date and schedule");
        return;
    }
    $(".searchEventResults").empty();
    let url = server_url+'/mediationfamily/event/search';
    if($('input[name="id"]').val() !=""){
        $.ajax({
            url: url,
            method: 'POST',
            data: {Tb_sch_date: $('input[name="Tb_sch_date"]').val(),Dd_time: $('select[name="Dd_time"]').val(),c_id: $('input[name="id"]').val()},
            dataType: 'JSON',
            success:function(response)
            {
                let eventTable = '<h3 class="sub-header col-xs-6">Scheduled Events</h3>';
                eventTable += "<table class='table col-xs-6 table-striped'>";

                eventTable += "<tr><td></td><td>Case Number</td><td>Mediator</td><td>Date:Time</td><td>Length</td><td>Petitioner Charge</td><td>Respondent Charge</td><td>Outcome</td><td>Respondent FTA</td><td>Petitioner FTA</td><td>Notes</td></tr>";

                response.forEach(function(event) {

                    eventTable +="<tr>";

                    eventTable += "<td><a href='javascript:void(0);' onClick='return editEventSchedule("+event.id+");'>Edit</a> / <a href='javascript:void(0);' onClick='return deleteEvent("+event.id+");'>Delete</a></td>";

                    eventTable += "<td>"+event.case.c_caseno+"</td>";

                    eventTable += "<td>"+event.medmaster.name+"</td>";
                    //eventTable += "<td style='width:15%'>"+event.ESchDatetimeAmpm+"</td>";
                    eventTable += "<td style='width:12%'>";
                    eventTable += "<div>" + event.ESchDatetimeAmpm.split(" ")[0] + "</div>"; // Date in first line
                    eventTable += "<div>" + event.ESchDatetimeAmpm.split(" ")[1] +" "+ event.ESchDatetimeAmpm.split(" ")[2] + "</div>"; // Time in second line
                    eventTable += "</td>";
                    eventTable += "<td>"+event.e_sch_length.slice(0, -3)+"</td>";

                    // eventTable += "<td>"+event.e_med_fee+"</td>";

                    eventTable += "<td>$ "+event.e_pltf_chg+"</td>";

                    eventTable += "<td>$ "+event.e_def_chg+"</td>";

                    eventTable += "<td>"+((event.outcome != null) ? event.outcome.o_outcome : "")+"</td>";

                    if(event.e_def_failedtoap == 1)
                    {
                        eventTable += "<td><input type='checkbox' disabled checked></td>";
                    }
                    else{
                        eventTable += "<td><input type='checkbox' disabled></td>";
                    }

                    if(event.e_pltf_failedtoap == 1)
                    {
                        eventTable += "<td><input type='checkbox' disabled checked></td>";
                    }
                    else{
                        eventTable += "<td><input type='checkbox' disabled></td>";
                    }


                    eventTable += "<td>"+((event.e_notes == null) ? "" : event.e_notes)+"</td>";

                    eventTable += "<td></td>";

                    eventTable +="</tr>";
                    $("#case_mediation_payment").show();
                });



                eventTable += "</table>";
                if(response.length == 0){
                    $("#case_mediation_payment").hide();
                }
                showAvaiableTimings();
                $(".searchEventResults").append(eventTable);
                // console.log(eventTable);
                //
            },
            error: function(error) {
                alert(error);
            }
        });
    }
}

function showAvaiableTimings()
{
    let url = server_url+'/mediationfamily/availabeTimings';
    $.ajax({
        url: url,
        method: 'POST',
        data: {Tb_sch_date: $('input[name="Tb_sch_date"]').val(),Dd_time: $('select[name="Dd_time"]').val(),c_id: $('input[name="id"]').val()},
        dataType: 'JSON',
        success:function(response)
        {
            let availScheduleTable = '<h3 class="sub-header col-xs-6">Available Mediators</h3>';
            availScheduleTable += "<table class='table col-xs-6 table-striped'>";

            availScheduleTable += "<tr><td></td><td>Avl. Time</td><td>First</td><td>Date</td>";

            response.forEach(function(schedl) {
                // if(schedl.available !== null)
                // {

                    availScheduleTable +="<tr>";

                    availScheduleTable += "<td><a href='javascript:void(0);' onClick='return createEventSchedule("+schedl.id+");'>Select</a></td>";

                    availScheduleTable += "<td>"+schedl.atMeridiem+"</td>";

                    availScheduleTable += "<td>"+schedl.medmaster.name+"</td>";
                    var s = $('input[name="Tb_sch_date"]').val().split('-');
                    availScheduleTable += "<td>"+`${s[1]}-${s[2]}-${s[0]}`+"</td>";
                    availScheduleTable +="</tr>";
                // }

            });

            $(".searchEventResults").append(availScheduleTable);
        },
        error: function(error) {

        }
    });
}

function createEventSchedule(schedulelId)
{
    let url = server_url+'/mediationfamily/event/store';
    $.ajax({
        url: url,
        method: 'POST',
        data: {schedId: schedulelId, caseId: $('input[name="id"]').val(), Tb_sch_date: $('input[name="Tb_sch_date"]').val()},
        dataType: 'JSON',
        success:function(response)
        {
            searchEvents();
        },
        error: function(error) {
            alert("Error occured during Scheduling Event!");
        }
    });
}

function editEventSchedule(schedId)
{

    $(".eventScheduleEdit").remove();
    let url = server_url+'/mediationfamily/event/'+schedId+"/edit";
    var outcomes = {};
    $.ajax({
        url: server_url+'/mediationfamily/outcome',
        method: 'GET',
        dataType: 'JSON',
        async:false,
        success:function(response)
        {
            outcomes = response;
        }
    });
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'JSON',
        success:function(response)
        {
            let eventModal = '<div class="modal eventScheduleEdit" tabindex="9999" role="dialog">'+
                '<div class="modal-dialog" role="document">'+
                    '<div class="modal-content">'+
                    '<div class="modal-header">'+
                        '<h5 class="modal-title">Edit Event Schedule</h5>'+
                        '<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                        '<span aria-hidden="true">&times;</span>'+
                        '</button>'+
                    '</div>'+
                    '<div class="modal-body">'+
                        '<form id = "updateEventSchedule">'+
                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Case No</label>'+
                        '<input type="text" class="form-control" disabled value ="'+response.case.c_caseno+'">'+
                        '<input type="hidden" name = "eventId" class="form-control" disabled value ="'+response.id+'">'+

                        '</div>'+
                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Date Time</label>'+
                        '<input type="text" class="form-control" disabled value ="'+response.e_sch_datetime+'">'+

                        '</div>'+
                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Mediator</label>'+
                        '<input type="text" class="form-control" disabled value ="'+response.medmaster.name+'">'+

                '</div>'+
                '<div class="form-group ">'+
                '<label for="exampleInputEmail1">Length</label>'+
                '<div class="form-row"><input type="hidden" class="form-control" name="e_sch_length" id="e_sch_length" value ="'+response.e_sch_length+'">'+
                '<div class="input-group col-sm-6"><div class="input-group-prepend"><span class="input-group-text">Hour(s) :</span></div><input type="number" min="0" max="23" maxlength="2" minlength="2" class="form-control col-md-5" name="e_sch_length_hours" id="e_sch_length_hours" value ="'+response.e_sch_length.substring(0, 2)+'" onchange="leadingZeros(this)" onkeyup="leadingZeros(this)" placeholder="Hours" onclick="leadingZeros(this)" ></div>'+
                '<div class="input-group col-sm-6"><div class="input-group-prepend"><span class="input-group-text">Minute(s) :</span></div><input type="number" min="0" max="59" maxlength="2" minlength="2" class="form-control col-md-5" name="e_sch_length_minutes" id="e_sch_length_minutes" value ="'+response.e_sch_length.substring(3, 5)+'"  onchange="leadingZeros(this)" onkeyup="leadingZeros(this)" placeholder="Minutes" onclick="leadingZeros(this)"></div>'+

                        '</div></div>'+
                        // '<div class="form-group">'+
                        // '<label for="exampleInputEmail1">Med Fee</label>'+
                        // '<input type="text" class="form-control" name="e_med_fee" value ="'+response.e_med_fee+'">'+

                        // '</div>'+
                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Petitioner Charge</label>'+

                        '<div class="input-group">'+
                        '<div class="input-group-prepend"><span class="input-group-text">$</span></div>'+
                        '<input type="text" class="form-control" name="e_pltf_chg" value ="'+response.e_pltf_chg+'">'+
                        '</div>'+
                        '</div>'+
                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Respondent Charge</label>'+
                        '<div class="input-group">'+
                        '<div class="input-group-prepend"><span class="input-group-text">$</span></div>'+
                        '<input type="text" class="form-control" name="e_def_chg" value ="'+response.e_def_chg+'">'+
                        '</div>'+
                        '</div>'+


                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Outcome</label>'+
                        '<select class="form-control" name="e_outcome_id">';
                        $.each(outcomes, function (key,outcome){
                            let selected = (outcome.id === response.e_outcome_id) ? "selected" : "";
                            eventModal += '<option value="'+outcome.id+'" '+selected+'>'+outcome.o_outcome+'</option>';
                        });

                    eventModal += '</select>'+

                        '</div>'+
                        '<div class="form-group">'+
                        '<label for="e_def_failedtoap" class="radio-inline col-md-6">Respondent FTA '+
                        '<input type="checkbox" name="e_def_failedtoap" id="e_def_failedtoap" '+((response.e_def_failedtoap == 1) ? "checked":"")+'>'+

                        '</label>'+
                        // '<div class="form-group">'+
                        '<label for="e_pltf_failedtoap" class="radio-inline  col-md-6">Petitioner FTA '+
                        '<input type="checkbox" name="e_pltf_failedtoap" id="e_pltf_failedtoap" '+((response.e_pltf_failedtoap == 1) ? "checked": "")+'>'+

                        '</label></div>'+
                        // '<div class="form-group">'+
                        // '<label for="exampleInputEmail1">Subject</label>'+
                        // '<input type="text" class="form-control" name="e_subject" value ="'+response.e_subject+'">'+

                        // '</div>'+
                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Event Notes</label>'+
                        '<input type="text" class="form-control" name="e_notes" value ="'+((response.e_notes == null) ? "" : response.e_notes)+'">'+

                        '</div>'+
                        '<button type="button" class="btn btn-primary" onClick="return updateEventSchedule();">Submit</button>'
                    '</form>'+
                    '</div>'+
                    '</div>'+
                '</div>'+
            '</div>';
            // console.log(eventModal);
            $("body").append(eventModal);
            $('.eventScheduleEdit').modal('toggle');
        },
        error: function(error) {
            alert("Error occured during Scheduling Event!");
        }
    });

}

function leadingZeros(input) {
    if(input.value.length === 1) {
        input.value = '0' + input.value;
    }
    else if(input.value.length === 3) {
        input.value = input.value.replace(/^0+/, '');
    }

    if(parseInt(input.value) > parseInt(input.max))
    {
        input.value = parseInt(input.max);
    }

    $("#e_sch_length").val($("#e_sch_length_hours").val()+":"+$("#e_sch_length_minutes").val());
}

function updateEventSchedule()
{
    let url = server_url+'/mediationfamily/event/'+$('input[name="eventId"]').val()+'/update';
    $.ajax({
        url: url,
        method: 'POST',
        data: $('#updateEventSchedule').serialize(),
        dataType: 'JSON',
        success:function(response)
        {
            searchEvents();
            // alert("Event Schedule updated successfully!");
            $('.eventScheduleEdit').modal('toggle');

        },
        error: function(error) {
            alert("Error while updating event schedule!");
        }
    });
}

function deleteEvent(eventlId)
{
    let url = server_url+'/mediationfamily/event/delete';
    $.ajax({
        url: url,
        method: 'DELETE',
        data: {eventId: eventlId},
        dataType: 'JSON',
        success:function(response)
        {
            // alert(response);
            searchEvents();
        },
        error: function(error) {
            alert("Error while deleting Scheduled Event!");
        }
    });
}
