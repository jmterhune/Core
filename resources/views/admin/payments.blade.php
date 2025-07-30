@extends(backpack_view('blank'))
@section('before_styles')

@endsection
@section('content')
<div class="card">
<div class="card-body">
    <div class="ml-3">
    @if(Session::has('message'))
    <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
    @endif
<div class="row">
    <h2>Payment Form</h2>
</div>
<div class="row">
    <div class="form-row col-md-6">
        <div class="col-5">
            <input type="search" name="case_number_search" id = "case_number_search" class="form-control rounded" placeholder="Search" aria-label="Search" aria-describedby="search-addon" />
        </div>
        <div class="col">
            <button type="button" class="btn btn-outline-primary" onclick="return searchCaseNumber();">search</button>
        </div>
    </div>
</div>
<br>
<div class="row">
    <h3>Case # {{$case->c_caseno}} Payment form</h3>
</div>
<div class="row">
    <label><strong>Plaintiff : &nbsp;&nbsp;</strong></label>
    <label> {{@$case->PltfAttroney->name}}</label>
</div>
<div class="row">
    <label><strong>Defendant :&nbsp;&nbsp;</strong></label>
    <label> {{@$case->DefAttroney->name}}</label>
</div>
<div class="row">
    <table cellspacing="0" cellpadding="4" border="0" id="GridView1" style="color:#333333;border-collapse:collapse;">
        <tbody>
            <tr style="color:White;background-color:#5D7B9D;font-weight:bold;">
                <th scope="col">&nbsp;</th>
                <th scope="col">Paid</th>
                <th scope="col">Who paid</th>
                <th scope="col">Date paid</th>
                <th scope="col">ScheduleDate</th>
                <th scope="col">Delete</th>
                <th scope="col">Applied</th>
            </tr>
            @foreach($case->payments as $payment)
            <tr style="color:#333333;background-color:#F7F6F3;">
                <td><a href="javascript:void(0);" onclick="return showPaymentUpdate({{$payment->id}});" style="color:#333333;">Edit</a></td>
                <td>{{$payment->amount_paid}}</td>
                <td>
                    <span id="GridView1_ctl02_Label1">{{$payment->paid_by}}</span>
                </td>
                <td align="right">
                    <span id="GridView1_ctl02_Label2" itemstyle-horizontalalign="Right">{{date("m-d-Y", strtotime($payment->paid_on))}}</span>
                </td>
                <td>
                    <span id="GridView1_ctl02_Label3">{{date("m-d-Y g:i A", strtotime($payment->event->e_sch_datetime))}}</span>
                </td>
                <td align="right" valign="top" style="background-color:#FFFF99;font-weight:bold;">
                    <a onclick="return deletePayment({{$payment->id}});" id="GridView1_ctl02_LinkButton99" href="javascript:void(0);" style="color:Red;">Delete</a>

                </td>
                <td style="color:#CC0000;font-weight:bold;">
                    <span id="GridView1_ctl02_Label4">OK</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<br><br>
<div class="row">
    <span class="auto-style2"><strong>Add Pmt to Case#</strong>: {{$case->c_caseno}}</span><br>
