/**
 * ============================================================
 * RUPIAH INPUT FORMATTER
 * app/resources/js/rupiah.js
 * ============================================================
 *
 * CARA PAKAI:
 * Cukup tambah class "rupiah-input" pada input display,
 * dan data-target="#id_hidden" untuk hidden input penerima.
 *
 * HTML:
 *   <div class="input-group">
 *       <span class="input-group-text">Rp</span>
 *       <input type="text"
 *              class="form-control rupiah-input"
 *              data-target="#harga_satuan"
 *              placeholder="0">
 *       <input type="hidden" id="harga_satuan" name="harga_satuan">
 *   </div>
 *
 * Tanpa hidden input (field biasa, nilai dikirim langsung):
 *   <input type="text"
 *          class="form-control rupiah-input"
 *          name="harga_satuan"
 *          placeholder="0">
 *
 * HASIL:
 *   User ketik  : 38000
 *   Tampil      : 38.000
 *   Dikirim     : 38000  (dari hidden input)
 * ============================================================
 */

const RupiahInput = (function () {

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Hapus semua karakter non-digit (titik, koma, spasi, dll)
     * "38.000" → "38000"
     */
    function toRaw(value) {
        return String(value).replace(/\D/g, '');
    }

    /**
     * Format angka dengan separator ribuan titik
     * "38000" → "38.000"
     * "1500000" → "1.500.000"
     */
    function toFormatted(value) {
        const raw = toRaw(value);
        if (!raw) return '';
        return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    /**
     * Sinkronisasi: format display input & isi hidden input
     */
    function sync(displayInput) {
        const targetSelector = displayInput.getAttribute('data-target');
        const hiddenInput    = targetSelector ? document.querySelector(targetSelector) : null;
        const raw            = toRaw(displayInput.value);

        displayInput.value = toFormatted(raw);

        if (hiddenInput) {
            hiddenInput.value = raw;
        }
    }

    // ── Event binding untuk satu elemen ──────────────────────────────────

    function bindElement(el) {
        // Hindari double-bind
        if (el.dataset.rupiahBound === '1') return;
        el.dataset.rupiahBound = '1';

        // Saat user mengetik
        el.addEventListener('input', function () {
            const raw        = toRaw(this.value);
            const cursorPos  = this.selectionStart;
            const prevLen    = this.value.length;

            this.value = toFormatted(raw);

            // Pertahankan posisi kursor setelah format ulang
            const newLen  = this.value.length;
            const diff     = newLen - prevLen;
            this.setSelectionRange(cursorPos + diff, cursorPos + diff);

            // Sync hidden
            const targetId   = this.getAttribute('data-target');
            const hidden     = targetId ? document.querySelector(targetId) : null;
            if (hidden) hidden.value = raw;
        });

        // Saat focus keluar — pastikan format rapi
        el.addEventListener('blur', function () {
            sync(this);
        });

        // Hanya izinkan digit dan navigasi keyboard
        el.addEventListener('keydown', function (e) {
            const allowedKeys = [
                'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
                'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                'Home', 'End',
            ];
            const isDigit    = /^\d$/.test(e.key);
            const isCtrl     = e.ctrlKey || e.metaKey; // Ctrl+A, Ctrl+C, dll

            if (!isDigit && !allowedKeys.includes(e.key) && !isCtrl) {
                e.preventDefault();
            }
        });

        // Inisialisasi nilai awal (edit mode / old() dari validasi gagal)
        if (el.value) {
            sync(el);
        }
    }

    // ── Init: bind semua .rupiah-input yang ada di DOM ───────────────────

    function init(scope) {
        const root = scope || document;
        root.querySelectorAll('.rupiah-input').forEach(bindElement);
    }

    // ── Observer: otomatis bind elemen baru (untuk modal / Ajax load) ────

    function observe() {
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) return; // bukan element

                    // Cek node itu sendiri
                    if (node.classList && node.classList.contains('rupiah-input')) {
                        bindElement(node);
                    }

                    // Cek child elements di dalam node yang baru ditambahkan
                    node.querySelectorAll && node.querySelectorAll('.rupiah-input').forEach(bindElement);
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree:   true,
        });
    }

    // ── Public API ────────────────────────────────────────────────────────

    return {
        init:       init,
        observe:    observe,
        toRaw:      toRaw,
        toFormatted: toFormatted,

        /**
         * Ambil nilai mentah dari display input
         * RupiahInput.getValue('#display_harga') → "38000"
         */
        getValue: function (selector) {
            const el = document.querySelector(selector);
            return el ? toRaw(el.value) : '';
        },

        /**
         * Set nilai ke display input + sync hidden
         * RupiahInput.setValue('#display_harga', 38000)
         */
        setValue: function (selector, value) {
            const el = document.querySelector(selector);
            if (el) {
                el.value = String(value);
                sync(el);
            }
        },
    };

})();

// ── Auto-init saat DOM siap ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    RupiahInput.init();
    RupiahInput.observe(); // pantau elemen baru (modal, ajax, dll)
});