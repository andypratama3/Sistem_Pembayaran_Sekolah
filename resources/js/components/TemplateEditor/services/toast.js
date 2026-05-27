/**
 * toast.js — SweetAlert2-backed toast helpers.
 *
 * Adds a thin layer on top of SweetAlert2:
 *  - Duplicate suppression: identical (icon+message) toasts within a short
 *    window are coalesced so rapid identical errors don't spam the screen.
 *  - Bounded queue: at most MAX_QUEUE pending toasts; older ones are dropped.
 *  - Listener cleanup: timer pause/resume listeners are removed in `didClose`
 *    so we don't rely on GC to clean them up.
 */

import Swal from 'sweetalert2';

const DEDUPE_WINDOW_MS = 1500;
const MAX_QUEUE = 5;

const lastFired = new Map(); // key -> timestamp
const pending = []; // queue of pending fire payloads

let activeCount = 0;

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    customClass: {
        container: 'swal-above-editor',
    },
    didOpen: (toast) => {
        const onEnter = () => Swal.stopTimer();
        const onLeave = () => Swal.resumeTimer();
        toast.addEventListener('mouseenter', onEnter);
        toast.addEventListener('mouseleave', onLeave);
        // Cache so didClose can clean up.
        toast.__toastListeners = { onEnter, onLeave };
    },
    didClose: (toast) => {
        const listeners = toast?.__toastListeners;
        if (listeners) {
            toast.removeEventListener('mouseenter', listeners.onEnter);
            toast.removeEventListener('mouseleave', listeners.onLeave);
            delete toast.__toastListeners;
        }
    },
});

const dedupeKey = (icon, message) => `${icon}::${message}`;

const drainQueue = () => {
    if (activeCount > 0) return; // SweetAlert2 toasts queue internally
    const next = pending.shift();
    if (!next) return;
    activeCount += 1;
    Toast.fire(next).finally(() => {
        activeCount -= 1;
        drainQueue();
    });
};

const enqueue = (icon, title) => {
    if (typeof title !== 'string' || title.length === 0) return;

    const key = dedupeKey(icon, title);
    const now = Date.now();
    const last = lastFired.get(key);
    if (last && now - last < DEDUPE_WINDOW_MS) {
        // Suppress duplicate within the dedupe window.
        return;
    }
    lastFired.set(key, now);

    // Trim the dedupe map opportunistically so it can't grow without bound.
    if (lastFired.size > 200) {
        for (const [k, t] of lastFired) {
            if (now - t > DEDUPE_WINDOW_MS * 10) lastFired.delete(k);
        }
    }

    if (pending.length >= MAX_QUEUE) {
        // Drop the oldest pending toast to make room for the new one.
        pending.shift();
    }
    pending.push({ icon, title });
    drainQueue();
};

export const showSuccess = (message) => enqueue('success', message);
export const showError = (message) => enqueue('error', message);
export const showWarning = (message) => enqueue('warning', message);

export const showConfirm = (title, text, confirmText = 'Ya') => {
    return Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal',
        customClass: {
            container: 'swal-above-editor',
        },
    });
};

export default Toast;
