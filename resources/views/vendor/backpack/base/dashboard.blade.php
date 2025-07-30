 @extends(backpack_view('blank'))

 @php
 $widgets['before_content'][] = [
 'type' => 'jumbotron',
 'heading' => trans('backpack::base.welcome'),
 'content' => trans('backpack::base.use_sidebar'),
 'button_link' => backpack_url('logout'),
 'button_text' => trans('backpack::base.logout'),
 ];
 @endphp


 @section('content')
 <div class="input-group">
  <label for="case_num" class="input-group-text">Case #:</label>
  <input type="search" id="case_num" class="form-control" placeholder="Search by Case Number..." style="max-width: 200px;" />
  <button type ="button" id="search-button" class="btn btn-primary" onclick="return searchCaseNumber();" >Find</button>
</div>




 <div class="animated">

     <div class="col-sm-12" style="float:left">
         <div class="card-header">
             <div style="float:left">
                 <h4>Time Slots</h4>
             </div>
             <div style=""><span style=""><a href="{{ url('timeslot-crud')}}" class="d-flex flex-row justify-content-end">view all</a></span></div>
         </div>
         <table id="crudTable" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2 dataTable dtr-inline" data-responsive-table="1" data-has-details-row="0" data-has-bulk-actions="0" cellspacing="0" aria-describedby="crudTable_info">
             <thead>
                 <tr>
                     <th>Court</th>
                     <th>Date / Time</th>
                     <th>Length</th>
                     <th>Available</th>
                     <th>Quantity</th>
                     <th>Actions</th>
                 </tr>
             </thead>

             <tbody>
                 @foreach($timeslots as $index => $timeslot)
                 <tr class="{{$index%2 !== 0 ? 'odd' : 'even'}}">
                     <td class="dtr-control"><span>{{$timeslot->court->description ?? null}}</span></td>
                     <td class="dtr-control"><span>{{date("m/d/Y @ h:i a",strtotime(@$timeslot->start))}}</span></td>
                     <td class="dtr-control"><span>{{@$timeslot->duration}} minutes</span></td>
                     <td class="dtr-control"><span>{{@($timeslot->Available) ? 'yes' : 'no'}}</span></td>
                     <td class="dtr-control"><span>{{@$timeslot->quantity}}</span></td>

                     <td>
                         <!-- Single edit button -->
                         <a href="{{ url('calendar/'.$timeslot->court->id.'?create_event='. $timeslot->id) }}" class="btn btn-sm btn-link"><i class="la la-edit"></i> Edit</a>

                 </tr>
                 @endforeach

             </tbody>
         </table>

     </div>
     <div class="col-sm-12" style="float:left">
         <div class="card-header">
             <div style="float:left">
                 <h4>Events</h4>
             </div>
             <div><span><a href="{{ url('event')}}" class="d-flex flex-row justify-content-end">view all</a></span></div>
         </div>
         <table id="crudTable" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2 dataTable dtr-inline" data-responsive-table="1" data-has-details-row="0" data-has-bulk-actions="0" cellspacing="0" aria-describedby="crudTable_info">
             <thead>
                 <tr>
                     <th>Case Number</th>
                     <th>Motion</th>
                     <th>Timeslot</th>
                     <th>Court</th>
                     <th>Status</th>
                     <th>Attorney</th>
                     <th>Opposing Attorney</th>
                     <th>Actions</th>
                 </tr>
             </thead>

             <tbody>
                 @foreach($events as $index => $event)
                 <tr class="{{$index%2 !== 0 ? 'odd' : 'even'}}">
                     <td class="dtr-control"><span>{{$event->case_num}}</span></td>
                     <td class="dtr-control"><span>{{@$event->motion->description}}</span></td>
                     <td class="dtr-control"><span>{{date("m/d/Y @ h:i a",strtotime(@$event->timeslot->start))}}</span></td>
                     <td class="dtr-control"><span>{{@$event->timeslot->court->description}}</span></td>
                     <td class="dtr-control"><span>{{@$event->status->name}}</span></td>
                     <td class="dtr-control"><span>{{@$event->attorney->name}}</span></td>
                     <td class="dtr-control"><span>{{@$event->opp_attorney->name}}</span></td>

                     <td>
                         <!-- Single edit button -->
                         <a href="{{ url('event/'.$event->id.'/edit')}}" class="btn btn-sm btn-link"><i class="la la-edit"></i> Edit</a>

                 </tr>
                 @endforeach

             </tbody>
         </table>
     </div>
 </div>

 @endsection

  <!--

Event Search model pop

