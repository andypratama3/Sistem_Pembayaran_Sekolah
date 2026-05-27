<script>
    $(document).ready(function() {
        // Form Loading State
        $('form').on('submit', function() {
            const btn = $(this).find('button[type="submit"]');
            if (btn.length) {
                btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...').prop('disabled', true);
            }
        });

        // Add Classroom AJAX
        $('#addClassroomForm').on('submit', function(e) {
            e.preventDefault();
            // Add your AJAX submission logic here
        });

        // --- STUDENT IMPORT DAPODIK ---
        let currentBatchId = null;
        const userId = "{{ auth()->id() }}";

        $(document).on('submit', '#importStudentForm', function(e) {
            e.preventDefault();
            console.log('[Import] Form submit triggered');
            
            const formData = new FormData(this);
            const btn = $('#importSubmitBtn');
            
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah...');
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('[Import] Success:', response);
                    const progressUrl = response.data.progress_url;
                    
                    // Redirect to progress page
                    window.location.href = progressUrl;
                },
                error: function(xhr) {
                    let message = 'Gagal mengunggah file.';
                    if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
                    Swal.fire({ icon: 'error', title: 'Kesalahan', text: message });
                    btn.prop('disabled', false).text('Mulai Impor');
                }
            });
        });

        $('#importStopBtn').on('click', function() {
            if (!currentBatchId) return;
            
            Swal.fire({
                title: 'Batalkan Impor?',
                text: "Proses yang sudah berjalan tidak dapat dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hentikan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('dashboard/students/import') }}/" + currentBatchId,
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function() {
                            Swal.fire('Dibatalkan', 'Proses impor telah dihentikan.', 'info').then(() => {
                                location.reload();
                            });
                        }
                    });
                }
            });
        });

        // Reset modal on close
        $('#importStudentModal').on('hidden.bs.modal', function () {
            $('#importInitialState').show();
            $('#importProgressState').hide();
            $('#importSuccessState').hide();
            $('#importInitialFooter').show();
            $('#importProgressFooter').hide();
            $('#importSubmitBtn').show().prop('disabled', false).html('<i class="feather-upload me-1"></i>Mulai Impor');
            $('#importCancelBtn').show().text('Batal');
            $('#importStopBtn').hide();
            $('#importProgressBar').css('width', '0%').removeClass('bg-success').addClass('bg-primary progress-bar-striped progress-bar-animated');
            $('#importPercentage').text('0%');
            $('#importCountSuccess').text('0');
            $('#importCountFailed').text('0');
            $('#importCountTotal').text('0');
            $('#importSubStatus').text('Mohon tunggu, proses import sedang berjalan');
            $('#importErrorItems').empty();
            $('#importErrorList').hide();
            $('#importStatusText').text('Memproses data siswa...');
            
            if (currentBatchId && window.Echo) {
                window.Echo.leave(`import.students.${userId}`);
            }
            currentBatchId = null;
        });
    });
</script>
