@component('mail::message')
# Florida 18th Judicial Circuit Court

## Hearing Confirmation
## {{ $event->timeslot->date }} at {{ $event->timeslot->start_time }} for {{ $event->timeslot->length }}

## {{ $event->timeslot->court->description }} - {{ $event->timeslot->court->judge->name }}

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
@endisset
@isset($event->attorney)
| **Attorney** | {{ $event->attorney->name }} |
@endisset
@isset($event->plaintiff)
| **Plaintiff** | {{ $event->plaintiff }} |
@endisset
@isset($event->opp_attorney)
| **Opposing Attorney** |  {{ $event->opp_attorney->name }} |
@endisset
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
