

@component('mail::message')
@if($ticket->action=="New")
Thank you for submitting your request to the JACS system.
Please be advised that it may take up to 24 hours to get an update.

@else
Thank you for submitting your request to the JACS system.
Please be advised that it may take up to 24 hours to get an update.

@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
