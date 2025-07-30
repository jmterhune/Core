@component('mail::message')
# Florida 18th Judicial Circuit Court

## Hearing Rescheduling
The hearing on: {{ $old_timeslot->date }} at {{ $old_timeslot->start_time }} for {{ $old_timeslot->length }} has been **RESCHEDULED** to the following Date/Time:
## {{ $event->timeslot->date }} at {{ $event->timeslot->start_time }} for {{ $event->timeslot->length }}

## {{ $event->timeslot->court->description }} - {{ $event->timeslot->court->judge->name }}

<x-mail::table>
|                     |                        |
| ------------------  | ---------------------- |
| **Case Number**  | {{ $event->case_num }} |
| **Motion**       | @if($event->motion->id == 221) {{ $event->custom_motion }} @else {{ $event->motion->description }} @endif |
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

<x-mail::panel>

If you like to subscribe for a Reminder for this Hearing, please click the button below.
<x-mail::button :url="$url" color="success">
    Set Reminder!
</x-mail::button>

</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
