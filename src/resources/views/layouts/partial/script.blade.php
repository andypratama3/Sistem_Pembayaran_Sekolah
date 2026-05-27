<script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
<!-- vendors.min.js {always must need to be top} -->
<script src="{{ asset('assets/vendors/js/datepicker.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>
<!--! END: Vendors JS !-->
<!--! BEGIN: Apps Init  !-->
<script src="{{ asset('assets/js/common-init.min.js') }}"></script>
{{-- <script src="{{ asset('assets/js/dashboard-init.min.js')}}"></script> --}}
<!--! END: Apps Init !-->
<!--! BEGIN: Theme Customizer  !-->
<script src="{{ asset('assets/js/theme-customizer-init.min.js') }}"></script>
<!--! BEGIN: Theme Mode Fix (Dark/Light/Fullscreen/Tab) !-->
{{-- <script src="{{ asset('assets/js/theme-mode-fix.js') }}"></script> --}}
<!--! END: Theme Mode Fix !-->
<script src="{{ asset('assets/vendors/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/select2-active.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/dataTables.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/dataTables.bs5.min.js') }}"></script>

{{-- format Rupiah --}}
<script src="{{ asset('assets/js/rupiah.js') }}"></script>

{{-- SweetAlert2 --}}

<!--! BEGIN: Laravel Echo - Reverb Real-time (Configured in app.blade.php) !-->
<!--! Using Reverb for WebSocket - CDN import & initialization in main layout !-->
<!--! END: Laravel Echo !-->

<script>
    function initSelect2(scope) {
        var container = scope || document;
        
        // Default selects (searchable, full width)
        $(container).find('[data-select2-selector="default"]').not('.select2-hidden-accessible').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        });

        // Status selects (no search, with bg color formatting)
        $(container).find('[data-select2-selector="status"]').not('.select2-hidden-accessible').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    theme: 'bootstrap-5',
                    templateResult: bgformat,
                    templateSelection: bgformat,
                    minimumResultsForSearch: Infinity
                });
            }
        });

        // Multiple selects
        $(container).find('[data-select2-selector="multiple"]').not('.select2-hidden-accessible').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'Pilih...'
                });
            }
        });
    }

    function initDatePicker() {
        $("#input_date").datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            todayHighlight: true,
        });
    }

    function addRequiredFieldMarkers(root) {
        const scope = root || document;
        const requiredFields = scope.querySelectorAll(
            'input[required], select[required], textarea[required], input[aria-required="true"], select[aria-required="true"], textarea[aria-required="true"]'
        );

        requiredFields.forEach((field) => {
            const tag = (field.tagName || '').toLowerCase();
            const type = (field.getAttribute('type') || '').toLowerCase();

            if (tag === 'input' && ['hidden', 'submit', 'button', 'reset'].includes(type)) {
                return;
            }

            let label = null;
            const fieldId = field.getAttribute('id');

            if (fieldId) {
                label = scope.querySelector(`label[for="${fieldId}"]`) || document.querySelector(
                    `label[for="${fieldId}"]`);
            }

            if (!label) {
                label = field.closest('.mb-3, .row, .form-group, .input-group, .col-lg-8, .col-md-6')
                    ?.querySelector('label');
            }

            if (!label) {
                return;
            }

            const hasMarker = label.querySelector('.required-marker') || /\*/.test(label.textContent || '');
            if (hasMarker) {
                return;
            }

            const marker = document.createElement('span');
            marker.className = 'text-danger required-marker ms-1';
            marker.textContent = '*';
            label.appendChild(marker);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        addRequiredFieldMarkers(document);
        initSelect2(document);

        document.addEventListener('shown.bs.modal', function(event) {
            addRequiredFieldMarkers(event.target);
            initSelect2(event.target);
        });

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType !== 1) {
                        return;
                    }

                    if (node.matches && node.matches(
                            'form, .modal, .offcanvas, .tab-pane')) {
                        addRequiredFieldMarkers(node);
                    } else if (node.querySelector) {
                        const target = node.querySelector(
                            'form, .modal, .offcanvas, .tab-pane');
                        if (target) {
                            addRequiredFieldMarkers(target);
                        }
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    // Update fullscreen icons when fullscreen state changes
    document.addEventListener('fullscreenchange', function() {
        var switcher = document.querySelector('.full-screen-switcher');
        if (!switcher) return;

        var maximize = switcher.querySelector('.maximize');
        var minimize = switcher.querySelector('.minimize');
        var active = !!document.fullscreenElement;

        if (maximize) {
            maximize.style.display = active ? 'none' : '';
        }
        if (minimize) {
            minimize.style.display = active ? '' : 'none';
        }
    });
</script>

<!--! BEGIN: Enhanced DataTables !-->
<script src="{{ asset('assets/js/datatables-enhanced.js') }}"></script>
<!--! END: Enhanced DataTables !-->

<!--! BEGIN: Datepicker Init !-->
<script src="{{ asset('assets/js/datepicker-init.js') }}"></script>
<!--! END: Datepicker Init !-->

<!--! BEGIN: SweetAlert Init !-->
<script src="{{ asset('assets/js/swal-init.js') }}"></script>
<!--! END: SweetAlert Init !-->

<!--! END: Theme Customizer !-->

 <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Timer Countdown Logic
            const headerTimerDisplay = document.getElementById('headerTimerDisplay');
            if (headerTimerDisplay) {
                const startTimeStr = headerTimerDisplay.dataset.start;
                const startTime = new Date(startTimeStr);

                function updateTimer() {
                    const now = new Date();
                    const diff = Math.floor((now - startTime) / 1000);

                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;

                    headerTimerDisplay.textContent = 
                        String(hours).padStart(2, '0') + ':' + 
                        String(minutes).padStart(2, '0') + ':' + 
                        String(seconds).padStart(2, '0');
                }

                setInterval(updateTimer, 1000);
                updateTimer();
            }

            // 2. Stop Timer Logic
            $(document).on('click', '.stop-timer-btn', function() {
                const id = $(this).data('id');
                const btn = $(this);
                
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Stopping...');
                
                $.ajax({
                    url: `/dashboard/timesheets/${id}/stop`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        location.reload();
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to stop timer.', 'error');
                        btn.prop('disabled', false).html('<i class="feather-stop-circle me-1"></i> Stop Timer');
                    }
                });
            });
        });
    </script>
@stack('scripts')
