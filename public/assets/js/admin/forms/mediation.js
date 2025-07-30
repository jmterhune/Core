var server_url= "https://jacs-admin.flcourts18.org";


$(function () {

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
})

function searchCaseNumber(){
    let url = server_url+'/mediation/case/search';
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
                    window.location.replace(server_url+"/mediation/create?case_number="+$("#case_number_search").val());
                }
                else{
                    window.location.replace(server_url+"/mediation/"+response.id+"/edit");
                }
            },
            error: function(error) {
                window.location.replace(server_url+"/mediation/create?case_number="+$("#case_number_search").val());
            }
        });
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
    let url = server_url+'/mediation/event/search';
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

                eventTable += "<tr><td></td><td>Mediator</td><td>Date/Time</td><td>Length</td><td>Med Fee</td><td>Plaintiff Chg</td><td>Defendent Chg</td><td>Outcome</td><td>Defendent FTA</td><td>Plaintiff FTA</td><td>Notes</td></tr>";

                response.forEach(function(event) {

                    eventTable +="<tr>";

                    eventTable += "<td><a href='javascript:void(0);' onClick='return editEventSchedule("+event.id+");'>Edit</a> / <a href='javascript:void(0);' onClick='return deleteEvent("+event.id+");'>Delete</a> </td>";


                    eventTable += "<td>"+event.medmaster.name+"</td>";

                    //eventTable += "<td style='width:15%'>"+event.ESchDatetimeAmpm+"</td>";
                    eventTable += "<td style='width:12%'>";
                    eventTable += "<div>" + event.ESchDatetimeAmpm.split(" ")[0] + "</div>"; // Date in first line
                    eventTable += "<div>" + event.ESchDatetimeAmpm.split(" ")[1] +" "+ event.ESchDatetimeAmpm.split(" ")[2] + "</div>"; // Time in second line
                    eventTable += "</td>";
                    eventTable += "<td>"+event.e_sch_length.slice(0, -3)+"</td>";

                    eventTable += "<td>$ "+event.e_med_fee+"</td>";

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
                    $("#email_instructions").show();
                });



                eventTable += "</table>";
                if(response.length == 0){
                    $("#case_mediation_payment").hide();
                    $("#email_instructions").hide();
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
    let url = server_url+'/mediation/availabeTimings';
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
    let url = server_url+'/mediation/event/store';
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
    let url = server_url+'/mediation/event/'+schedId+"/edit";
    var outcomes = {};
    $.ajax({
        url: server_url+'/mediation/outcome',
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
                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Med Fee</label>'+
                        '<div class="input-group">'+
                        '<div class="input-group-prepend"><span class="input-group-text">$</span></div>'+
                        '<input type="text" class="form-control" name="e_med_fee" value ="'+response.e_med_fee+'">'+
                        '</div>'+
                        '</div>'+
                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Plantiff Chg</label>'+
                        '<div class="input-group">'+
                        '<div class="input-group-prepend"><span class="input-group-text">$</span></div>'+
                        '<input type="text" class="form-control" name="e_pltf_chg" value ="'+response.e_pltf_chg+'">'+
                        '</div>'+
                        '</div>'+
                        '<div class="form-group">'+
                        '<label for="exampleInputEmail1">Defendent Chg</label>'+
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
                        '<label for="e_def_failedtoap" class="radio-inline col-md-6">Defendent FTA '+
                        '<input type="checkbox"" name="e_def_failedtoap" id="e_def_failedtoap" '+((response.e_def_failedtoap == 1) ? "checked":"")+'>'+

                        '</label>'+
                        // '<div class="form-group">'+
                        '<label for="e_pltf_failedtoap" class="radio-inline col-md-6">Plaintiff FTA '+
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
                        '<button type="button" class="btn btn-primary" onClick="return updateEventSchedule();">Submit</button>' +
                        '<button type="button" class=" ml-3 btn btn-primary" onClick="return generateInvoice();">Generate Invoice</button>'
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
            //alert(error);
            console.log(error);
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
    let url = server_url+'/mediation/event/'+$('input[name="eventId"]').val()+'/update';
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

function generateInvoice()
{
    let downloadUrl = server_url+'/mediation_invoice/'+$('input[name="eventId"]').val();

    window.open(downloadUrl, '_blank');
    // $.ajax({
    //     url: url,
    //     method: 'POST',
    //     data: $('#updateEventSchedule').serialize(),
    //     dataType: 'JSON',
    //     success:function(response)
    //     {
    //
    //     },
    //     error: function(error) {
    //         alert("Error while updating event schedule!");
    //     }
    // });
}

function deleteEvent(eventlId)
{
    let url = server_url+'/mediation/event/delete';
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

////////////////////////////////////
var previous = $('input[name="previous"]');
let Plaintiff_count = 1;
let Defendant_count =1;$("form").show();
let tom_settings = {
    valueField: 'id',
    labelField: 'name',
    plugins: ['clear_button'],
    placeholder: 'Enter Attorney\'s Bar Number or Name',
    searchField: ['name','bar_num'],
    load: function(query, callback) {
        var url = server_url+'/attorney?q=' + encodeURIComponent(query);
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

}

// let plaintiff_select = new TomSelect('#Plaintiffs-0-attorney', tom_settings);
// let defendant_select = new TomSelect('#Defendants-0-attorney', tom_settings);

previous.change(function(){
    selected_value = $("input[name='previous']:checked").val();
    if(selected_value == 1){
        $('#previous').removeClass('d-none');
        $('#previous_case_num').prop('required', true);
        $('#origin').prop('required', true);
    } else{
        $('#previous').addClass('d-none');
        $('#previous_case_num').val('');
        $('#origin').val('');
        $('#previous_case_num').prop('required', false);
        $('#origin').prop('required', false);
    }
})

function addParty(name){

    let count;

    if(name === 'Plaintiffs'){
        count = Plaintiff_count;
    } else{
        count = Defendant_count;
    }

    let html =
        "<fieldset id=\"party_" + name +"_"+  count + "\">  " +
        "<legend class=\"border-bottom\"> Additional " + name + " #" + count + "</legend><button class=\"btn btn-danger w-100\" onclick=\"removeParty('party_"+ name + "_" + count + "')\" type=\"button\">Remove</button> " +
        "<div class=\"form-group required pt-3\"> <label class=\"font-weight-bold\" for=\"" + name + "[" + count +"][name]\">" + name + " Name</label> " +
        "<input type=\"text\" class=\"form-control form-control-user \" id=\"" + name + "[" + count +"][name]\" name=\"" + name + "[" + count +"][name]\" required>" +

        "</div>" +

        "<div class=\"form-group\">" +
        "<label class=\"font-weight-bold \" for=\"" + name + "[" + count +"][attorney]\">" + name + " Attorney </label>" +
        "<select name=\"" + name + "[" + count +"][attorney]\" id=\"" + name + "-" + count +"-attorney\" autocomplete=\"off\"></select>" +
        "<small class=\"form-text text-muted\"><a href=\"https://jacs-dev.flcourts18.org/attorney-add\">Can't find Attorney?</a></small>" +
        "</div>" +

        "<div class=\"form-group\">" +
        "<label class=\"font-weight-bold\" for=\"" + name + "[" + count +"][address]\">Address<small> for attorney, or <span class=\"font-weight-bold\">if not attorney, for the party.</span></small></label>" +
        "<textarea id=\"" + name + "[" + count +"][address]\" name=\"" + name + "[" + count +"][address]\" class=\"form-control form-control-user\" cols=\"40\" rows=\"2\" ></textarea>" +
        "</div>" +
        "<div class=\"form-group\">" +
        "<label class=\"font-weight-bold \" for=\"" + name + "[" + count +"][tele]\">Daytime Telephone #</label>"+
        "<input maxlength=\"16\" class=\"form-control form-control-user\" id=\"" + name + "-" + count +"-tele\" name=\"" + name + "[" + count +"][tele]\">"+
        "</div>" +


        "<div class=\"form-group\">" +
        "<label class=\"font-weight-bold \" for=\"" + name + "[" + count +"][email]\">" + name + " Email (Separate multiple emails with ';')</label>" +
        "<input type=\"text\" class=\"form-control form-control-user \" id=\"" + name + "[" + count +"][email]\" name=\"" + name + "[" + count +"][email]\" >" +
        "<small>Separate emails with multiple emails with a semicolon.</small>" +

        "</div>" +

        "</fieldset>";


    document.getElementById(name + "_form").insertAdjacentHTML('afterend', html);
    new TomSelect('#' + name + '-' + count + '-attorney', tom_settings);

    document.getElementById( name + '-' + count + '-tele');
    document.getElementById( name +'-' + count + '-tele').addEventListener('keydown',enforceFormat);
    document.getElementById( name +'-' + count + '-tele').addEventListener('keyup',formatToPhone);

    count++;

    if(name === 'Plaintiffs'){
        Plaintiff_count = count;
    } else{
        Defendant_count = count;
    }
}
