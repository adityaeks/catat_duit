/**
 * Catat Duit - PWA Daily Reminder System (Jam 17:00)
 */

const STORAGE_KEYS = {
    ENABLED: 'catatduit_reminder_enabled',
    TIME: 'catatduit_reminder_time', // default '17:00'
    LAST_DATE: 'catatduit_last_reminder_date',
};

let reminderTimer = null;

export function isNotificationSupported() {
    return 'Notification' in window && 'serviceWorker' in navigator;
}

export function getReminderSettings() {
    const enabled = localStorage.getItem(STORAGE_KEYS.ENABLED) === 'true';
    const time = localStorage.getItem(STORAGE_KEYS.TIME) || '17:00';
    const [hour, minute] = time.split(':').map(Number);
    return {
        enabled,
        time,
        hour: isNaN(hour) ? 17 : hour,
        minute: isNaN(minute) ? 0 : minute,
        permission: 'Notification' in window ? Notification.permission : 'unsupported'
    };
}

export function saveReminderSettings(enabled, time = '17:00') {
    localStorage.setItem(STORAGE_KEYS.ENABLED, enabled ? 'true' : 'false');
    localStorage.setItem(STORAGE_KEYS.TIME, time);

    if (enabled) {
        scheduleNextReminder();
    } else if (reminderTimer) {
        clearTimeout(reminderTimer);
        reminderTimer = null;
    }

    updateReminderUI();
}

export async function requestPermission() {
    if (!isNotificationSupported()) {
        throw new Error('Browser atau perangkat ini tidak mendukung notifikasi web.');
    }

    const permission = await Notification.requestPermission();
    updateReminderUI();
    return permission;
}

export async function triggerNotification(title, options = {}) {
    const defaultOptions = {
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-192x192.png',
        vibrate: [200, 100, 200],
        data: { url: '/' },
        ...options
    };

    // 1. Try via Service Worker registration (works when installed PWA / background)
    if ('serviceWorker' in navigator) {
        try {
            const reg = await navigator.serviceWorker.ready;
            if (reg && reg.showNotification) {
                await reg.showNotification(title, defaultOptions);
                return true;
            }
        } catch (e) {
            console.warn('[Catat Duit] Service worker showNotification error:', e);
        }
    }

    // 2. Fallback to standard Notification API
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, defaultOptions);
        return true;
    }

    return false;
}

export async function sendTestNotification() {
    if (!isNotificationSupported()) {
        alert('Perangkat Anda tidak mendukung notifikasi web.');
        return;
    }

    if (Notification.permission !== 'granted') {
        const perm = await requestPermission();
        if (perm !== 'granted') {
            alert('Izin notifikasi belum diberikan. Silakan izinkan di browser HP Anda.');
            return;
        }
    }

    await triggerNotification('Catat Duit 💰', {
        body: 'Tes berhasil! Ini contoh pengingat jam 5 sore untuk mencatat pengeluaran Anda.',
        tag: 'catatduit-test-' + Date.now(),
        renotify: true
    });
}

export function checkAndTriggerReminder() {
    const settings = getReminderSettings();
    if (!settings.enabled || Notification.permission !== 'granted') {
        return;
    }

    const now = new Date();
    const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    const lastDate = localStorage.getItem(STORAGE_KEYS.LAST_DATE);

    // Trigger if current hour >= target hour and not yet triggered today
    if (now.getHours() >= settings.hour && lastDate !== todayStr) {
        localStorage.setItem(STORAGE_KEYS.LAST_DATE, todayStr);
        triggerNotification('Catat Duit 💰', {
            body: 'Sudah jam 5 sore nih! Jangan lupa catat pengeluaran hari ini ya.',
            tag: 'catatduit-reminder-' + todayStr,
            renotify: true
        });
    }

    scheduleNextReminder();
}

export function scheduleNextReminder() {
    if (reminderTimer) {
        clearTimeout(reminderTimer);
        reminderTimer = null;
    }

    const settings = getReminderSettings();
    if (!settings.enabled || Notification.permission !== 'granted') {
        return;
    }

    const now = new Date();
    const target = new Date();
    target.setHours(settings.hour, settings.minute, 0, 0);

    // If past target time today, schedule for tomorrow
    if (now.getTime() >= target.getTime()) {
        target.setDate(target.getDate() + 1);
    }

    const delayMs = target.getTime() - now.getTime();
    console.log(`[Catat Duit] Reminder scheduled in ${(delayMs / 1000 / 60).toFixed(1)} mins at ${target.toLocaleTimeString()}`);

    if (delayMs > 0 && delayMs < 2147483647) {
        reminderTimer = setTimeout(() => {
            checkAndTriggerReminder();
        }, delayMs);
    }
}

export function updateReminderUI() {
    const settings = getReminderSettings();
    const bellBadge = document.getElementById('reminderActiveBadge');
    if (bellBadge) {
        if (settings.enabled && settings.permission === 'granted') {
            bellBadge.classList.remove('hidden');
        } else {
            bellBadge.classList.add('hidden');
        }
    }

    const toggle = document.getElementById('reminderToggleInput');
    if (toggle) {
        toggle.checked = settings.enabled;
    }

    const statusText = document.getElementById('reminderStatusText');
    if (statusText) {
        if (settings.permission === 'granted') {
            statusText.innerHTML = settings.enabled 
                ? '<span class="text-emerald-400 font-semibold flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif setiap 17:00 WIB</span>'
                : '<span class="text-slate-400">Notifikasi diizinkan, fitur dinonaktifkan</span>';
        } else if (settings.permission === 'denied') {
            statusText.innerHTML = '<span class="text-rose-400 font-medium">Izin notifikasi diblokir di browser</span>';
        } else {
            statusText.innerHTML = '<span class="text-amber-400 font-medium">Perlu izin browser</span>';
        }
    }
}

// Global functions attached to window for inline onclicks in Blade
window.openReminderModal = function () {
    const modal = document.getElementById('reminderModal');
    if (modal) {
        modal.classList.remove('hidden');
        updateReminderUI();
    }
};

window.closeReminderModal = function () {
    const modal = document.getElementById('reminderModal');
    if (modal) {
        modal.classList.add('hidden');
    }
};

window.toggleDailyReminder = async function () {
    const settings = getReminderSettings();
    const willEnable = !settings.enabled;

    if (willEnable && Notification.permission !== 'granted') {
        const perm = await requestPermission();
        if (perm !== 'granted') {
            const toggle = document.getElementById('reminderToggleInput');
            if (toggle) toggle.checked = false;
            if (window.Swal) {
                window.Swal.fire({
                    title: 'Izin Notifikasi Dibutuhkan',
                    text: 'Untuk menerima pengingat jam 5 sore, mohon izinkan notifikasi di browser atau layar utama HP Anda.',
                    icon: 'info',
                    confirmButtonColor: '#059669',
                    confirmButtonText: 'Mengerti'
                });
            }
            return;
        }
    }

    saveReminderSettings(willEnable, '17:00');

    if (willEnable) {
        // Send a quick confirmation notification
        triggerNotification('Pengingat Aktif ⏰', {
            body: 'Pengingat jam 5 sore berhasil diaktifkan! Catat Duit akan mengingatkan Anda setiap sore.',
            tag: 'catatduit-enabled'
        });
    }
};

window.testReminderNotification = function () {
    sendTestNotification();
};

export function initReminder() {
    updateReminderUI();
    checkAndTriggerReminder();

    // Check periodically every 15 minutes when app is active
    setInterval(() => {
        checkAndTriggerReminder();
    }, 15 * 60 * 1000);
}
