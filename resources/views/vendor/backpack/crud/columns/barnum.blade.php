@php
    $column['text'] = $column['value'] ?? data_get($entry, $column['name']);
@endphp

<span>
    <a target="_blank" href="https://www.floridabar.org/directories/find-mbr/?barNum={{ $column['text'] }}">{{ $column['text'] }}</a>
</span>
