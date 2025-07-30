@extends(backpack_view('blank'))

@section('content')
<style>
    .table-borderless td{
        border:none !important;
    }
</style>
<main class="main pt-2">


    <section class="container-fluid">
        <h2>
            <span class="text-capitalize">Case Document</span>
        </h2>
    </section>

    <div class="container-fluid animated fadeIn">

        <div class="col-md-8">
            <div class="{{ $widget['class'] ?? 'alert alert-info' }}" role="alert">
                <h4 class="alert-heading">Note</h4>
                <p> This allow's a user to print the docket report for a calendar they have access to.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-md-6">

                        <div class="col-md-10">
                            <div class="form-group">
                                <label  for="validationServer02">Case#</label>
                                <div class="input-group" id="timeslot_end" data-target-input="nearest">
                                    <input id="case_number" type="text" name="case_number" class="form-control" value="{{$case->c_caseno}}" />
                                    <input type="button" class="btn btn-default" value="Validate" onclick="return searchCaseNumber();">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-10">
                            <div class="form-group">
                                <label id="case_event" for="validationServer02">Select Mediation Date</label>
                                <div class="input-group date" id="timeslot_end" data-target-input="nearest">
                                    <select id="case_event" name="case_event" class="form-control">
                                        @foreach($case->events as $event)
                                        <option value="{{$event->id}}">{{$event->e_sch_datetime}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-10">
                            <div class="form-group">
                                <label id="case_event" for="validationServer02">Documents List &nbsp;&nbsp; 
                                    <button class="btn btn-danger"onclick="return clearCaseDocuments({{$case->id}})">Clear Links</button>
                                </label>
                                <div class="input-group date" id="timeslot_end" data-target-input="nearest">
                                    <ul>
                                        @foreach($case_documents as $case_document)
                                            <li>{{$case_document}}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td colspan="2" style="vertical-align:top; text-align:left;"> <div class="badge bg-warning text-wrap">CHECK BOXS, TO EMAIL ADDRESS </div> </td>
                            </tr>
                            <tr>
                                <td><input class="form-control" name="TextBox1" type="text" id="TextBox1" value="{{$case->c_pltf_email}}"></td>
                                <td><input class="form-check-input" id="CheckBox1" type="checkbox" name="CheckBox1"><label for="CheckBox1" class="form-check-label">Petitioner</label></td>
                            </tr>
                            <tr>
                                <td><input class="form-control" name="TextBox2" type="text" id="TextBox2" value="{{$case->p_a_email}}"></td>
                                <td><input class="form-check-input" id="CheckBox2" type="checkbox" name="CheckBox2" ><label for="CheckBox2" class="form-check-label">Plaintiff Atty 1</label></td>
                            </tr>
                            <tr>
                                <td><input class="form-control" name="TextBox3" type="text" id="TextBox3" value="{{$case->p_a_email2}}"></td>
                                <td><input class="form-check-input" id="CheckBox3" type="checkbox" name="CheckBox3"><label for="CheckBox3" class="form-check-label">Plaintiff Atty 2</label></td>
                            </tr>
                            <tr>
                                <td><input class="form-control" name="TextBox4" type="text" id="TextBox4" value="{{$case->c_def_email}}"></td>
                                <td><input class="form-check-input" id="CheckBox4" type="checkbox" name="CheckBox4"><label for="CheckBox4" class="form-check-label">Defendant</label></td>
                            </tr>
                            <tr>
                                <td><input class="form-control" name="TextBox5" type="text" id="TextBox5" value="{{$case->d_a_email}}"></td>
                                <td><input class="form-check-input" id="CheckBox5" type="checkbox" name="CheckBox5"><label for="CheckBox5" class="form-check-label">Defendant Atty 1</label></td>
                            </tr>
                            <tr>
                                <td><input class="form-control" name="TextBox6" type="text" id="TextBox6" value="{{$case->d_a_email2}}"></td>
                                <td><input class="form-check-input" id="CheckBox6" type="checkbox" name="CheckBox6"><label for="CheckBox6" class="form-check-label">Defendant Atty 2</label></td>
                            </tr>

                            <tr>
                                <td><input class="form-control" name="Tb_return" type="text" value="Kathy.mulvaney@flcourts18.org" id="Tb_return"></td>
                                <td>Return Address &amp; BCC</label></td>
                            </tr>

                            <tr>
                                <td colspan="2">Subject:<input class="form-control" name="Tb_subject" type="text" value="SERVICE OF COURT DOCUMENT" id="Tb_subject" style="width:339px;"></td>
                            </tr>
                            <tr>
                                <td style="vertical-align:top; text-align:right;"> &nbsp;&nbsp;&nbsp;<input type="submit" name="Bt_email" value="Email Document List" id="Bt_email"></td>
                                <td>&nbsp;</td>

                            </tr>

                        </tbody>
                    </table>
                    </div>
                </div>
                
                <div class="form-group row">
                    <table>
                    <tr>
                                    <td colspan="3">Extra field 1 optional:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="form-control" name="Tb_extra1" type="text" id="Tb_extra1" style="width:800px;"></td>
                                </tr>
                                <tr>
                                    <td colspan="3">Email message optional:&nbsp;<input class="form-control" name="Tb_message" type="text" id="Tb_message" style="width:800px;"></td>
                                </tr>

                    </table>
                </div>
                <div class="row">&nbsp;</div>
                <div class="form-group row">
                    <table class="table col-md-8">
                        <tr>
                            <th></th>
                            <th>Title</th>
                            <th>File</th>
                            <th>Template</th>
                            <th>Valid Date</th>
                            <th>Original</th>
                            <th></th>
                        </tr>
                        @foreach($documents as $document)
                        <tr>
                            <td>
                                <a href="{{ url('mediation/documents/').'/'.$document->id.'/edit' }}">Edit</a> &nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="javascript:void(0)" onclick="return buildCaseDocument({{$document->id}},{{$case->id}});">Build</a>
                            </td>
                            <td>{{$document->d_title}}</td>
                            <td>{{$document->d_fname}}</td>
                            <td><a href="{{url('mediation/document/download')}}/{{$document->id}}">View</a></td>
                            <td>{{$document->d_valid_date}}</td>
                            <td>{{$document->d_original}}</td>
                            <td></td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection

@section('after_scripts')
<script>
function buildCaseDocument(documentId,caseId)
{
    $.ajax({
                url: "<?= env('APP_URL') ?>/mediation/case/document/build",
                method: 'POST',
                data: {'documentId':documentId, 'caseId': caseId},
                dataType: 'JSON',
                success:function(response)
                {
                    window.location.reload();
                },
                error: function(response) {
                    alert('error');
                }
            });
}

function clearCaseDocuments(caseId)
{
    $.ajax({
                url: "<?= env('APP_URL') ?>/mediation/case/document/delete",
                method: 'POST',
                data: {'caseId': caseId},
                dataType: 'JSON',
                success:function(response)
                {
                    window.location.reload();
                },
                error: function(response) {
                    alert('error');
                }
            });
}

function searchCaseNumber()
{
    $.ajax({
        url: "<?= env('APP_URL') ?>/mediation/case/search",
        method: 'POST',
        data: {'case_number': $("#case_number").val()},
        dataType: 'JSON',
        success:function(response)
        {
            if(response != NULL)
            {
             window.location.href = "<?= env('APP_URL') ?>/mediation/case/documents/"+response.id;
            }
        },
        error: function(response) {
            alert('invalid case number');
        }
    });
}
</script>
@endsection