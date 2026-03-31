@php
    $logoUrl = asset('images/darklogo.png');
@endphp

<img
    alt="{{ config('app.name') }} logo"
    src="{{ $logoUrl }}"
    loading="lazy"
    style="height: 80px;"
    class=""
/>
