@component('mail::message')
# Florida 18th Judicial Circuit

## Hearing Cancellation
## {{ $event->timeslot->date }} at {{ $event->timeslot->start_time }} for {{ $event->timeslot->length }}

## {{ $event->timeslot->court->description }} - {{ $event->timeslot->court->judge->name }}

This is an automated message informing all parties that this hearing has been cancelled due to the following reason:

{{ $event->cancellation_reason }}

@isset($custom_body)

{!! $custom_body !!}

@endisset

<x-mail::table>
|                     |                        |
| ------------------  | ---------------------- |
| **Case Number**  | {{ $event->case_num }} |
| **Motion**       | @if($event->motion->id == 221) {{ $event->custom_motion }} @else {{ $event->motion->description }} @endif |
@isset($event->timeslot->court->category_print)
| **Category** | {{ $event->category->description }} |
@endif
@isset($event->attorney)
| **Attorney** | {{ $event->attorney->name }} |
@endif
@isset($event->plaintiff)
| **Plaintiff** | {{ $event->plaintiff }} |
@endisset
@isset($event->opp_attorney)
| **Opposing Attorney** |  {{ $event->opp_attorney->name }} |
@endif
@isset($event->defendant)
| **Defendant** | {{ $event->defendant }} |
@endisset

</x-mail::table>

**Please Do not reply to this email**

Thanks,<br>
{{ config('app.name') }}
@endcomponent
