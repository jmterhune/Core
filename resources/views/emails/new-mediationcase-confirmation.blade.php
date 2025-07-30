@component('mail::message')
# Florida 18th Judicial Circuit Court

@isset($case->events[0])
## Hearing Confirmation
## {{ $case->events[0]->e_sch_datetime }} 
@endisset

This is an automated message informing all parties that this case has been approved with the following comments:

{{ $case->approval_reason }}

<x-mail::table>
@if($case->form_type == "f-form")
|                     |                        |
| ------------------  | ---------------------- |
| **Case Number**  | {{ $case->c_caseno }} |
| **Judge**       | {{ $case->judge->name }} |
| **Type of Case** | {{ $case->c_type }} |
@isset(implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type', ['plaintiff', 'petitioner'])->pluck('attorney_id'))->pluck('name')->toArray()))
| **Petitioner Attorney** | {{ implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type', ['plaintiff', 'petitioner'])->pluck('attorney_id'))->pluck('name')->toArray()) }} |
@endisset
@isset(implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', 'petitioner')->pluck('name')->toArray()))
| **Petitioner** | {{ implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', 'petitioner')->pluck('name')->toArray()) }} |
@endisset
@isset(implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type',  'respondent')->pluck('attorney_id'))->pluck('name')->toArray()))
| **Respondent Attorney** |  {{ implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type',  'respondent')->pluck('attorney_id'))->pluck('name')->toArray()) }} |
@endisset
@isset(implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', 'respondent')->pluck('name')->toArray()))
| **Respondent** | {{ implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', 'respondent')->pluck('name')->toArray()) }} |
@endisset

@else
|                     |                        |
| ------------------  | ---------------------- |
| **Case Number**  | {{ $case->c_caseno }} |
| **Judge**       | {{ $case->judge->name }} |
| **Type of Case** | {{ $case->c_type }} |
@isset(implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type', ['plaintiff', 'petitioner'])->pluck('attorney_id'))->pluck('name')->toArray()))
| **Plaintiff Attorney** | {{ implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type', ['plaintiff', 'petitioner'])->pluck('attorney_id'))->pluck('name')->toArray()) }} |
@endisset
@isset(implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', ['plaintiff', 'petitioner'])->pluck('name')->toArray()))
| **Plaintiff** | {{ implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', ['plaintiff', 'petitioner'])->pluck('name')->toArray()) }} |
@endisset
@isset(implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type', ['defendant', 'respondent'])->pluck('attorney_id'))->pluck('name')->toArray()))
| **Defendant Attorney** |  {{ implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type', ['defendant', 'respondent'])->pluck('attorney_id'))->pluck('name')->toArray()) }} |
@endisset
@isset(implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', ['defendant', 'respondent'])->pluck('name')->toArray()))
| **Defendant** | {{ implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', ['defendant', 'respondent'])->pluck('name')->toArray()) }} |
@endisset

@endif

</x-mail::table>

**Please Do not reply to this email**

<!-- <x-mail::panel>

If you like to subscribe for a Reminder for this Hearing, please click the button below.


</x-mail::panel> -->

Thanks,<br>
{{ config('app.name') }}
@endcomponent
