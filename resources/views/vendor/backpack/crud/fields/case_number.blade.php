<!-- field_type_name -->
<?php
//get object data
$court_types = DB::table('court_types')->select('id','old_id')->get();
$counties = DB::table('counties')->select('id','name','code')->get();

$event = \App\Models\Event::find(CRUD::getCurrentEntryId());
$case_num_format = $event->timeslot->court->case_num_format;

$user_defined_fields = CRUD::getCurrentEntry()->template;
$court_templates = $event->timeslot->court->templates->where('display_on_schedule',1);

?>


<div class="form-group col-md-12 required">

    <div class = "dynamic_case_number_format"></div>

    <input type="hidden" class="form-control" name="case_num" id="case_num" required value="{{CRUD::getCurrentEntry()->case_num}}">
</div>



@if ($crud->checkIfFieldIsFirstOfItsType($field))
    {{-- FIELD EXTRA CSS  --}}
    {{-- push things in the after_styles section --}}

    @push('crud_fields_styles')
        <!-- no styles -->
    @endpush

    {{-- FIELD EXTRA JS --}}
    {{-- push things in the after_scripts section --}}

    @push('crud_fields_scripts')
        <script type="text/javascript">
            $(document).ready(function() {

                get_dynamic_case_number_format_fields('{{ CRUD::getCurrentEntry()->case_num }}');

                function evaluateformfields($changedfield) {

                    var case_format_val = [];
                    var case_num = '{{ $case_num_format }}';
                    var valTokens = case_num.split("-");

                    for (var i = 1; i <= valTokens.length; i++) {
                        var value = $("#case_num_format_multiple" + i).val();
                        case_format_val.push(value);

                    }
                    console.log(case_format_val.join('-'));
                    $("#case_num").val(case_format_val.join('-'));
                }


                function get_dynamic_case_number_format_fields(case_num_format) {
                    console.log(case_num_format);
                    var fields = '';
                    if (case_num_format != null) {
                        var format = case_num_format;
                        var split_format = format.split('-');
                        if (split_format.length == 1) {
                            fields = '<label for="case_num">Case Number</label>' +

                                '<div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">' +

                                '<div class="col-md-12 mb-3">' +
                                '<label for="case_num"></label>' +
                                '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple1" required value="' + split_format[0] + '">' +
                                '<div class="valid-feedback">' +
                                'Looks good!' +
                                '</div>' +
                                '</div>';

                        } else if (split_format.length == 2) {

                            fields = '<label for="case_num">Case Number</label>' +
                                '<div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">' +
                                '<div class="col-md-4 mb-4">' +
                                '<label for="case_num"></label>' +
                                '<input type="text" class="form-control case_num_format_multiple" maxlength="4" id="case_num_format_multiple1" required value="' + split_format[0] + '">' +
                                '<div class="valid-feedback">' +
                                'Looks good!' +
                                '</div>' +
                                '</div>' +
                                '<div class="col-md-4 mb-4">' +
                                '<label for="case_num"></label>' +
                                '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple2" maxlength="7" required value="' + split_format[1] + '">' +
                                '<div class="valid-feedback">' +
                                'Looks good!' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                        } else if (split_format.length == 3) {
                            if (split_format[1].length == 2 || split_format[1] === 0) {
                                fields = '<label for="case_num">Case Number</label>' +
                                    '<div class="form-row col-md-12 case-format-row" style="margin:0px 0px 0px -20px;">' +
                                    '<div class="col-md-2 mb-2">' +

                                    '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple1"  maxlength="4" required value="' + split_format[0] + '">' +
                                    '<div class="valid-feedback">' +
                                    ' Looks good!' +
                                    '</div>' +
                                    '</div>' +
                                    '<div class="col-md-2 mb-2">' +
                                    '<select class="form-control col-md-12 case_num_format_multiple" id="case_num_format_multiple2">';
                                var court_types = @php echo json_encode($court_types) @endphp;
                                $.each(court_types, function (key, court_type) {
                                    var selected = (court_type["old_id"] == split_format[1]) ? "selected" : "";
                                    fields += '<option value="' + court_type["old_id"] + '"' + selected + '>' + court_type["old_id"] + '</option>';
                                })

                                fields += '</select>' +
                                    '</div>' +
                                    '<div class="col-md-2 mb-2">' +
                                    '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple3"  maxlength="7" required value="' + split_format[2] + '">' +
                                    '<div class="valid-feedback">' +
                                    'Looks good!' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>';
                            } else {
                                fields = '<label for="case_num">Case Number</label>' +
                                    '<div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">' +

                                    '<div class="col-md-3 mb-3">' +
                                    '<label for="case_num"></label>' +
                                    ' <input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple1" maxlength="4" required value="' + split_format[0] + '">' +
                                    ' <div class="valid-feedback">' +
                                    ' Looks good!' +
                                    ' </div>' +
                                    ' </div>' +

                                    ' <div class="col-md-3 mb-3">' +
                                    ' <label for="case_num"></label>' +
                                    ' <input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple2" maxlength="7" required value="' + split_format[1] + '">' +
                                    ' <div class="valid-feedback">' +
                                    ' Looks good!' +
                                    ' </div>' +
                                    ' </div>' +

                                    ' <div class="col-md-3 mb-3">' +
                                    '<label for="case_num"></label>' +
                                    '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple3"   maxlength="4" required value="' + split_format[2] + '">' +
                                    '<div class="valid-feedback">' +
                                    ' Looks good!' +
                                    ' </div>' +
                                    '</div>' +
                                    ' </div>';
                            }
                        } else if (split_format.length == 6 || split_format.length == 5 || split_format.length == 4) {
                            fields = '<label for="case_num">Case Number</label>' +
                                '<div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">' +

                                '<div class="col-md-1 mb-1">' +
                                '<label for="case_num"></label>' +
                                ' <input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple1" maxlength="2" required value="' + split_format[0] + '">' +
                                ' <div class="valid-feedback">' +
                                ' Looks good!' +
                                ' </div>' +
                                '</div>' +
                                '<div class="col-md-2 mb-2">' +
                                ' <label for="case_num"></label>' +
                                ' <input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple2"  maxlength="4" required value="' + split_format[1] + '" placeholder="year">' +
                                ' <div class="valid-feedback">' +
                                ' Looks good!' +
                                ' </div>' +
                                '</div>' +
                                ' <div class="col-md-2 mb-2">' +
                                ' <label for="case_num"></label>' +
                                '<select class="form-control col-md-12 case_num_format_multiple" id="case_num_format_multiple3">';
                            var court_types = @php echo json_encode($court_types) @endphp;
                            $.each(court_types, function (key, court_type) {
                                var selected = (court_type["old_id"] == split_format[2]) ? "selected" : "";
                                fields += '<option value="' + court_type["old_id"] + '"' + selected + '>' + court_type["old_id"] + '</option>';
                            })

                            fields += ' </select>' +
                                '</div>' +

                                '<div class="col-md-2 mb-2">' +
                                '<label for="case_num"></label>' +
                                '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple4" maxlength="6" required value="' + split_format[3] + '" placeholder="case number">' +
                                ' <div class="valid-feedback">' +
                                'Looks good!' +
                                '</div>' +
                                '</div>' +
                                ' <div class="col-md-2 mb-2">' +
                                '<label for="case_num"></label>' +
                                '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple5" maxlength="4" required value="' + split_format[4] + '">' +
                                '<div class="valid-feedback">' +
                                ' Looks good!' +
                                '</div>' +
                                ' </div>' +
                                ' <div class="col-md-1 mb-1">' +
                                '<label for="case_num"></label>' +
                                '<input type="text" class="form-control case_num_format_multiple" id="case_num_format_multiple6"  maxlength="2" required value="' + split_format[5] + '">' +
                                '<div class="valid-feedback">' +
                                ' Looks good!' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                        }
                    } else {
                        fields = '<label for="case_num">Case Number</label>' +
                            ' <div class="form-row col-md-12 case-format-row" style="margin:-23px 0px 0px -20px;">' +
                            '<div class="col-md-4 mb-3">' +

                            '<input type="text" class="form-control case_num_format_multiple" required>' +
                            '<div class="valid-feedback">' +
                            ' Looks good!' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                    }

                    $(".dynamic_case_number_format").html(fields);
                    $('.case_num_format_multiple').keyup(function () {
                        evaluateformfields($(this));
                    });

                    $('.case_num_format_multiple').change(function () {
                        evaluateformfields($(this));
                    });
                }

            });

