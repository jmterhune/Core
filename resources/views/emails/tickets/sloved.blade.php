@component('mail::message')
# JACS Ticket {{$tickets->ticket_number}} successfully resolved

## Your ticket status has been updated!


We are pleased to inform you that your support ticket  {{$tickets->ticket_number}} has been successfully resolved.

We appreciate your patience and understanding while our team worked to resolve your issue. We hope that the solution provided has met your expectations and that you are satisfied with the outcome.

Please let us know if there is anything else we can assist you with or if you have any feedback on your experience with our support team. Your satisfaction is important to us and we welcome any suggestions for improvement.

Thank you for choosing JACS for your support needs.

JACS Tickets: [Click Here](https://jacs-admin.flcourts18.org/tickets)





Thanks,<br>
{{ config('app.name') }}
@endcomponent
