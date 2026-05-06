@props(['themeSlug'])

<!-- Default Core Assets -->
<link rel="stylesheet" href="{{ url('assets/css/tabler.min.css?v=1.7') }}" />
<link rel="stylesheet" href="{{ url('assets/css/choices.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-flags.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-payments.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-social.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-vendors.min.css" />
<style>@import url("{{ url('assets/css/inter.css') }}");</style>

@php
    // Dynamically call the theme's head() method if it exists
    $themeClassName = str_replace(' ', '', ucwords(str_replace('-', ' ', $themeSlug))) . 'Theme';
    if (class_exists($themeClassName)) {
        $themeObj = new $themeClassName();
        if (method_exists($themeObj, 'head')) {
            $themeObj->head();
        }
    }
@endphp
