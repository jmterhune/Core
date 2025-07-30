@component('mail::message')
# JACS Ticket Status Update

## Your ticket status has been updated!

We are writing to update you on the status of your support ticket {{$tickets->ticket_number}}.
The current status of your ticket is {{$tickets->status->name}}. Our team has been working on resolving your issue and we apologize for any inconvenience this may have caused.

If you have any additional information or concerns, We will continue to update you on the progress of your ticket and will let you know as soon as it is resolved.

Thank you for choosing JACS support for your support needs.

For more information please log in to the system.

JACS Tickets: [Click Here](https://jacs-admin.flcourts18.org/tickets)





Thanks,<br>
{{ config('app.name') }}
@endcomponent
