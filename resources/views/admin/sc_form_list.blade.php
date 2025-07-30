@php
    use Carbon\Carbon;
@endphp
@extends(backpack_view('blank'))
@section('before_styles')
<link href="/css/tom-select.css" rel="stylesheet">
@endsection
@section('content')
<div class="card">
<div class="card-body">
    <div class="ml-3">
    @if(Session::has('message'))
    <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
    @endif
    <div class="row">
        <h2 class="col-md-6">Pending Approvals</h2>
        <div class="col-md-6 control-group row">
            <div class="col-md-4">
                <select name="county" id="county" class="form-control" onChange="showCountyCase(this);">
                    <option value="">Select County</option>
                    @foreach($counties as $county)
                        <option value="{{$county->name}}">{{$county->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="casetype" id="casetype" class="form-control" onChange="showCaseType(this);">
                    <option value="">Select Division</option>
                    <option value="sc-form">County Civil</option>
                    <option value="f-form">Family Court</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" id="search" placeholder="Search..." class="form-control form-rounded" onKeyup="filterTable(this);">
            </div>
    </div>
    <div class="col-md-12 alert alert-info" role="alert">
        <h4 class="alert-heading">Note</h4>
        <p>

        </p>
    </div>
</div>
<div class="row">
    <table cellspacing="0" cellpadding="4" border="0" id="GridView1" style="color:#333333;border-collapse:collapse;" class="table scformbody">
        <thead>
            <tr style="color:White;background-color:#5D7B9D;font-weight:bold;">
                <th scope="col">Ref No.</th>
                <th scope="col">Case No.</th>
                <th scope="col">Case Type</th>
                <th scope="col">County</th>
                <th scope="col">Judge</th>
                <th scope="col">Type</th>
                <th scope="col">Plaintiff/Petitioner</th>
                <th scope="col">Defendant/Respondent</th>
                <th scope="col">Created On</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cases as $case)
            <tr style="color:#333333;background-color:#F7F6F3;" class="county county-{{@$case->judge->court->county->name}} casetype casetype-{{$case->form_type}} {{@$case->judge->court->county->name}}{{$case->form_type}}">
                <td>REF000{{$case->id}}</td>
                <td>{{$case->c_caseno}}</td>
                <td>@if($case->form_type == "sc-form") Civil @else Family @endif</td>
                <td>{{substr($case->c_caseno,0,2) === '59' ? 'Seminole' : 'Brevard'}}</td>
                <td>{{$case->judge->name}}</td>
                <td>{{$case->c_type}}</td>
                <td>
                    @foreach($case->parties as $party)
                        @if($party->type == "plaintiff" || $party->type == "petitioner")
                            {{ $party->name }}
                        @endif
                    @endforeach
                </td>
                <td>
                    @foreach($case->parties as $party)
                        @if($party->type == "defendant" || $party->type == "respondent")
                            {{ $party->name }}
                        @endif
                    @endforeach
                </td>
                <td>{{ Carbon::parse($case->created_at)->format('m/d/Y @ h:i a') }}</td>
                <td style="color: #CC0000; font-weight: bold;">
    <input type="hidden" id="sc-form-data-{{$case->id}}" value="{{json_encode($case)}}">
    <span id="GridView1_ctl02_Label4" class="btn btn-link preview-button" onClick="return showSCForm('{{$case->id}}');">Preview</span>
</td>

            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</div>
</div>
</div>
</div>
<div class="modal" id="scformdetails">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Details</h4>
                <h4 id="signed_by" class="modal-title pl-3">
                    (Signed by
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="container">
                    <form >
                        <div class="row">
                            <div class="form-group required col-md-6">
                                <label class="font-weight-bold " for="case_num">Case Number (For example: 59-2024-CC-123456)</label>
                                <input type="hidden" id="mediation_case_id" name="mediation_case_id">
                                <input type="text" class="form-control form-control-user @error('case_num') is-invalid @enderror" id="case_num" name="case_num" value="{{ old('case_num') }}" required>
                                @error('case_num')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="form-group required col-md-6" >
                                <label class="font-weight-bold" for="judge">Judge</label>
                                <select name="judge" id="judge" class="form-control form-control-user">
                                    <option value="">--select judge--</option>
                                    @foreach($judges as $judge)
                                        <option value="{{$judge->id}}" @if(old('judge') == $judge->id) selected @endif>{{$judge->name}}</option>
                                    @endforeach
                                </select>
                                <!-- <input type="text" class="form-control form-control-user @error('judge') is-invalid @enderror" id="judge" name="judge" value="{{ old('judge') }}" required> -->
                                @error('judge') <div class="invalid-feedback"> {{ $message }} </div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold" for="type">Type of Case</label>
                                <select class="form-control form-control-user" id="type" name="type">
                                    <option form-type="sc-form" value="Auto Repair" @if(old('type') == "Auto Repair") selected @endif>Auto Repair</option>
                                    <option form-type="sc-form" value="Breach of Contract" @if(old('type') == "Breach of Contract") selected @endif>Breach of Contract</option>
                                    <option form-type="sc-form" value="Consumer Goods" @if(old('type') == "Consumer Goods") selected @endif>Consumer Goods</option>
                                    <option form-type="sc-form" value="Landlord" @if(old('type') == "Landlord") selected @endif>Landlord</option>
                                    <option form-type="sc-form" value="Recovery of Money" @if(old('type') == "Recovery of Money") selected @endif>Recovery of Money</option>
                                    <option form-type="sc-form" value="Worthless Check" @if(old('type') == "Worthless Check") selected @endif>Worthless Check</option>
                                    <option form-type="sc-form" value="Other" @if(old('type') == "Other") selected @endif>Other</option>
                                    <option form-type="f-form" value="Divorce with Children" @if(old('type') == "Divorce with Children") selected @endif>Divorce with Children</option>
                                    <option form-type="f-form" value="Divorce without Children" @if(old('type') == "Divorce without Children") selected @endif>Divorce without Children</option>
                                    <option form-type="f-form" value="Paternity" @if(old('type') == "Paternity") selected @endif>Paternity</option>
                                    <option form-type="f-form" value="Modification" @if(old('type') == "Modification") selected @endif>Modification</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <div class="form-group">
                                    <label for="validationServer02"
                                           class="font-weight-bold text-gray-700">Type</label>
                                    <select class="form-control" id="location_type_id" name="location_type_id" required>
                                        <option value=""> -</option>
                                        @foreach($event_types as $type)
                                            <option value="{{ $type->id }}" @if(old('location_type_id') == $type->id) selected @endif> {{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="font-weight-bold" for="type">CERTIFIED BY THE CLERK AS INDIGENT/INSOLVENT</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="petitioner" name="petitioner" value="1" @if(old('petitioner') != null) checked @endif>
                                    <label class="form-check-label" for="petitioner">Petitioner</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="respondent" name="respondent" value="1" @if(old('respondent') != null) checked @endif>
                                    <label class="form-check-label" for="respondent">Respondent</label>
                                </div>
                            </div>



                            <div class="form-group col-md-6 pt-3 rounded bg-light">
                                <fieldset class="pp_party">
                                    <legend class="border-bottom plaintiff_information">Plaintiff Information</legend>

                                    <div class="form-group required pt-3">
                                        <label class="font-weight-bold " for="party[0][name]">Plaintiff Name</label>
                                        <input type="text" class="form-control form-control-user @error('party[0][name]') is-invalid @enderror" id="party[0][plaintiff]" name="party[0][plaintiff]" value="{{ old('party[0][name]') }}" >
                                        @error('party[0][plaintiff]') <div class="invalid-feedback"> {{ $message }} </div> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold " for="party[0][plaintiff]">Plaintiff Attorney </label>
                                        <select name="plaintiff_att" id="plaintiff_att" autocomplete="off"></select>
                                        <!-- <input type="text" class="form-control form-control-user" id="plaintiff_att" name="plaintiff_att" value="{{ old('plaintiff_att') }}"> -->
                                    </div>


                                    <div class="form-group">
                                        <label class="font-weight-bold" for="plaintiff_add">Address<small> for attorney, or <span class="font-weight-bold">if not attorney, for the party.</span></small></label>
                                        <textarea id="plaintiff_add" name="plaintiff_add" class="form-control form-control-user" cols="40" rows="2" > {{ old('plaintiff_add') }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold " for="plaintiff_tel">Daytime Telephone #</label>
                                        <input maxlength="16" class="form-control form-control-user" id="plaintiff_tel" name="plaintiff_tel" value="{{ old('plaintiff_tel') }}">
                                    </div>


                                    <div class="form-group">
                                        <label class="font-weight-bold" for="plaintiff_email">Emails (Separate multiple emails with ';')</label>
                                        <input type="text" class="form-control form-control-user @error('plaintiff_email') is-invalid @enderror" id="plaintiff_email" name="plaintiff_email" value="{{ old('plaintiff_email') }}">
                                        @error('plaintiff_email') <div class="invalid-feedback"> {{ $message }} </div> @enderror
                                    </div>


                                </fieldset>

                            </div>


                            <div class="form-group col-md-6 pt-3">
                                <fieldset class="dr_party">
                                    <legend class="border-bottom defendant_information">Defendant Information</legend>
                                    <div class="form-group required pt-3">
                                        <label class="font-weight-bold" for="defendant">Defendant Name</label>
                                        <input type="text" class="form-control form-control-user @error('defendant') is-invalid @enderror" id="defendant" name="defendant" value="{{ old('defendant') }}" >
                                        @error('defendant') <div class="invalid-feedback"> {{ $message }} </div> @enderror
                                    </div>


                                    <div class="form-group ">
                                        <label class="font-weight-bold" for="defendant_att">Defendant Attorney</label>
                                        <select name="defendant_att" id="defendant_att" autocomplete="off"></select>
                                        <!-- <input type="text" class="form-control form-control-user" id="defendant_att" name="defendant_att" value="{{ old('defendant_att') }}"> -->
                                    </div>

                                    <div class="form-group ">
                                        <label class="font-weight-bold" for="defendant_add">Address<small> for attorney, or <span class="font-weight-bold">if not attorney, for the party.</span></small></label>
                                        <textarea id="defendant_add" name="defendant_add" class="form-control form-control-user" cols="40" rows="2" >{{ old('defendant_att') }}</textarea>
                                    </div>


                                    <div class="form-group 6">
                                        <label class="font-weight-bold" for="defendant_tel">Daytime Telephone #</label>
                                        <input maxlength="16"  class="form-control form-control-user" id="defendant_tel" name="defendant_tel" value="{{ old('defendant_tel') }}">
                                    </div>


                                    <div class="form-group">
                                        <label class="font-weight-bold" for="plaintiff_email">Emails (Separate multiple emails with ';')</label>
                                        <input type="text" class="form-control form-control-user @error('defendant_email') is-invalid @enderror" id="defendant_email" name="defendant_email" value="{{ old('defendant_email') }}">
                                        @error('defendant_email') <div class="invalid-feedback"> {{ $message }} </div> @enderror
                                    </div>

                                </fieldset>
                            </div>
                            <div class="form-group col-md-12 pt-3 f-form" style="display:none;">
                                <fieldset>
                                    <legend class="border-bottom">G.A.L Information</legend>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group pt-3">
                                                <label class="font-weight-bold" for="gal">G.A.L Name</label>
                                                <input type="text" class="form-control form-control-user @error('gal') is-invalid @enderror" id="gal" name="gal" value="{{ old('gal') }}" >
                                            </div>

                                            <div class="form-group ">
                                                <label class="font-weight-bold" for="gal_add">G.A.L Address</label>
                                                <textarea id="gal_add" name="gal_add" class="form-control form-control-user" cols="40" rows="2" >{{ old('gal_att') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group pt-3">
                                                <label class="font-weight-bold" for="gal_tel">G.A.L Daytime Telephone #</label>
                                                <input maxlength="16"  class="form-control form-control-user" id="gal_tel" name="gal_tel" value="{{ old('gal_tel') }}">
                                            </div>

                                            <div class="form-group ">
                                                <label class="font-weight-bold" for="gal_email">G.A.L Email</label>
                                                <input type="email" class="form-control form-control-user @error('gal_email') is-invalid @enderror" id="gal_email" name="gal_email" value="{{ old('gal_email') }}">
                                            </div>
                                        </div>

                                    </div>




                                </fieldset>
                            </div>
                            <div class="form-group col-md-12 f-form" style="display:none;">
                                <label class="font-weight-bold" for="type">Check all contested issues included in the Petition which are appropriate for mediation:</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="parental_responsibility" name="f_issues['parental_responsibility']" value="parental_responsibility" @if(old("f_issues['parental_responsibility']") != null) checked @endif>
                                            <label class="form-check-label" for="parental_responsibility">Parental Responsibility</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="timesharing" name="f_issues['timesharing']" value="timesharing" @if(old("f_issues['timesharing']") != null) checked @endif>
                                            <label class="form-check-label" for="timesharing">Timesharing</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="child_support" name="f_issues['child_support']" value="child_support" @if(old("f_issues['child_support']") != null) checked @endif>
                                            <label class="form-check-label" for="child_support">Child Support</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="exclusive_possession" name="f_issues['exclusive_possession']" value="exclusive_possession" @if(old("f_issues['exclusive_possession']") != null) checked @endif>
                                            <label class="form-check-label" for="exclusive_possession">Exclusive Possession of Home</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="visitation" name="f_issues['visitation']" value="visitation" @if(old("f_issues['visitation']") != null) checked @endif>
                                            <label class="form-check-label" for="visitation">Parental Responsibility</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="alimony" name="f_issues['alimony']" value="alimony" @if(old("f_issues['alimony']") != null) checked @endif>
                                            <label class="form-check-label" for="alimony">Alimony</label>
                                        </div>

                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="children_school" name="f_issues['children_school']" value="children_school" @if(old("f_issues['children_school']") != null) checked @endif>
                                            <label class="form-check-label" for="children_school">Children School Issues</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="attorney_fees" name="f_issues['attorney_fees']" value="attorney_fees" @if(old("f_issues['attorney_fees']") != null) checked @endif>
                                            <label class="form-check-label" for="attorney_fees">Attorney Fees</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="display: none;">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="other_matters" name="f_issues['other_matters']" value="other_matters" onclick="showDescription()" @if(old("f_issues.other_matters") != null) checked @endif>
                                            <label class="form-check-label" for="other_matters">Other Matters</label>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-row other_matters_form" style="@if(old("f_issues.other_matters") == null) display: none @endif ">
                                    <div class="col-md-12 mt-3">
                                        <div class="form-group">
                                            <label for="other_matters_description" id="other_matters_description_label" class="font-weight-bold text-gray-700">Other Matters Description</label>
                                            <input class="form-control" name="f_issues_other_notes" id="other_matters_description" type="text" value="{{ old("f_issues_other_notes") }}">
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group col-md-12 pt-3">
                                <label class="font-weight-bold" for="defendant_email">Have the parties been involved in any current or previous litigation? </label>
                                <div class="form-check form-check-inline pl-3">
                                    <input class="form-check-input" type="radio" name="previous" id="previous_yes" value="1" @if(old('previous') == 1) checked @endif>
                                    <label class="form-check-label" for="previous_yes">
                                    Yes
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="previous" id="previous_no" value="0" @if(old('previous') == 0) checked @endif>
                                    <label class="form-check-label" for="previous_no">
                                        No
                                    </label>
                                </div>
                                <div class="row " id="previous">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold" for="previous_case_num">Previous Litigation Case Number</label>
                                        <input type="text" class="form-control form-control-user" id="previous_case_num" name="previous_case_num" value="{{ old('previous_case_num') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold" for="origin">Previous Litigation State/County or Origin</label>
                                        <input type="text" class="form-control form-control-user" id="origin" name="origin" value="{{ old('origin') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold" for="previous_case_tel">Telephone #</label>
                                        <input maxlength="16"  class="form-control form-control-user" id="previous_case_tel" name="previous_case_tel" value="{{ old('previous_case_tel') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold" for="previous_case_email">Respondent Email (Separate multiple emails with ';')</label>
                                        <input type="email" class="form-control form-control-user @error('previous_case_email') is-invalid @enderror" id="previous_case_email" name="previous_case_email" value="{{ old('previous_case_email') }}">
                                    </div>
                                </div>

                            </div>

                            <div class="form-group col-md-12 pt-3" id="injunction">
                                <label class="font-weight-bold" for="defendant_email">Is there an injunction in place? </label>
                                <div class="form-check form-check-inline pl-3">
                                    <input class="form-check-input" type="radio" name="injunction" id="injunction_yes" value="1" @if(old('injunction') == 1) checked @endif>
                                    <label class="form-check-label" for="injunction_yes">
                                        Yes
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="injunction" id="injunction_no" value="0" @if(old('injunction') == 0) checked @endif>
                                    <label class="form-check-label" for="injunction_no">
                                        No
                                    </label>
                                </div>
                            </div>

                            {{-- Availability --}}
                            <div class="form-group col-md-12 pt-3">
                                <fieldset>
                                    <legend class="border-bottom">Availability</legend>

                                    <div class="row">
                                        <div class="col-md-12">

                                            <div class="form-group required">
                                                <label class="font-weight-bold" for="availability">Please type below when you are available for mediation.</label>
                                                <textarea id="availability" name="availability" class="form-control form-control-user" cols="40" rows="2" required>{{ old('availability') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>


                        </div>
                    </form>
                </div>

            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="return approveSC();">Approve</button>
                <button type="button" class="btn btn-danger" onclick="return deleteSC();">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
@endsection
@section('after_scripts')
<script src="/js/tom-select.complete.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    jQuery('#scformdetails').on('shown.bs.modal', function() {
        jQuery(document).off('focusin.modal');
    });
</script>
<script type="text/javascript">

var previous = $('input[name="previous"]');

previous.change(function(){
    selected_value = $("input[name='previous']:checked").val();
    if(selected_value == 1){
        $('#previous').removeClass('d-none');
        $('#previous_case_num').prop('required', true);
        $('#origin').prop('required', true);
        $('#previous_case_tel').prop('required', true);
        $('#previous_case_email').prop('required', true);
    } else{
        $('#previous').addClass('d-none');
        $('#previous_case_num').val('');
        $('#origin').val('');
        $('#previous_case_tel').val('');
        $('#previous_case_email').val('');
        $('#previous_case_num').prop('required', false);
        $('#origin').prop('required', false);
        $('#previous_case_tel').prop('required', false);
        $('#previous_case_email').prop('required', false);
    }
})

function showDescription() {
    var checkBox = document.getElementById("other_matters");

    if (checkBox.checked == true){
        $('.other_matters_form').css('display', 'flex');

        $('#other_matters_description').prop('required', true);
    } else {
        $('.other_matters_form').css('display', 'none');

        $('#other_matters_description').prop('required', false);
    }

}
function setPPTomSelect(ppId){
    let attorney_select = new TomSelect("#"+ppId,{
        valueField: 'id',
        labelField: 'name',
        plugins: ['clear_button'],
        placeholder: 'Type Bar Number',
        searchField: ['name','bar_num'],
        load: function(query, callback) {
            var url = '{{ env('APP_URL')  }}/api/attorney?q=' + encodeURIComponent(query);
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

    });
    return attorney_select;
}

function setDRTomSelect(drId){
// Javascript Opposing Attorney Fetch
    let defendant_attorney_select = new TomSelect("#"+drId,{
        valueField: 'id',
        labelField: 'name',
        plugins: ['clear_button'],
        placeholder: 'Type Bar Number',
        searchField: ['name','bar_num'],
        load: function(query, callback) {
            var url = '{{ env('APP_URL')  }}/api/attorney?q=' + encodeURIComponent(query);
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
    });
    return defendant_attorney_select;
}
// $(document).ready(function() {

    function showCountyCase(county){
        let countyName = county.value;
        let formtype = ($("#casetype").val() != undefined) ? $("#casetype").val() :"";
        let combinedclass = countyName+formtype;
        console.log(countyName+$("#casetype").val());
        if(countyName != "" && formtype != ""){
            $(".casetype").hide();
            $("."+combinedclass).show();
        }
        else if(countyName != ""){
        $(".county").hide();
        $(".county-"+countyName).show();
        }
        else{
            $(".county").show();
        }
    }
    function showCaseType(casetype){
        let formtype = casetype.value;
        let countyName = ($("#county").val() != undefined) ? $("#county").val() : "";
        let combinedclass = countyName+formtype;

        console.log($("#county").val()+formtype);
        if(countyName != "" && formtype != ""){
            $(".casetype").hide();
            $("."+combinedclass).show();
        }
        else if(formtype != ""){
            $(".casetype").hide();
            $(".casetype-"+formtype).show();
        }
        else{
            $(".casetype").show();
        }
    }

    function filterTable(search){
        let searchKey = $.trim(search.value).replace(/ +/g, ' ').toLowerCase();
        $(".scformbody tbody tr").show().filter(function() {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(searchKey);
            }).hide();
    }
    function showSCForm(sc_id)
    {
        $("form")[0].reset();
        // attorney_select.clear();
        // defendant_attorney_select.clear();
        var formdata = JSON.parse($("#sc-form-data-"+sc_id).val(),true);
        console.log(formdata);
        $("#mediation_case_id").val(formdata.id);
        $("#case_num").val(formdata.c_caseno);
        $("#judge").val(formdata.judge.id);
        $("#type").val(formdata.c_type);
        $("#location_type_id").val(formdata.location_type_id);
        $("#type option").hide();
        $("#type option[form-type=" + formdata.form_type + "]").show();
        if(formdata.petitioner == 1)
        {
            $("#petitioner").prop( "checked", true );
        }
        if(formdata.respondent == 1)
        {
            $("#respondent").prop( "checked", true );
        }

        //////////
        var pp = "";
        var dr = "";
        var p = 1;
        var r = 1;
        $.map( formdata.parties, function( party, i ) {
            if(party.type == "plaintiff" || party.type == "petitioner"){
                // setPPTomSelect("party["+i+"][attorney_id]");
                pp += '<legend class="border-bottom plaintiff_information">'+party.type.substr(0,1).toUpperCase()+party.type.substr(1)+' Information #'+p+'</legend>'+
                        '<input type="hidden" name="party['+i+'][id]" value="'+party.id+'">'+
                        '<div class="form-group required pt-3">'+
                            '<label class="font-weight-bold " for="party['+i+'][name]">'+party.type.substr(0,1).toUpperCase()+party.type.substr(1)+' Name</label>'+
                            '<input type="text" class="form-control form-control-user" id="party['+i+'][name]" name="party['+i+'][name]" value="'+party.name+'" >'+
                        '</div>'+

                        '<div class="form-group">'+
                            '<label class="font-weight-bold " for="party['+i+'][attorney_id]">'+party.type.substr(0,1).toUpperCase()+party.type.substr(1)+' Attorney </label>'+
                            '<select name="party['+i+'][attorney_id]" id="party-'+i+'-attorney_id" autocomplete="off"></select>'+
                        '</div>'+

                        '<div class="form-group">'+
                            '<label class="font-weight-bold" for="party['+i+'][address]">Address<small> for '+party.type+', or <span class="font-weight-bold">if not '+party.type+', for the party.</span></small></label>'+
                            '<textarea id="party['+i+'][address]" name="party['+i+'][address]" class="form-control form-control-user" cols="40" rows="2" > '+((party.address != null) ? party.address : "")+'</textarea>'+
                        '</div>'+

                        '<div class="form-group">'+
                            '<label class="font-weight-bold " for="party['+i+'][telephone]">Daytime Telephone #</label>'+
                            '<input maxlength="16" class="form-control form-control-user" id="party['+i+'][telephone]" name="party['+i+'][telephone]" value="'+((party.telephone != null) ? party.telephone : "")+'">'+
                        '</div>'+

                        '<div class="form-group">'+
                            '<label class="font-weight-bold" for="party['+i+'][email]">Emails (Separate multiple emails with \';\')</label>'+
                            '<input type="text" class="form-control form-control-user" id="party['+i+'][email]" name="party['+i+'][email]" value="'+((party.email != null) ? party.email : "")+'">'+
                        '</div>';
                p++;
            }
            else if(party.type == "defendant" || party.type == "respondent"){

                // setDRTomSelect("party["+i+"][attorney_id]");
                dr += '<legend class="border-bottom defendant_information">'+party.type.substr(0,1).toUpperCase()+party.type.substr(1)+' Information #'+r+'</legend>'+
                        '<input type="hidden" name="party['+i+'][id]" value="'+party.id+'">'+
                        '<div class="form-group required pt-3">'+
                            '<label class="font-weight-bold" for="party['+i+'][name]">'+party.type.substr(0,1).toUpperCase()+party.type.substr(1)+' Name</label>'+
                            '<input type="text" class="form-control form-control-user" id="party['+i+'][name]" name="party['+i+'][name]" value="'+party.name+'" >'+
                        '</div>'+


                        '<div class="form-group ">'+
                            '<label class="font-weight-bold" for="party['+i+'][attorney_id]">'+party.type.substr(0,1).toUpperCase()+party.type.substr(1)+' Attorney</label>'+
                            '<select name="party['+i+'][attorney_id]" id="party-'+i+'-attorney_id" autocomplete="off"></select>'+
                        '</div>'+

                        '<div class="form-group ">'+
                            '<label class="font-weight-bold" for="party['+i+'][address]">Address<small> for '+party.type+', or <span class="font-weight-bold">if not '+party.type+', for the party.</span></small></label>'+
                            '<textarea id="party['+i+'][address]" name="party['+i+'][address]" class="form-control form-control-user" cols="40" rows="2" >'+((party.address != null) ? party.address : "")+'</textarea>'+
                        '</div>'+


                        '<div class="form-group 6">'+
                            '<label class="font-weight-bold" for="party['+i+'][telephone]">Daytime Telephone #</label>'+
                            '<input maxlength="16"  class="form-control form-control-user" id="party['+i+'][telephone]" name="party['+i+'][telephone]" value="'+((party.telephone != null) ? party.telephone : "")+'">'+
                        '</div>'+


                        '<div class="form-group">'+
                            '<label class="font-weight-bold" for="party['+i+'][email]">Emails (Separate multiple emails with ";")</label>'+
                            '<input type="text" class="form-control form-control-user" id="party['+i+'][email]" name="party['+i+'][email]" value="'+((party.email != null) ? party.email : "")+'">'+
                        '</div>';
                r++;
            }
        });
        $(".pp_party").empty().html(pp);
        $(".dr_party").empty().html(dr);
        var attorney_select = [];
        $.map( formdata.parties, function( party, i ) {
            if(party.type == "plaintiff" || party.type == "petitioner"){

                attorney_select[i] =     setPPTomSelect('party-'+i+'-attorney_id');
                if(party.attorney != null){
                    attorney_select[i].addOption({
                        id: party.attorney.id,
                        name: party.attorney.name,
                        bar_num: party.attorney.bar_num,
                    });
                    attorney_select[i].setValue(party.attorney_id);
                }
            }
            else if(party.type == "defendant" || party.type == "respondent"){
                    attorney_select[i] = setDRTomSelect('party-'+i+'-attorney_id');
                if(party.attorney != null){
                    attorney_select[i].addOption({
                        id: party.attorney.id,
                        name: party.attorney.name,
                        bar_num: party.attorney.bar_num,
                    });
                    attorney_select[i].setValue(party.attorney_id);
                }
            }
        });
        // $("#plaintiff").val(formdata.c_pltf_name);
        // $("#defendant").val(formdata.c_def_name);
        // if(formdata.pltf_attroney != null)
        // {
        //     attorney_select.addOption({
        //         id: formdata.pltf_attroney.id,
        //         name: formdata.pltf_attroney.name,
        //         bar_num: formdata.pltf_attroney.bar_num,
        //     });
        //     attorney_select.setValue(formdata.pltf_attroney.id);
        // }
        // if(formdata.def_attroney != null)
        // {
        //     defendant_attorney_select.addOption({
        //         id: formdata.def_attroney.id,
        //         name: formdata.def_attroney.name,
        //         bar_num: formdata.def_attroney.bar_num,
        //     });
        //     defendant_attorney_select.setValue(formdata.def_attroney.id);
        // }
        // $("#plaintiff_add").val(formdata.c_pltf_address);
        // $("#defendant_add").val(formdata.c_def_address);
        // $("#plaintiff_tel").val(formdata.c_pltf_phone);
        // $("#defendant_tel").val(formdata.c_def_phone);
        // $("#plaintiff_email").val(formdata.c_pltf_email);
        // $("#defendant_email").val(formdata.c_def_email);
        ////////////

        $('.other_matters_form').css('display', 'none');
        $(".f-form").css('display', 'none');
        if(formdata.form_type == "f-form")
        {
            // $(".f-form").css('display', 'flex');
            $("#gal").val(formdata.gal);
            $("#gal_tel").val(formdata.gal_tel);
            $("#gal_add").val(formdata.gal_add);
            $("#gal_email").val(formdata.gal_email);
            $.each(formdata.f_issues.split(","),function(i){
                $('input[type=checkbox][value="' + formdata.f_issues.split(",")[i]+ '"]').prop( "checked", true );

                if(formdata.f_issues.split(",")[i] === "other_matters")
                {
                    $('#other_matters_description').val(formdata.f_issues_other_notes);
                    showDescription();
                }
            });
            $(".f-form").show();


        }
        else{

        }
        if(formdata.previous == 1)
        {
            $("#previous_yes").prop( "checked", true );
        }else if(formdata.previous == 0)
        {
            $("#previous_no").prop( "checked", true );
        }
        $("#previous_case_num").val(formdata.previous_case_num);
        $("#origin").val(formdata.origin);
        $("#previous_case_tel").val(formdata.previous_case_tel);
        $("#previous_case_email").val(formdata.previous_case_email);
        if(formdata.previous == 1){

            $('#previous').removeClass('d-none');
            $("#previous_yes").prop("checked", true);
        }
        else{
            $('#previous').addClass('d-none');
            $("#previous_no").prop("checked", true);
        }

        if(formdata.injunction == 1 && formdata.form_type != "f-form"){

            $('#injunction').removeClass('d-none');
            $("#injunction_yes").prop("checked", true);
        }
        else{
            $('#injunction').addClass('d-none');
            $("#injunction_no").prop("checked", true);
        }


        $("#availability").val(formdata.availability);

        if(formdata.p_signature != null && formdata.form_type === 'f-form'){
            $("#signed_by").html('Signed by Attorney/Petitioner)')
        } else{
            $("#signed_by").html('Signed by Attorney/Plaintiff)')
        }
        if(formdata.d_signature != null && formdata.form_type === 'sc-form'){
            $("#signed_by").html('Signed by Attorney/Respondent)')
        } else{
            $("#signed_by").html('Signed by Attorney/Defendant)')
        }
        $("#p_signature").val(formdata.p_signature);
        $("#d_signature").val(formdata.d_signature);

        $("#scformdetails").modal("show");
    }

    function approveSC() {



        //if (confirm("Are you sure you want to approve this?")) {
            let url = '{{ env('APP_URL')  }}/mediation/case/scformapprove';
            Swal.fire({
                title: 'Are you sure?',
                text: "Please provide comments on approval!",
                icon: 'info',
                input: 'textarea',
                inputLabel: 'An email notification is sent to all parties once the form is approved.',
                inputPlaceholder: 'Type your public notes here...',
                inputAttributes: {
                    'aria-label': 'Type your public notes here'
                },
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!',
                customClass: {
                    validationMessage: 'my-validation-message'
                },
                preConfirm: (value) => {
                    if (!value) {
                        Swal.showValidationMessage(
                            '<i class="fa fa-info-circle"></i> Approval reason is required!'
                        )
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: $('form').serialize()+ '&approval_reason=' + result.value,
                        dataType: 'JSON',
                        success: function(data) {
                            Swal.fire({
                                title: 'Approved!',
                                text: 'Your case is approved.',
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#3085d6',
                                showCancelButton: true,
                                confirmButtonText: 'Approve Another?',
                                cancelButtonText: 'Schedule Mediation'
                            }).then((result) => {
                                console.log(data);
                                if (result.isConfirmed) {
                                    location.reload();
                                } else{if(data.form_type=="sc-form"){
                                    location.href = '/mediation/' + data.id + '/edit'
                                }else{location.href = '/mediationfamily/' + data.id + '/edit'}

                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            var err = eval("(" + xhr.responseText + ")");
                            alert(err.message);
                        }
                    });
                }
            })
        // }else {
        //     return false;
        // }
    }

    function deleteSC() {
        let url = '{{ env('APP_URL')  }}/mediation/case/scformdelete';
        Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'Cancel Reason',
                inputPlaceholder: 'Type your message here...',
                inputAttributes: {
                    'aria-label': 'Type your message here'
                },
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Cancel it!',
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
                        url: url,
                        method: 'POST',
                        data: {
                            'sc_id': $("#mediation_case_id").val(),
                            'cancel_reason': result.value
                        },
                        dataType: 'JSON',
                        success: function(data) {
                            Swal.fire(
                                'Cancelled!',
                                'Your case is cancelled.',
                                'success'
                            )
                            location.reload();
                        },
                        error: function(response) {
                            console.log('Error');
                        }
                    });
                }
            });


    }


// });
</script>
@endsection
