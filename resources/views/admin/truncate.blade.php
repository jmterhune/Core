@extends(backpack_view('blank'))
@php
    $breadcrumbs = [
        'Admin' => backpack_url('dashboard'),
        'Court' => backpack_url('court'),
        $court->description => route('calendar.show', $court->id),
        'Truncate' => false,
    ];


@endphp

@section('content')

    <h2>
        Truncate <a class="text-primary" href="{{ route('calendar.show', $court->id) }}"> {{ $court->description }}</a> Calendar
    </h2>


        <div class="row">

            <div class="col-md-8 bold-labels">
                <div class="{{ $widget['class'] ?? 'alert alert-danger' }}" role="alert">
                    <h4 class="alert-heading">Note</h4>
                    <p>
                        Truncating the calendar is an irreversible action. All scheduled hearings will be canceled and
                        corresponding notifications will be sent to attorneys and the JA (if option is enabled).
                    </p>
                </div>

                <form id="truncate" method="post" action="{{ route('truncate_timeslots', $court->id) }}">
                    @csrf

                    <input type="hidden" name="_http_referrer" value="https://jacs-admin.flcourts18.org/judge">

                    <div class="card">
                        <div class="card-body row">

                            <div class="form-group col-md-12">
                                @if($last_timeslot != null)
                                    <p> The last timeslot date in the calendar is <span class="text-primary"> {{ $last_timeslot->date }}</span></p>
                                @endif
                                @if($last_hearing != null)
                                    <p> The last scheduled hearing in the calendar is on <span class="text-primary"> {{ $last_hearing->date }}</span></p>
                                @endif

                            </div>

                            <div class="form-group col-md-3">
                                <label>Date</label>
                                <div class="input-group date">
                                    <input name="date" type="date" class="form-control" required>

                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter" id="exampleRadios1" value="all" checked>
                                    <label class="form-check-label" for="exampleRadios1">
                                        Truncate all timeslots from date forward
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter" id="exampleRadios2" value="hearings">
                                    <label class="form-check-label" for="exampleRadios2">
                                        Preserve scheduled hearings only
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter" id="exampleRadios3" value="templates" >
                                    <label class="form-check-label" for="exampleRadios3">
                                        Preserve blocked non-template timeslots only
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter" id="exampleRadios4" value="both" >
                                    <label class="form-check-label" for="exampleRadios4">
                                        Preserve both hearings and blocked non-template timeslots
                                    </label>
                                </div>
                            </div>

                            <div class="hidden">
                                <input type="hidden" name="court" value="{{ $court->id }}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- This makes sure that all field assets are loaded. -->
                    <div id="saveActions" class="form-group">

                        <button type="submit" class="btn btn-success">
                            <span id="save-button" class="la la-save" role="presentation" aria-hidden="true"></span> &nbsp;
                            <span data-value="save_and_edit">  Truncate</span>
                        </button>


                        <a href="{{ route('calendar.show', $court) }}" class="btn btn-default"><span class="la la-ban"></span> &nbsp;Cancel</a>
                    </div>
                </form>
            </div>
        </div>
@endsection

{{-- FIELD CSS - will be loaded in the after_styles section --}}
@section('after_styles')
    <link href="/packages/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
@endsection

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@section('after_scripts')
    <script src="/packages/moment/min/moment-with-locales.min.js"></script>
    <script src="/packages/bootstrap-daterangepicker/daterangepicker.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {


            $('#truncate').submit(function(){
                document.getElementById("save-button").classList.remove('la-save');
                document.getElementById("save-button").classList.add('la-spinner');
                document.getElementById("save-button").classList.add('la-spin');
                $(this).find(':input[type=submit]').prop('disabled', true);
            });

        });


    </script>
@endsection
{{-- End of Extra CSS and JS --}}
