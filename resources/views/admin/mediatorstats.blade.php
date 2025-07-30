@extends(backpack_view('blank'))
@section('before_styles')
<style>
    @page {     margin: 0 !important; }
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
        <div class="ml-3">
            <div class="row">
                <h2>Mediator Services</h2>
            </div>
            <div class="row">
                <form id="reportSearch">
                    <div class="form-row col-md-12">
                        <div class="col-3">
                            <select class="form-control" id ="mediator_id" name="mediator_id">
                                <option value="all">All</option>
                                <option value="brevard">Brevard</option>
                                <option value="seminole">Seminole</option>
                                @foreach($mediators->sortby('name') as $mediator)
                                    <option value="{{$mediator->id}}">{{$mediator->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            <input type="text" class="form-control date" placeholder="From Date" id="date_from" name="date_from">
                        </div>
                        <div class="col-2">
                            <input type="text" class="form-control date" placeholder="To Date" id="date_to" name="date_to">
                        </div>

                        <div class="col-2">
                            <button type="button" class="btn btn-outline-primary" onClick="return getReport();">Build Report</button>
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn btn-outline-primary" onClick="window.print()">Print</button>
                        </div>
                    </div>
                </form>
            </div>
            <br>
        </div>
        <div class="printpdf">
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
        let url = '{{ env('APP_URL ')  }}/mediation/report/getmediatorstats';
        $.ajax({
            url: url,
            method: 'POST',
            data: $("#reportSearch").serialize(),
            dataType: 'html',
            success: function(data) {
                $(".printpdf").empty();
                $(".printpdf").append(data);
            },
            error: function(response) {
                console.log('Error');
            }
        });
    }
</script>
@endsection
