<!-- field_type_name -->
<?php
//get object data
$court_types = DB::table('court_types')->select('id','old_id')->get();
$counties = DB::table('counties')->select('id','name','code')->get();

$get_case_format = DB::table('courts')->select('case_num_format','case_format_type')->where('id',Request::segments()[1])->first();

?>
<div class="form-group col-md-12 required">
   <label>{!! $field['label'] !!}</label>
   <div class="form-group col-md-12 case-format-row">
     <input
        type="radio"
        name="case_format_type"
        value="1"
        id="first_radio"

     >
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='4'
          minlength='4'
          placeholder='xxxx'
      >
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='7'
          minlength='7'
          placeholder="xxxxxxx"
      >
   </div>
   <div class="form-group col-md-12 case-format-row">
     <input
        type="radio"
        name="case_format_type"
        value="2"
        id="second_radio"
     >
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='4'
          minlength='4'
          placeholder='xxxx'
      >
      <select class="case_num_format_multiple">
         <option value="0"></option>
        @foreach($court_types as $court_type)
        <option value="{{$court_type->old_id}}">{{$court_type->old_id}}</option>
        @endforeach
      </select>
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='7'
          minlength='7'
          placeholder='xxxxxxx'
      >
   </div>
   <div class="form-group col-md-12 case-format-row">
     <input
        type="radio"
        name="case_format_type"
        value="3"
        id="third_radio"
        checked

     >
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='2'
          minlength='2'
          placeholder='County Code'
          id="case_format_input_first"
      >
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='4'
          minlength='4'
          placeholder='Year'
          id="case_format_input_second"
      >
       <select class="case_num_format_multiple">
        <option value="0"></option>
        @foreach($court_types as $court_type)
        <option value="{{$court_type->old_id}}">{{$court_type->old_id}}</option>
        @endforeach
      </select>
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='6'
          minlength='6'
          placeholder='Case Number'
          id="case_format_input_three"
      >
      <input
          type="text"
          value="XXXX"
          class="col-md-2 case_num_format_multiple"
          maxlength='4'
          minlength='4'

          id="case_format_input_four"
      >
      <input
          type="text"
          value="XX"
          class="col-md-2 case_num_format_multiple"
          maxlength='2'
          minlength='2'

          id="case_format_input_five"
      >
   </div>
   <div class="form-group col-md-12 case-format-row">
     <input
        type="radio"
        name="case_format_type"
        value="4"
        id="fourth_radio"

     >
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='4'
          minlength='4'
          placeholder='xxxx'
      >
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='7'
          minlength='7'
          placeholder='xxxxxx'
      >
      <input
          type="text"
          value=""
          class="col-md-2 case_num_format_multiple"
          maxlength='4'
          minlength='4'
          placeholder='xxxx'
      >
   </div>
   <div class="form-group col-md-12 case-format-row">
     <input
        type="radio"
        name="case_format_type"
        value="5"
        id="fifth_radio"
     >
      <input
          type="text"
          value=""
          class="col-md-4 case_num_format_multiple"
          placeholder='xxxxxxxxxxxx'
      >

      <input type="hidden"
      name="case_num_format"
      id="case_format_val"
      value="{{ $field['value'] ?? null }}"
      >

   </div>
    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
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
           $(document).ready(function()
           {

            @if(!empty($get_case_format->case_num_format))
              var format = '<?php echo $get_case_format->case_num_format ?>';

              @if($get_case_format->case_format_type === 1)
              var valTokens = format.split("-");
              $("#first_radio").prop('checked', true);
              var case_format_fields = $('.case-format-row').children("#first_radio").siblings('.case_num_format_multiple');
              for(var j=0; j<valTokens.length; j++) {
                $(case_format_fields[j]).val(valTokens[j]);
              }
              @elseif($get_case_format->case_format_type === 2)
              var valTokens = format.split("-");
              $("#second_radio").prop('checked', true);
              var case_format_fields = $('.case-format-row').children("#second_radio").siblings('.case_num_format_multiple');
              for(var j=0; j<valTokens.length; j++) {
                $(case_format_fields[j]).val(valTokens[j]);
              }
              @elseif($get_case_format->case_format_type === 3)
              var valTokens = format.split("-");
              $("#third_radio").prop('checked', true);
              var case_format_fields = $('.case-format-row').children("#third_radio").siblings('.case_num_format_multiple');
              for(var j=0; j<valTokens.length; j++) {
                $(case_format_fields[j]).val(valTokens[j]);
              }
              @elseif($get_case_format->case_format_type === 4)
              var valTokens = format.split("-");
              $("#fourth_radio").prop('checked', true);
              var case_format_fields = $('.case-format-row').children("#fourth_radio").siblings('.case_num_format_multiple');
              for(var j=0; j<valTokens.length; j++) {
                $(case_format_fields[j]).val(valTokens[j]);
              }
              @elseif($get_case_format->case_format_type === 5)
              var valTokens = format.split("-");
              $("#fifth_radio").prop('checked', true);
              var case_format_fields = $('.case-format-row').children("#fifth_radio").siblings('.case_num_format_multiple');
              for(var j=0; j<valTokens.length; j++) {
                $(case_format_fields[j]).val(valTokens[j]);
              }
              @endif
            @endif

            $('.case_num_format_multiple').keyup(function()
            {
               if(isradiochecked($(this)))
                evaluateformfields($(this));
            });

            $('.case_num_format_multiple').change(function()
            {
               if(isradiochecked($(this)))
                evaluateformfields($(this));
            });
            $('input[type=radio][name=case_format_type]').change(function() {
              if(isradiochecked($(this)))
                evaluateformfields($(this));

                });

           });

           function isradiochecked($changedfield)
           {
            return $changedfield.parent().find('input[type="radio"]').is(':checked');
           }

           function evaluateformfields($changedfield)
           {
              var assign_value= $changedfield.parent().children('.case_num_format_multiple');
              var case_format_val = [];
              assign_value.each(function(i, element)
              {
                case_format_val.push($(element).val());
              });
              case_format_val = case_format_val.join("-");
              $("#case_format_val").val(case_format_val);

           }
           var counties=<?=$counties ?>;
           $("#case_format_input_first").val(counties[0].code)

           $('select[name=county_id]').change(function(e) {
                var county_id=$(this).val();
                var counties=<?=$counties ?>;
                var county_code= counties.find(countie=>countie.id==county_id).code ;
                $("#case_format_input_first").val(county_code)
                $('#case_format_input_first').trigger('change');
           });


         </script>
      @endpush
@endif
