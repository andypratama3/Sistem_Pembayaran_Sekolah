{{-- resources/views/components/swal-flash.blade.php --}}
@push('scripts')
@if (Session::has('success'))
<script>
    if (window.Toast) {
        window.Toast.fire({ icon: 'success', title: '{{ addslashes(Session::get('success')) }}' });
    }
</script>
@endif

@if (Session::has('error'))
<script>
    if (window.Toast) {
        window.Toast.fire({ icon: 'error', title: '{{ addslashes(Session::get('error')) }}' });
    }
</script>
@endif

@if (Session::has('warning'))
<script>
    if (window.Toast) {
        window.Toast.fire({ icon: 'warning', title: '{{ addslashes(Session::get('warning')) }}' });
    }
</script>
@endif

@if (Session::has('info'))
<script>
    if (window.Toast) {
        window.Toast.fire({ icon: 'info', title: '{{ addslashes(Session::get('info')) }}' });
    }
</script>
@endif
@endpush