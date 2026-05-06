@props(['themeSlug'])

<!-- Default Core Scripts -->
<script src="{{ url('assets/js/tabler.min.js') }}"></script>
<script src="{{ url('assets/js/jquery-3.6.4.min.js') }}"></script>
<script src="{{ url('assets/js/custom-toast.js?v=1.2') }}"></script>
<script src="{{ url('assets/js/choices.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/hugerte@1/hugerte.min.js"></script>

@php
    // Dynamically call the theme's footer() method if it exists
    $themeClassName = str_replace(' ', '', ucwords(str_replace('-', ' ', $themeSlug))) . 'Theme';
    if (class_exists($themeClassName)) {
        $themeObj = new $themeClassName();
        if (method_exists($themeObj, 'footer')) {
            $themeObj->footer();
        }
    }
@endphp
