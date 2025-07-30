@component('mail::message')
# Florida 18th Judicial Circuit Court

This is an automated message informing all parties of the Mediation instructions for the upcoming case:

{{ $instructions }}

<x-mail::table>
@if($case->form_type == "f-form")

|                     |                        |
| ------------------  | ---------------------- |
| **Case Number**  | {{ $case->c_caseno }} |
| **Judge**       | {{ $case->judge->name }} |
| **Type of Case** | {{ $case->c_type }} |
@isset($case->PltfAttroney)
| **Petitioner Attorney** | {{ $case->PltfAttroney->name }} |
@endisset
@isset($case->c_pltf_name)
| **Petitioner** | {{ $case->c_pltf_name }} |
@endisset
@isset($case->DefAttroney)
| **Respondent Attorney** |  {{ $case->DefAttroney->name }} |
@endisset
@isset($case->c_def_name)
| **Respondent** | {{ $case->c_def_name }} |
@endisset

@else

|                     |                        |
| ------------------  | ---------------------- |
| **Case Number**  | {{ $case->c_caseno }} |
| **Judge**       | {{ $case->judge->name }} |
| **Type of Case** | {{ $case->c_type }} |
@isset($case->PltfAttroney)
| **Plaintiff Attorney** | {{ $case->PltfAttroney->name }} |
@endisset
@isset($case->c_pltf_name)
| **Plaintiff** | {{ $case->c_pltf_name }} |
@endisset
@isset($case->DefAttroney)
| **Defendant Attorney** |  {{ $case->DefAttroney->name }} |
@endisset
@isset($case->c_def_name)
| **Defendant** | {{ $case->c_def_name }} |
@endisset

@endif
</x-mail::table>

**Please Do not reply to this email**

Thanks,

{{ config('app.name') }}
@endcomponent
