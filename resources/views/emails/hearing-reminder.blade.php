@component('mail::message')
# Remainder for Hearing Case Number :{{$event->case_num}}

Case # :{{$event->case_num}}
Motion :{{$event->motion->description}}
Attorney :{{$event->attorney->name}}
Plaintiff :{{$event->plaintiff}}
Opposing Attorney :{{$event->opp_attorney->name}}
Defendant :{{$event->defendant}}

Confirmation # :{{$event->id}}

Thanks,<br>
{{ config('app.name') }}
@endcomponent