var j = 0;
            let court_templates = <?php echo $court_templates;?>;
            $.each(court_templates,function(index,court_template){

                var key = "";
                var template = "";
                var user_defined_fields_string = @php echo json_encode($user_defined_fields) @endphp;
                $.each(JSON.parse(user_defined_fields_string),function(index1,value1){
                    if(index1 === (court_template.field_name+index+"_|"+court_template.alignment+"_|"+court_template.field_type) && court_template.field_type == "yes_no"){
                        key = index1;
                        template = value1;
                        return true;
                    }else if(index1 === (court_template.field_name+"_|"+court_template.alignment+"_|"+court_template.field_type)){
                        key = index1;
                        template = value1;
                        return true;
                    }
                });
                if(key != "")
                {
                    let stringArray=key.split("_|");
                    if(stringArray[2] =="yes_no")
                    {
                        $('input[id=user_customer_field'+index+']').removeAttr("checked");
                        $('input[id=user_customer_field'+index+'][value='+template+']').attr('checked',true);
                    }else{
                        $("#"+ key.replace(/([^A-Za-z0-9-])/ig, "")).val(template);
                    }
                }
                
                j++;

           });

/*var i = 0;
var user_defined_fields_string = @php echo json_encode($user_defined_fields) @endphp;
            $.each(JSON.parse(user_defined_fields_string),function(index,template){
                const stringArray=index.split("_|")
                if(stringArray[2] =="yes_no")
                {
                    $('input[id=user_customer_field'+i+'][value='+template+']').attr('checked',true);
                }else{
                    $("#user_customer_field"+i).val(template);
                }
                i++;
	    })*/
        </script>
    @endpush
@endif
