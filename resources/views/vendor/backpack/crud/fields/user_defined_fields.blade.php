<?php
    $user_defined_fields = CRUD::getCurrentEntry()->timeslot->court->templates->where('display_on_schedule',1)
?>


    @foreach($user_defined_fields as $index => $user_defined_field)
        <div class="col-md-6 mb-3">
            @if($user_defined_field->field_type=="yes_no")
                <div class="form-group <?=($user_defined_field->yes_answer_required !=1)?'':"required" ?>">
                    <label for="plaintiff">{{$user_defined_field->field_name}}</label>
                    <div style="">
                        <label style="margin-left: 30px;">
                            <input type="radio" id = "user_customer_field{{$index}}" value="yes" class="form-check-input" name="templates_data[{{$user_defined_field->field_name}}_|{{$user_defined_field->alignment}}_|{{$user_defined_field->field_type}}]" <?=($user_defined_field->yes_answer_required !=1)?'':"required" ?>>Yes
                        </label>
                        <label style="margin-left:30px">
                            <input type="radio" id = "user_customer_field{{$index}}" value="no" class="form-check-input" name="templates_data[{{$user_defined_field->field_name}}_|{{$user_defined_field->alignment}}_|{{$user_defined_field->field_type}}]" <?=($user_defined_field->yes_answer_required !=1)?'':"required" ?>>No
                        </label>
                    </div>
                </div>
            @else
                <div class="form-group <?=($user_defined_field->required !=1)?'':"required" ?>">
		    <label for="plaintiff">{{$user_defined_field->field_name}}</label>
		    <input type="{{$user_defined_field->field_type}}" class="form-control" name="templates_data[{{$user_defined_field->field_name}}_|{{$user_defined_field->alignment}}_|{{$user_defined_field->field_type}}]" id = "{{  preg_replace('/[^A-Za-z0-9-]/', '',$user_defined_field->field_name.'_|'.$user_defined_field->alignment.'_|'.$user_defined_field->field_type )}}" value="{{empty($user_defined_field->default_value)?'':$user_defined_field->default_value}}"   <?=($user_defined_field->required !=1)?'':"required" ?>/>
                </div>
            @endif


        </div>
    @endforeach