</div>
<div class="row">
    <form id="newPayment">
        <input type="hidden" name="p_c_id" value="{{$case->id}}">
        <div class="form-row">
            <div class="col-3">
                <input type="text" class="form-control date" placeholder="Date Paid" id="paid_on" name="paid_on">
            </div>
            <div class="col-2">
                <input type="text" class="form-control" placeholder="Paid Amt" name="amount_paid" id="amount_paid">
            </div>
            <div class="col-2">
                <select name="paid_by" id="paid_by" class="form-control">
                    <option selected="selected" value="Defendant">Defendant</option>
                    <option value="Plaintiff">Plaintiff</option>
                </select>
            </div>
            <div class="col-3">
                <select name="p_e_id" id="p_e_id" class="form-control">
                    @foreach($case->events as $event)
                    <option selected="selected" value="{{$event->id}}">{{date("m-d-Y g:i A", strtotime($event->e_sch_datetime))}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-2">
                <input type="button" name="Bt_add" value="Add Pmt" onclick="return addPayment();" id="Bt_add" class="btn btn-success">
            </div>
        </div>
    </form>
</div>
</div>
</div>
</div>

<div class="modal" id="updatePaymentModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Payment Update</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <form id="updatePaymentForm" class="updatePaymentForm">
                    <input type="hidden" name="payment_id" id="payment_id" value="">
                    <div class="form-group">
                        <label for="amount_paid">Amount Paid</label>
                        <input type="text" class="form-control" id="amount_paid" name="amount_paid" value="">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Paid By</label>
                        <select name="paid_by" id="paid_by" class="form-control">
                            <option selected="selected" value="Defendant">Defendant</option>
                            <option value="Plaintiff">Plaintiff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Date Paid</label>
                        <input type="text" class="form-control date" id="paid_on" name="paid_on" value="">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Apply To</label>
                        <select name="p_e_id" id="p_e_id" class="form-control">
                            @foreach($case->events as $event)
                            <option selected="selected" value="{{$event->id}}">{{date("m-d-Y g:i A", strtotime($event->e_sch_datetime))}}</option>
                            @endforeach

                        </select>
                    </div>
                </form>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="return submitPaymentUpdate();">Update</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
@endsection
@section('after_styles')
    <link href="/packages/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet">
@endsection
@section('after_scripts')
<script src="/packages/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
<script type="text/javascript">
    $('.date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    });

    function searchCaseNumber(){
    let url = "{{ env('APP_URL ')  }}/mediation/case/search";

    $.ajax({
        url: url,
        method: 'POST',
        data: {case_number: $("#case_number_search").val()},
        dataType: 'JSON',
        success:function(response)
        {
            //console.log(response)
            if(response !== null)
            {

                window.location.replace("{{ env('APP_URL ')  }}/mediation/payments/"+response.id);
            }
        },
        error: function(error) {

        }
    });
}

    function addPayment() {
        let url = '{{ env('APP_URL ')  }}/mediation/payments/add';
        $.ajax({
            url: url,
            method: 'POST',
            data: $("#newPayment").serialize(),
            dataType: 'JSON',
            success: function(data) {
                location.reload();
            },
            error: function(response) {
                console.log('Error');
            }
        });
    }

    function showPaymentUpdate(paymentId) {
        let url = '{{ env('
        APP_URL ')  }}/mediation/payments/edit/' + paymentId;
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'JSON',
            success: function(data) {
                $(".updatePaymentForm #payment_id").val(data.id);
                $(".updatePaymentForm #amount_paid").val(data.amount_paid);
                $(".updatePaymentForm #paid_by").val(data.paid_by);
                $(".updatePaymentForm #paid_on").datepicker("setDate", new Date(data.paid_on));
                $(".updatePaymentForm #p_e_id").val(data.p_e_id);
                $("#updatePaymentModal").modal('show');
                $("#updatePaymentModal").appendTo('body');
            },
            error: function(response) {
                console.log('Error');
            }
        });
    }

    function submitPaymentUpdate() {
        let url = '{{ env('
        APP_URL ')  }}/mediation/payments/update';
        $.ajax({
            url: url,
            method: 'POST',
            data: $("#updatePaymentForm").serialize(),
            dataType: 'JSON',
            success: function(data) {
                location.reload();
            },
            error: function(response) {
                console.log('Error');
            }
        });
    }

    function deletePayment(paymentId) {
        if (confirm("Are you sure you want to delete this?")) {
            let url = '{{ env('
            APP_URL ')  }}/mediation/payments/delete';
            $.ajax({
                url: url,
                method: 'DELETE',
                data: {
                    'payment_id': paymentId
                },
                dataType: 'JSON',
                success: function(data) {
                    location.reload();
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        } else {
            return false;
        }

    }

    function allowOnlyNumbers(inputElement) {
        inputElement.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9]/g, "");
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        const inputField = document.getElementById("amount_paid");
        if (inputField) {
            allowOnlyNumbers(inputField);
        }
    });
</script>
@endsection
