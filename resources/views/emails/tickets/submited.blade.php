@component('mail::message')
# JACS Ticket Submitted Successfully




Thank you for reaching out to JACS support team. Your ticket has been received and is being processed.

Your ticket details are as follows:

Ticket Number: {{$tickets->ticket_number}}
Subject: {{$tickets->subject}}
Description: {{$tickets->issue}}

Please note that response times may vary depending on the nature and urgency of your request.

In the meantime, you can check the status of your ticket by visiting our support portal and logging in with your ticket number. 
Thank you for choosing JACS support for your queryes.

For more information please log in to the system.

JACS Tickets: [Click Here](https://jacs-admin.flcourts18.org/tickets)





Thanks,<br>
{{ config('app.name') }}
@endcomponent
