@extends(backpack_view('blank'))
@php
    $breadcrumbs = [
        'Admin' => backpack_url('dashboard'),
        'Court' => backpack_url('court'),
        $court->description => route('calendar.show', $court->id),
        'Upload' => false,
    ];
@endphp

@section('content')

    <h2>
        Upload for <a class="text-primary" href="{{ route('calendar.show', $court->id) }}"> {{ $court->description }}</a> Calendar
    </h2>

    <div class="row">
        <div class="col-md-8">
            <div class="{{ $widget['class'] ?? 'alert alert-info' }}" role="alert">
                <h4 class="alert-heading">Note</h4>
                <p>
                    This will upload events from an Excel document to the calendar.
                </p>
            </div>
        </div>


        <div class="col-md-8 bold-labels">

            <form id="upload" method="post" action="{{ route('upload_data', $court) }}" enctype="multipart/form-data">
                @csrf

                <div class="card">
                    <div class="card-body row">


                        <div class="form-group col-md-6 required">
                            <label>Timeslot Date</label>
                            <div class="input-group date">
                                <input name="start_date" type="text" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group col-md-6 required">
                            <label>File</label>
                            <div class="input-group date">
                                <input name="file" type="file" class="form-control"  accept=".xls, .xlsx" required>
                            </div>
                        </div>

                        <div class="hidden">
                            <input type="hidden" name="court" value="{{ $court->id }}" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- This makes sure that all field assets are loaded. -->
                <div id="saveActions" class="form-group">

                    <input type="hidden" name="_save_action" value="save_and_edit">
                    <div class="btn-group" role="group">
                        <button type="submit" class="btn btn-success">
                            <span id="save-button" class="la la-save" role="presentation" aria-hidden="true"></span> &nbsp;
                            <span data-value="save_and_edit">Upload</span>
                        </button>
                    </div>

                    <a href="{{ route('calendar.upload', $court) }}" class="btn btn-default"><span class="la la-ban"></span> &nbsp;Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

{{-- FIELD CSS - will be loaded in the after_styles section --}}
@section('after_styles')
    <link href="/packages/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet">
@endsection

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@section('after_scripts')
    <script src="/packages/moment/min/moment-with-locales.min.js"></script>
    <script src="/packages/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('input[name="start_date"]').datepicker({
                autoclose: true
            });

            $('input[name="start_date"]').datepicker('setDate', '{{ $last_template_timeslot->date ?? null }}');



            $('#upload').submit(function(){
                document.getElementById("save-button").classList.remove('la-save');
                document.getElementById("save-button").classList.add('la-spinner');
                document.getElementById("save-button").classList.add('la-spin');
                $(this).find(':input[type=submit]').prop('disabled', true);
            });
        });
    </script>
@endsection
{{-- End of Extra CSS and JS --}}
