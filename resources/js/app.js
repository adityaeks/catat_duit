import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

// Global Custom Delete Confirmation
window.confirmDelete = function (formOrCallback, title = 'Hapus Data?', text = 'Data yang dihapus tidak dapat dikembalikan!') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        background: '#111726',
        color: '#F8FAFC',
        showCancelButton: true,
        confirmButtonColor: '#e11d48', // rose-600
        cancelButtonColor: '#334155', // slate-700
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-3xl shadow-2xl border border-white/10 !bg-[#111726] !text-white',
            title: 'text-lg font-bold text-white',
            htmlContainer: 'text-xs text-slate-400',
            confirmButton: 'px-5 py-2.5 rounded-2xl font-bold text-xs shadow-md',
            cancelButton: 'px-5 py-2.5 rounded-2xl font-bold text-xs'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof formOrCallback === 'function') {
                formOrCallback();
            } else if (formOrCallback && typeof formOrCallback.submit === 'function') {
                formOrCallback.submit();
            }
        }
    });
};

// Global Custom Action Confirmation (e.g. Logout)
window.confirmAction = function (formOrCallback, title = 'Konfirmasi', text = 'Apakah Anda yakin ingin melanjutkan?', confirmText = 'Ya, Lanjutkan') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        background: '#111726',
        color: '#F8FAFC',
        showCancelButton: true,
        confirmButtonColor: '#059669', // emerald-600
        cancelButtonColor: '#334155', // slate-700
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-3xl shadow-2xl border border-white/10 !bg-[#111726] !text-white',
            title: 'text-lg font-bold text-white',
            htmlContainer: 'text-xs text-slate-400',
            confirmButton: 'px-5 py-2.5 rounded-2xl font-bold text-xs shadow-md',
            cancelButton: 'px-5 py-2.5 rounded-2xl font-bold text-xs'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof formOrCallback === 'function') {
                formOrCallback();
            } else if (formOrCallback && typeof formOrCallback.submit === 'function') {
                formOrCallback.submit();
            }
        }
    });
};

// Initialize PWA Daily Reminder System
import { initReminder } from './reminder.js';

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReminder);
} else {
    initReminder();
}
