/**
 * Catat Duit - PWA & Web Push Daily Reminder System (Jam 17:00)
 */

const STORAGE_KEYS = {
    ENABLED: 'catatduit_reminder_enabled',
    TIME: 'catatduit_reminder_time', // default '17:00'
    LAST_DATE: 'catatduit_last_reminder_date',
};

let reminderTimer = null;

/**
 * Convert URL-safe Base64 string to Uint8Array for VAPID applicationServerKey
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function getCsrfToken() {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    return tokenMeta ? tokenMeta.getAttribute('content') : '';
}

export function isNotificationSupported() {
    return 'Notification' in window && 'serviceWorker' in navigator;
}

export function isPushManagerSupported() {
    return isNotificationSupported() && 'PushManager' in window;
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

/**
 * Fetch VAPID public key from backend
 */
export async function getVapidPublicKey() {
    try {
        const res = await fetch('/push/vapid-public-key', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!res.ok) return null;
        const data = await res.json();
        return data.publicKey || null;
    } catch (e) {
        console.warn('[Catat Duit] Failed to fetch VAPID key:', e);
        return null;
    }
}

/**
 * Register this device with the server for background Web Push
 */
export async function subscribeDeviceToPush() {
    if (!isPushManagerSupported()) {
        console.warn('[Catat Duit] PushManager is not supported in this browser.');
        return null;
    }

    try {
        const registration = await navigator.serviceWorker.ready;
        if (!registration.pushManager) {
            return null;
        }

        const vapidPublicKey = await getVapidPublicKey();
        if (!vapidPublicKey) {
            console.warn('[Catat Duit] VAPID public key not configured on server.');
            return null;
        }

        const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

        // Check if device already has a subscription
        let subscription = await registration.pushManager.getSubscription();
        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: convertedVapidKey
            });
        }

        // Send subscription object to Laravel backend
        const res = await fetch('/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify(subscription.toJSON())
        });

        if (res.ok) {
            console.log('[Catat Duit] Device registered for Server Web Push!');
            return subscription;
        }
    } catch (e) {
        console.warn('[Catat Duit] Server Web Push registration error:', e);
    }

    return null;
}

/**
 * Unsubscribe device from background Web Push
 */
export async function unsubscribeDeviceFromPush() {
    if (!isPushManagerSupported()) {
        return;
    }

    try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (subscription) {
            await fetch('/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ endpoint: subscription.endpoint })
            });

            await subscription.unsubscribe();
            console.log('[Catat Duit] Device unsubscribed from Web Push.');
        }
    } catch (e) {
        console.warn('[Catat Duit] Web Push unsubscribe error:', e);
    }
}

/**
 * Local fallback notification trigger
 */
export async function triggerNotification(title, options = {}) {
    const defaultOptions = {
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-192x192.png',
        vibrate: [200, 100, 200],
        data: { url: '/' },
        ...options
    };

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

    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, defaultOptions);
        return true;
    }

    return false;
}

/**
 * Send test notification (tries Server Web Push first, falls back to local Service Worker)
 */
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

    const testBtn = document.getElementById('testReminderBtn');
    const originalText = testBtn ? testBtn.innerHTML : '';
    if (testBtn) {
        testBtn.disabled = true;
        testBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-emerald-400 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Mengirim notifikasi dari server...</span>
        `;
    }

    try {
        // Ensure device is subscribed on server first
        const subscription = await subscribeDeviceToPush();

        if (subscription) {
            const res = await fetch('/push/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ endpoint: subscription.endpoint })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                if (testBtn) {
                    testBtn.disabled = false;
                    testBtn.innerHTML = `<span>✅ Notifikasi Terkirim dari Server!</span>`;
                    setTimeout(() => { testBtn.innerHTML = originalText; }, 2500);
                }
                return;
            }
        }
    } catch (e) {
        console.warn('[Catat Duit] Server test push failed, using local trigger:', e);
    }

    // Fallback if server push is unavailable or running on non-HTTPS
    await triggerNotification('Catat Duit 💰', {
        body: 'Tes berhasil! Ini contoh pengingat jam 5 sore untuk mencatat pengeluaran Anda.',
        tag: 'catatduit-test-' + Date.now(),
        renotify: true
    });

    if (testBtn) {
        testBtn.disabled = false;
        testBtn.innerHTML = originalText;
    }
}

/**
 * In-tab backup check (runs if web is currently open around 17:00)
 */
export function checkAndTriggerReminder() {
    const settings = getReminderSettings();
    if (!settings.enabled || Notification.permission !== 'granted') {
        return;
    }

    const now = new Date();
    const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    const lastDate = localStorage.getItem(STORAGE_KEYS.LAST_DATE);

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

    if (now.getTime() >= target.getTime()) {
        target.setDate(target.getDate() + 1);
    }

    const delayMs = target.getTime() - now.getTime();
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
                ? '<span class="text-emerald-400 font-semibold flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif jam 17:00 (Otomatis Server & HP)</span>'
                : '<span class="text-slate-400">Notifikasi diizinkan, fitur dinonaktifkan</span>';
        } else if (settings.permission === 'denied') {
            statusText.innerHTML = '<span class="text-rose-400 font-medium">Izin notifikasi diblokir di browser</span>';
        } else {
            statusText.innerHTML = '<span class="text-amber-400 font-medium">Perlu izin browser</span>';
        }
    }
}

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
                    background: '#111726',
                    color: '#F8FAFC',
                    buttonsStyling: false,
                    confirmButtonText: 'Mengerti',
                    customClass: {
                        popup: '!bg-[#111726] !border !border-white/10 !rounded-3xl !p-6 !shadow-2xl !max-w-sm',
                        confirmButton: 'w-full py-2.5 px-4 bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-slate-950 font-bold rounded-xl text-xs transition-all cursor-pointer'
                    },
                    html: `
                        <div class="flex flex-col items-center text-center">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-3 shadow-lg shadow-emerald-500/10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                            </div>
                            <h3 class="text-base font-extrabold text-white tracking-tight">Izin Notifikasi Dibutuhkan</h3>
                            <p class="text-xs text-slate-400 mt-2 leading-relaxed">Untuk menerima pengingat harian jam 5 sore, mohon izinkan notifikasi di browser atau layar utama HP Anda.</p>
                        </div>
                    `
                });
            }
            return;
        }
    }

    if (willEnable) {
        // Register device subscription to server
        await subscribeDeviceToPush();
        saveReminderSettings(true, '17:00');

        triggerNotification('Pengingat Aktif ⏰', {
            body: 'Pengingat jam 5 sore berhasil diaktifkan! Anda akan menerima notifikasi setiap sore langsung di HP.',
            tag: 'catatduit-enabled'
        });
    } else {
        // Unregister device subscription from server
        await unsubscribeDeviceFromPush();
        saveReminderSettings(false, '17:00');
    }
};

window.testReminderNotification = function () {
    sendTestNotification();
};

export function initReminder() {
    updateReminderUI();
    checkAndTriggerReminder();

    // If enabled, refresh device subscription quietly in background
    const settings = getReminderSettings();
    if (settings.enabled && Notification.permission === 'granted') {
        subscribeDeviceToPush();
    }

    // Check periodically every 15 minutes when app is active
    setInterval(() => {
        checkAndTriggerReminder();
    }, 15 * 60 * 1000);
}
