<x-mail::message>
# Small Claims Mediation Form

@isset($form_data['p_signature']) **{{ $form_data['plaintiff'] }}** @else **{{ $form_data['defendant'] }}** @endisset just submitted a Small Claims form.

Please see the information below.

**Case Number**: {{ $form_data['case_num'] }}

**Judge**: {{ $form_data['judge'] }}

**Type**: {{ $form_data['type'] }}

**Indigent/Insolvent**: @isset($form_data['petitioner']) Petitioner @endif  @if(isset($form_data['petitioner']) && isset($form_data['respondent'])) & @endif @isset($form_data['respondent']) Respondent @endif @if(!isset($form_data['petitioner']) && !isset($form_data['respondent'])) Neither @endif

<x-mail::table>
| Plaintiff Information |
| :---   |
| **Name**: {{ $form_data['plaintiff'] }}
@isset($form_data['plaintiff_att'])
| **Attorney**: {{ $form_data['plaintiff_att'] }}
@endisset

@isset($form_data['plaintiff_add'])
| **Address**: {{ $form_data['plaintiff_add'] }}
@endisset

@isset($form_data['plaintiff_tel'])
| **Phone**:{{ $form_data['plaintiff_tel'] }}
@endisset

@isset($form_data['plaintiff_email'])
| **Email**: {{ $form_data['plaintiff_email'] }}
@endisset

</x-mail::table>

___

<x-mail::table>
| Defendant Information |
| :---   |
| **Name**: {{ $form_data['defendant'] }}
@isset($form_data['defendant_att'])
| **Attorney**: {{ $form_data['defendant_att'] }}
@endisset

@isset($form_data['defendant_add'])
| **Address**: {{ $form_data['defendant_add'] }}
@endisset

@isset($form_data['defendant_tel'])
| **Phone**: {{ $form_data['defendant_tel'] }}
@endisset

@isset($form_data['defendant_email'])
| **Email**: {{ $form_data['defendant_email'] }}
@endisset

</x-mail::table>

@isset($form_data['previous'])

@isset($form_data['defendant_email'])
**Previous Case Number** : {{ $form_data['previous_case_num'] }}

**Origin** : {{ $form_data['origin'] }}
@endisset

@endisset

Signed by : @isset($form_data['p_signature']) {{ $form_data['plaintiff'] }} (Attorney/Plaintiff) @else {{ $form_data['defendant'] }} (Attorney/Defendant) @endisset


Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
