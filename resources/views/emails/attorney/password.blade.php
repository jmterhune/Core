@component('mail::message')
# JACS Account Password

## Your attorney(s) account has been created!

The username and password will be the Florida bar number **{{ $attorney->bar_num }}**.

*NOTE: The username will always be the attorney's bar number **{{ $attorney->bar_num }}**.*

After logging in for the first time (for each attorney), you will be prompted to change the password.
The password must be more than 14 characters and is case-sensitive.
Please do not use dashes in the case number when scheduling.

JACS Login: [Click Here](https://jacs.flcourts18.org/login)

Thanks,<br>
{{ config('app.name') }}
@endcomponent
