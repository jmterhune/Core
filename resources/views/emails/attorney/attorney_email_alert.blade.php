@component('mail::message')
# New JACS Account Request

@if($requester != $attorney->name)
{{ $requester }} is requesting access for {{ $attorney->name }}.
@else
{{ $attorney->name }} is requesting access.
@endif

<x-mail::panel>

Use the buttons below to verify and enable the Attorney's account.
<x-mail::button :url="$attorney_url" color="success">
    View Attorney
</x-mail::button>
<x-mail::button :url="$enable_url" color="success">
    Enable
</x-mail::button>

</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
