@extends(backpack_view('blank'))

@section('content')

    <main class="main pt-2">

        <nav aria-label="breadcrumb" class="d-none d-lg-block">
            <ol class="breadcrumb bg-transparent p-0 justify-content-end">
                <li class="breadcrumb-item text-capitalize"><a href="http://jacs-admin.flcourts.org/dashboard">Admin</a></li>
                <li class="breadcrumb-item text-capitalize active">Docket Report</li>
            </ol>
        </nav>

        <section class="container-fluid">
            <h2>
                <span class="text-capitalize">Docket Report</span>
            </h2>
        </section>

        <div class="container-fluid animated fadeIn">

            <div class="col-md-8">
                <div class="{{ $widget['class'] ?? 'alert alert-info' }}" role="alert">
                    <h4 class="alert-heading">Note</h4>
                    <p> This allow's a user to print the docket report for a calendar they have access to.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 bold-labels">
                    <!-- Default box -->

                    <form method="post" action="{{ route('docket.print') }}">
                        @csrf

                        <div class="card">
                            <div class="card-body row">

                                <div class="form-group col-md-12">
                                    <label>Judge</label>
                                    <select name="court" id="court" class="form-control" onchange="getCategory()">
                                        @foreach($courts->sortBy('judge.name') as $court)
                                            <option value="{{ $court->id }}"> {{ $court->judge->name }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Categories</label>
                                    <select name="category" id="court" class="form-control categories_select" style="width: 100%">
                                        <option value="0"> All </option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"> {{ $category->description }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="from">From Date</label>
                                    <input type="date" class="form-control" name="from" id="from" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label> To Date </label>
                                    <input type="date" class="form-control" name="to">
                                </div>
{{--                                <div class="form-group col-md-12">--}}
{{--                                    <label>Customize Docket Main Heading</label>--}}
{{--                                    <textarea name="mainHeader" id="mainHeader" rows="5" style="width: 100%;"></textarea>--}}
{{--                                </div>--}}

                                <div class="from-group col-md-12" >

                                    <label class="d-block">Hearings</label>

                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="hearing" value="all" id="all" checked>
                                        <label class="radio-inline form-check-label font-weight-normal" for="all">All</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="hearing" value="addon" id="addon">
                                        <label class="radio-inline form-check-label font-weight-normal" for="addon">Add On Only</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="hearing" value="noaddon" id="noaddon">
                                        <label class="radio-inline form-check-label font-weight-normal" for="noaddon">No Add On</label>
                                    </div>

                                </div>
                                <div class="form-group col-sm-12 mb-0 mt-2">
                                    <h6 class="text-primary mt-4">Optional Fields</h6><hr class="mb-0">
                                </div>
                                <div class="from-group col-md-12" >
                                    <label class="d-block"></label>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="category_print" name="category_print" value="1" checked >
                                        <label class="form-check-label" for="category_print">Print Category</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- This makes sure that all field assets are loaded. -->
                        <div class="d-none" id="parentLoadedAssets">["packages\/select2\/dist\/css\/select2.min.css","packages\/select2-bootstrap-theme\/dist\/select2-bootstrap.min.css","packages\/select2\/dist\/js\/select2.full.min.js","bpFieldInitRelationshipSelectElement"]</div>
                        <div id="saveActions" class="form-group">

                            <div class="btn-group" role="group">

                                <button type="submit" class="btn btn-success">
                                    <span class="la la-print" role="presentation" aria-hidden="true"></span> &nbsp;
                                    <span data-value="save_and_back">Print Docket</span>
                                </button>


                            </div>

                            <a href="{{ env('APP_URL') }}" class="btn btn-default"><span class="la la-ban"></span> &nbsp;Cancel</a>

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
                    //$('#mainHeader').val(response.custom_header);
                },
                error: function (response) {
                    errorsHtml = '<div class="alert alert-danger">Internal server error</div>';
                    $('#form-errors').html(errorsHtml);
                }
            })
        }

    </script>
@endsection
