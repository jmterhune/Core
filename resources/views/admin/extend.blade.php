@extends(backpack_view('blank'))
@php
    $breadcrumbs = [
        'Admin' => backpack_url('dashboard'),
        'Court' => backpack_url('court'),
        $court->description => route('calendar.show', $court->id),
        'Extend' => false,
    ];
@endphp

@section('content')

    <h2>
        Extend <a class="text-primary" href="{{ route('calendar.show', $court->id) }}"> {{ $court->description }}</a> Calendar
    </h2>

    <div class="row">
        <div class="col-md-8">
            <div class="{{ $widget['class'] ?? 'alert alert-info' }}" role="alert">
                <h4 class="alert-heading">Note</h4>
                <p>
                    This will extend the calendar based on the order of the automated templates.
                </p>
            </div>
        </div>


        <div class="col-md-8 bold-labels">

            <form id="extend" method="post" action="{{ route('extend_calendar', $court) }}">
                @csrf

                <div class="card">
                    <div class="card-body row">

                        <div class="form-group col-md-12">
                            @if($last_timeslot != null)
                                <p> The last timeslot date in the calendar is <span class="text-primary"> {{ $last_timeslot->date }}</span></p>
                            @endif
                                @if($last_template_timeslot != null)
                                    <p> The last template used:<span class="text-primary"> {{ $last_template_timeslot->template->name ?? null }}</span> on <span class="text-primary"> {{ $last_template_timeslot->date }}</span></p>
                                @endif
                            @if($last_hearing != null)
                                <p> The last scheduled hearing in the calendar is on <span class="text-primary"> {{ $last_hearing->date }}</span></p>
                            @endif

                        </div>

                        <div class="form-group col-md-6">
                            <label for="start_template">Starting template</label>

                            <select class="form-control" id="start_template" name="start_template" required>
                                @foreach($templates as $template)

                                    <option value="{{ $template->order }}">{{ \App\Models\Template::find($template->template_id)->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6 required">
                            <label for="weeks">
                                Weeks to Extend
                            </label>
                            <input class="form-control" type="number" name="weeks" id="weeks" required>

                        </div>

                        <div class="form-group col-md-6 required">
                            <label>Start Date</label>
                            <div class="input-group date">
                                <input name="start_date" type="text" class="form-control" required>
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
                            <span data-value="save_and_edit">Extend</span>
                        </button>
                    </div>

                    <a href="{{ route('calendar.show', $court) }}" class="btn btn-default"><span class="la la-ban"></span> &nbsp;Cancel</a>
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



            $('#extend').submit(function(){
                document.getElementById("save-button").classList.remove('la-save');
                document.getElementById("save-button").classList.add('la-spinner');
                document.getElementById("save-button").classList.add('la-spin');
                $(this).find(':input[type=submit]').prop('disabled', true);
            });
        });
    </script>
@endsection
{{-- End of Extra CSS and JS --}}
