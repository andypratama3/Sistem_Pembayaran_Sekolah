/**
 * swal-init.js
 * Global initialization for SweetAlert2 inspired by Duralux theme.
 */

"use strict";

// 1. Premium Toast Mixin
window.Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    onOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// 2. Premium Confirmation Dialog
window.ConfirmAction = function(title, text, confirmButtonText, callback) {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-primary m-1',
            cancelButton: 'btn btn-light-brand m-1'
        },
        buttonsStyling: false
    });

    return swalWithBootstrapButtons.fire({
        title: title || 'Apakah Anda yakin?',
        text: text || "Tindakan ini tidak dapat dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmButtonText || 'Ya, Lanjutkan!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.value && typeof callback === 'function') {
            callback();
        }
        return result;
    });
};

// 3. Theme Compatibility: Global click handler for .successAlertMessage
$(document).ready(function() {
    $(document).on('click', '.successAlertMessage', function(e) {
        e.preventDefault();
        window.Toast.fire({
            icon: 'success',
            title: 'Aksi Berhasil Dilakukan!'
        });
    });
});
