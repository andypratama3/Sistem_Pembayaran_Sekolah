/**
 * datepicker-init.js
 * Global initialization for vanilla-js-datepicker found in Duralux theme.
 */

"use strict";

$(document).ready(function() {
    // ── 1. Initialize by Data Attribute ──────────────────────────────────────
    // Usage: <input type="text" data-datepicker="true">
    function initGlobalDatepickers() {
        document.querySelectorAll('[data-datepicker="true"]').forEach(function(el) {
            if (!el.datepicker) {
                new Datepicker(el, {
                    autohide: true,
                    format: 'yyyy-mm-dd',
                    clearBtn: true,
                    todayBtn: true,
                    todayHighlight: true,
                    allowOneSidedRange: true,
                    orientation: 'bottom auto'
                });
            }
        });
    }

    // ── 2. Specialized Initialization by ID (for compatibility with theme examples) ──
    function initSpecificDatepickers() {
        const ids = ['startDate', 'dueDate', 'datepicker'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.datepicker) {
                new Datepicker(el, {
                    autohide: true,
                    format: 'yyyy-mm-dd',
                    clearBtn: true,
                    todayBtn: true,
                    todayHighlight: true,
                    allowOneSidedRange: true,
                    orientation: 'bottom auto'
                });
            }
        });
    }

    // Run on load
    initGlobalDatepickers();
    initSpecificDatepickers();

    // ── 3. Re-initialize for dynamic content (Modals, AJAX) ─────────────────
    $(document).on('shown.bs.modal', function() {
        initGlobalDatepickers();
    });

    // Expose globally if needed
    window.initDatepickers = function() {
        initGlobalDatepickers();
        initSpecificDatepickers();
    };
});
