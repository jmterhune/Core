@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
@endphp

@if($column['value'])

        <i class="las la-check-circle text-success"></i>


@else
    <span>
        <i class="las la-ban text-danger"></i>
    </span>

@endif
