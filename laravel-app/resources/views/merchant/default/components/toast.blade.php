<script>
    function showToast(type, message) {
        toastr.options = {
            "duration": 3000,
            "animationDuration": 400,
            "progressBar": true,
            "autoClose": true,
            "closeButton": true,
            "closeButtonIcon": "csm-toast-close-icon",
            "positionClass": "csm-toast-top-right",
            "showIcon": true,
            "preventDuplicates":true,
            "icons":{
                "info":"csm-toast-info-icon",
                "warning":"csm-toast-warning-icon",
                "success":"csm-toast-success-icon",
                "error":"csm-toast-error-icon"
            },
            "colorsClasses":{
                "info":"csm-toast-info",
                "warning":"csm-toast-warning",
                "success":"csm-toast-success",
                "error":"csm-toast-error"
            }
        };

        switch(type) {
            case'success':
                toastr.success(message);
                break;
            case 'error':
                toastr.error(message);
                break;
            default:
                toastr.info(message);
                break;
        }
    }
</script>

@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
    showToast('success', '{{ session('success') }}');
});
</script>
@endif

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
    showToast('error', '{{ session('error') }}');
});
</script>
@endif

@if (session('errors'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
    var errorMessages = '';
    @foreach ($errors->all() as $error)
        errorMessages += '<div>{{ $error }}</div>';
    @endforeach
    showToast('error', errorMessages);
});
</script>
@endif
