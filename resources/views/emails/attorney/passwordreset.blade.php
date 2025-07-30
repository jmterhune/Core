@component('mail::message')
# JACS Account Password Reset

## Your attorney(s) account password has been reset!

The password has been reset to the Florida bar number **{{ $attorney->bar_num }}**.

*NOTE: The username will always be the attorney's bar number **{{ $attorney->bar_num }}**.*

After logging in you will be prompted to change the password.
The password must be more than 14 characters and is case-sensitive.

JACS Login: [Click Here](https://jacs.flcourts18.org/login)

Thanks,<br>
{{ config('app.name') }}
@endcomponent
