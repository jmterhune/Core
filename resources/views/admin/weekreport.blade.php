@extends(backpack_view('blank'))
@section('before_styles')
<style>
    @media print {
        body {
            visibility: hidden;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .printpdf {
            display: block!important;
            visibility: visible;
            left: 0;
            top: 0;
        }

        .pdfheading{
            font-size: 24px;
        }
        .table{
            font-size:16px;
            width: 1300px !important;
        }
    }
</style>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="col-md-12">
            <div class="row">
                <h2>Weekly Report</h2>
            </div>
            <div class="row">
                <form id="reportSearch">
                    <div class="form-row col-md-12">
                        <div class="col-md-2">
                            <input type="text" class="form-control date" placeholder="From Date" id="date_from" name="date_from" onchange="getReport()">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control date" placeholder="To Date" id="date_to" name="date_to" onchange="getReport()">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="mediator_id" name="mediator_id" onchange="getReport()">
                                <option value="">All Mediators</option>
                                @foreach($mediators as $mediator)
                                <option value="{{$mediator->id}}">{{$mediator->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="county" name="county" onchange="getReport()">
                                <option value="">All Counties</option>
                                <option value="59">Seminole</option>
                                <option value="05">Brevard</option>

                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="form_type" name="form_type" onchange="getReport()">
                                <option value="">All Types</option>
                                <option value="f-form">Family Mediation</option>
                                <option value="sc-form">Civil Mediation</option>
                            </select>
                        </div>
{{--                        <div class="col-md-1">--}}
{{--                            <button type="button" class="btn btn-outline-primary" onClick="return getReport();"><i class="la la-search"></i></button>--}}
{{--                        </div>--}}
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-primary" onClick="window.print()">Print</button>
                        </div>
                    </div>
                </form>
            </div>
            <br><br>

            <div class="printpdf">
                <div class="row pdfheading" style="display:none;">
                    <p class="text-center"><b>Weekly Schedule</b></p>
                </div>
                <br>
                <div class="row reportTable" style = "font-weight: bold;">

                </div>
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
    var current = new Date();
    var weekstart = current.getDate() - current.getDay() + 1;
    var weekend = weekstart + 4;
    $('#date_from').datepicker({
        format: 'mm/dd/yy',
        autoclose: true
    }).datepicker('setDate', new Date(current.setDate(weekstart)));

    $('#date_to').datepicker({
        format: 'mm/dd/yy',
        autoclose: true
    }).datepicker('setDate', new Date(current.setDate(weekend)));
    getReport();



    function getReport() {
        let url = '{{ env('
        APP_URL ')  }}/mediation/report/week/search';
        $.ajax({
            url: url,
            method: 'POST',
            data: $("#reportSearch").serialize(),
            dataType: 'html',
            success: function(data) {
                $(".reportTable").empty();
                $(".pdfheading").show();
                $(".reportTable").append(data);
            },
            error: function(response) {
                console.log('Error');
            }
        });
    }
</script>
@endsection
