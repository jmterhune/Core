@extends(backpack_view('blank'))


@section('before_scripts')
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css"
    />
    <script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
@endsection
@section('content')

    <main class="main pt-2">

        <nav aria-label="breadcrumb" class="d-none d-lg-block">
            <ol class="breadcrumb bg-transparent p-0 justify-content-end">
                <li class="breadcrumb-item text-capitalize"><a href="http://jacs-admin.flcourts.org/dashboard">Admin</a></li>
                <li class="breadcrumb-item text-capitalize active">Mediation Email Instructions</li>
            </ol>
        </nav>

        <section class="container-fluid">
            <h2>
                <span class="text-capitalize">Mediation Email Instructions</span>
            </h2>
        </section>

        <div class="container-fluid animated fadeIn">

{{--            <div class="col-md-8">--}}
{{--                <div class="{{ $widget['class'] ?? 'alert alert-info' }}" role="alert">--}}
{{--                    <h4 class="alert-heading">Note</h4>--}}
{{--                    <p> This allow's a user to print the docket report for a calendar they have access to.</p>--}}
{{--                </div>--}}
{{--            </div>--}}

            <div class="row">
                <div class="col-md-8 bold-labels">
                    <!-- Default box -->

                    <form method="post" action="{{ route('email_instruction.email') }}">
                        @csrf

                        <div class="card">
                            <div class="card-body row">

                                <div class="form-group col-md-12">
                                    <label>Instructions</label>
                                    <textarea name="instructions" class="form-control" id="my-text-area" data-initialized="true" style="">{{ $instructions->instruction ?? null }}</textarea>
                                </div>

                                <input type="hidden" name="case_id" value="{{ $case->id }}" />

                                @foreach($parties as $key => $party)
                                    <div class="col-md-6">
                                        <label for="{{ $key }}" class="form-label">{{ Str::title($key) }} Email address(es)</label>
                                        <ul>
                                            @foreach($party as $member)
                                                <li>{{ $member->email ?? 'N/A' }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach

                                <div class="form-group pt-3 col-md-12">
                                    <label>Additional Email Address(es)</label>
                                    <input type="text" name="emails" value="" class="form-control">
                                    <small>Use semi-colon to separate email address</small>
                                </div>

                            </div>
                        </div>

                        <!-- This makes sure that all field assets are loaded. -->

                        <div id="saveActions" class="form-group">

                            <div class="btn-group" role="group">

                                <button type="submit" class="btn btn-success">
                                    <span class="la la-print" role="presentation" aria-hidden="true"></span> &nbsp;
                                    <span data-value="save_and_back">Send Instructions</span>
                                </button>


                            </div>

                            <a href="{{ route('mediation.edit', $case->id) }}" class="btn btn-default"><span class="la la-ban"></span> &nbsp;Cancel</a>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

@endsection

@section('after_scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.categories_select').select2();

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            getCategory();

        });

        const easymde = new EasyMDE({
            element: document.getElementById('my-text-area'),
        });

        function getCategory() {
            var courtId = $('#court').val();
            let url = '{{ env('APP_URL') }}/court/' + courtId + '/category';
            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    //    let checkValue = (response ==1) ? true :false ;
                    //  $('#category_print').prop('checked', checkValue);
                    let checkValue = (response.category_print != '') ? true : false;
                    $('#category_print').prop('checked', checkValue);
                    $('#mainHeader').val(response.custom_header);
                },
                error: function (response) {
                    errorsHtml = '<div class="alert alert-danger">Internal server error</div>';
                    $('#form-errors').html(errorsHtml);
                }
            })
        }

    </script>
@endsection
