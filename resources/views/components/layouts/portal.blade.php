@props([
    'portalUser' => null,
    'title' => null,
])

@include('layouts.portal', [
    'portalUser' => $portalUser,
    'slot' => $slot,
    'title' => $title,
])