-->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function searchCaseNumber(){
            let url = '{{ env('APP_URL') }}/event/casenum';

            $.ajax({
                url: url,
                method: 'POST',
                data: {case_number: $("#case_num").val()},
                dataType: 'JSON',
                success:function(response)
                {
                    if(response == null)
                    {
                        alert("No Event found with this case number!");
                    }
                    var case_details = '<tbody>'+
                         '<tr data-dt-row="0" data-dt-column="0">'+
                             '<td style="vertical-align:top; border:none;"><strong>Case Number:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;"><span>'+
                             response.case_num+
                                 '</span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="1">'+
                             '<td style="vertical-align:top; border:none;"><strong>Motion:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;"><span>'+
                                '<span class="d-inline-flex">'+(response.motion != null ? response.motion.description : "-") +'</span></span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="2">'+
                             '<td style="vertical-align:top; border:none;"><strong>Timeslot:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;"><span>'+
                                '<span class="d-inline-flex">'+ response.timeslot.date +'@ '+ response.timeslot.start_time +'</span></span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="3">'+
                             '<td style="vertical-align:top; border:none;"><strong>Court:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;"><span>'+
                                '<span class="d-inline-flex">'+response.timeslot.court.description+'</span></span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="4">'+
                             '<td style="vertical-align:top; border:none;"><strong>Status:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;"><span>'+
                                '<span class="d-inline-flex">'+(response.status != null ? response.status.name : "-")+'</span></span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="5">'+
                             '<td style="vertical-align:top; border:none;"><strong>Attorney:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;"><span>'+(response.attorney != null ? response.attorney.name : "-") +'</span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="6">'+
                             '<td style="vertical-align:top; border:none;"><strong>Opposing Attorney:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;"><span>'+(response.opp_attorney != null ? response.opp_attorney.name : "-") +'</span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="7">'+
                             '<td style="vertical-align:top; border:none;"><strong>Plaintiff:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;"><span>'+(response.plaintiff != null ? response.plaintiff : "-") +'</span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="8">'+
                             '<td style="vertical-align:top; border:none;"><strong>Defendant:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;"><span>'+(response.defendant != null ? response.defendant : "-") +'</span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="9">'+
                             '<td style="vertical-align:top; border:none;"><strong>Category:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;">'+
                                '<span><span class="d-inline-flex">'+(response.timeslot.category != null ? response.timeslot.category.description : "-") +'</span></span>'+
                             '</td>'+
                         '</tr>'+
                         '<tr data-dt-row="0" data-dt-column="10">'+
                             '<td style="vertical-align:top; border:none;"><strong>Actions:<strong></strong></strong></td>'+
                             '<td style="padding-left:10px;padding-bottom:10px; border:none;">'+
                                 '<a href="event/'+response.id+'/edit" class="btn btn-sm btn-link"><i class="la la-edit"></i> Edit</a>'+
                                '&lt;<a href="javascript:void(0)" onclick="cancelEntry('+response.id+')" class="btn btn-sm btn-link" data-button-type="delete"><i class="la la-trash"></i> Cancel</a>'+
                                '<a href="event/'+response.id+'/revise" class="btn btn-sm btn-link"><i class="la la-history"></i> Revisions</a>'+

                             '</td>'+
                         '</tr>'+
                     '</tbody>';
			$(".dtr-bs-modal table").empty();
                     $(".dtr-bs-modal table").append(case_details);
                    $(".dtr-bs-modal").modal('show');
                },
                error: function(error) {
                    alert("No Event found with this case number!");
                }
            });
    }

        function cancelEntry(e) {
            // ask for confirmation before deleting an item
            // e.preventDefault();
            var route = '{{ env('APP_URL') }}/event/' + e;
            console.log(e);
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
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: route,
                        method: 'DELETE',
                        dataType: 'JSON',
                        data: result,
                        success: function() {
                            Swal.fire(
                                'Cancelled!',
                                'Your hearing has now been cancelled.',
                                'success'
                            ).then(() => {
                                window.location.replace('/dashboard');
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
            function deleteEntry(button) {
                // ask for confirmation before deleting an item
                // e.preventDefault();
                var route = $(button).attr('data-route');

                Swal({
                    title: "Warning",
                    text: "Are you sure you want to delete this item?",
                    icon: "warning",
                    buttons: ["Cancel", "Delete"],
                    dangerMode: true,
                }).then((value) => {
                    if (value) {
                        $.ajax({
                            url: route,
                            type: 'DELETE',
                            success: function(result) {
                                if (result == 1) {
                                    // Redraw the table
                                    if (typeof crud != 'undefined' && typeof crud.table != 'undefined') {
                                        // Move to previous page in case of deleting the only item in table
                                        if (crud.table.rows().count() === 1) {
                                            crud.table.page("previous");
                                        }

                                        crud.table.draw(false);
                                    }

                                    // Show a success notification bubble
                                    new Noty({
                                        type: "success",
                                        text: "<strong>Item Deleted</strong><br>The item has been deleted successfully."
                                    }).show();

                                    // Hide the modal, if any
                                    $('.modal').modal('hide');
                                } else {
                                    // if the result is an array, it means
                                    // we have notification bubbles to show
                                    if (result instanceof Object) {
                                        // trigger one or more bubble notifications
                                        Object.entries(result).forEach(function(entry, index) {
                                            var type = entry[0];
                                            entry[1].forEach(function(message, i) {
                                                new Noty({
                                                    type: type,
                                                    text: message
                                                }).show();
                                            });
                                        });
                                    } else { // Show an error alert
                                        swal({
                                            title: "NOT deleted",
                                            text: "There's been an error. Your item might not have been deleted.",
                                            icon: "error",
                                            timer: 4000,
                                            buttons: false,
                                        });
                                    }
                                }
                            },
                            error: function(result) {
                                // Show an alert with the result
                                swal({
                                    title: "NOT deleted",
                                    text: "There's been an error. Your item might not have been deleted.",
                                    icon: "error",
                                    timer: 4000,
                                    buttons: false,
                                });
                            }
                        });
                    }
                });
            }
 </script>

<div class="modal fade dtr-bs-modal" style="" aria-modal="true" role="dialog">
     <div class="modal-dialog" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h4 class="modal-title"></h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
             </div>
             <div class="modal-body">
                 <table class="table table-striped mb-0">

                 </table>
             </div>
         </div>
     </div>
 </div>

