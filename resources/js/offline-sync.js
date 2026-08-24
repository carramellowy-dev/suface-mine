const DB_NAME = 'surface-mine';
const STORE_NAME = 'outbox';
const DB_VERSION = 1;

const dbPromise = new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = () => {
        const db = request.result;
        if (!db.objectStoreNames.contains(STORE_NAME)) {
            db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
        }
    };

    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
});

async function withStore(mode, handler) {
    const db = await dbPromise;
    return await new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, mode);
        const store = tx.objectStore(STORE_NAME);
        const result = handler(store);
        tx.oncomplete = () => resolve(result);
        tx.onerror = () => reject(tx.error);
        tx.onabort = () => reject(tx.error);
    });
}

async function getCount() {
    return await withStore('readonly', store => store.count());
}

async function setBadge() {
    const count = await getCount();
    window.outboxCount = count;
    window.dispatchEvent(new CustomEvent('outbox:changed', { detail: count }));
}

async function toBase64(file) {
    return await new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result.split(',')[1]);
        reader.onerror = () => reject(reader.error);
        reader.readAsDataURL(file);
    });
}

async function serializeForm(form) {
    const formData = new FormData(form);
    const files = [];

    for (const [key, value] of formData.entries()) {
        if (value instanceof File && value.size > 0) {
            files.push({ field: key, name: value.name, type: value.type, base64: await toBase64(value) });
            formData.delete(key);
        }
    }

    return { payload: Object.fromEntries(formData.entries()), files };
}

async function saveOutbox(item) {
    await withStore('readwrite', store => store.add(item));
    await setBadge();
}

async function freshCsrf() {
    const response = await fetch('/csrf-token', { headers: { Accept: 'application/json' } });
    const data = await response.json();
    return data.token;
}

async function replayOutbox() {
    const items = await withStore('readonly', store => new Promise((resolve, reject) => {
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result || []);
        request.onerror = () => reject(request.error);
    }));

    if (!items.length) {
        await setBadge();
        return;
    }

    for (const item of items) {
        try {
            const formData = new FormData();
            const token = await freshCsrf();
            formData.append('_token', token);

            for (const [key, value] of Object.entries(item.payload || {})) {
                formData.append(key, value);
            }

            for (const file of item.files || []) {
                const blob = await (await fetch(`data:${file.type};base64,${file.base64}`)).blob();
                formData.append(file.field, new File([blob], file.name, { type: file.type }));
            }

            const response = await fetch(item.url, {
                method: item.method,
                body: formData,
                headers: { 'X-Offline-Replay': '1', 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            });

            if (response.ok) {
                await withStore('readwrite', store => store.delete(item.id));
                showToast('Data offline berhasil tersinkronisasi', 'online');
            } else if (response.status === 422 || response.status === 409) {
                // Validation/duplicate error - remove from outbox
                await withStore('readwrite', store => store.delete(item.id));
                showToast('Data tidak dapat disinkronisasi (duplikat/validasi)', 'offline');
            } else {
                // Server error - will retry on next sync
                break;
            }
        } catch {
            break;
        }
    }

    await setBadge();
}

function showToast(message, type) {
    const existing = document.querySelector('.offline-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'offline-toast';
    toast.innerHTML = `<span style="font-size:20px;font-family:'Material Symbols Outlined'">${type === 'offline' ? 'cloud_off' : 'cloud_done'}</span><span>${message}</span>`;
    Object.assign(toast.style, {
        position: 'fixed', bottom: '24px', right: '24px', zIndex: '9999',
        background: type === 'offline' ? '#1d2023' : '#1b5e20',
        color: '#e1e2e6', padding: '14px 20px', borderRadius: '12px',
        fontSize: '14px', fontWeight: '700', display: 'flex', alignItems: 'center',
        gap: '12px', boxShadow: '0 8px 30px rgba(0,0,0,0.5)',
        border: type === 'offline' ? '1px solid #5d3f3b' : '1px solid #2e7d32',
        fontFamily: '"Plus Jakarta Sans","Inter",sans-serif',
        transform: 'translateY(20px)', opacity: '0',
        transition: 'transform 0.3s ease, opacity 0.3s ease'
    });
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    });

    setTimeout(() => {
        toast.style.transform = 'translateY(20px)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

async function handleFormSubmit(event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    event.preventDefault();
    const payload = await serializeForm(form);

    if (navigator.onLine) {
        try {
            const token = await freshCsrf();
            const formData = new FormData(form);
            formData.append('_token', token);
            
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            });

            if (response.ok) {
                showToast('Data berhasil dikirim', 'online');
                form.reset();
                await setBadge();
                return;
            }
        } catch {}
    }

    await saveOutbox({ url: form.action, method: form.method || 'POST', payload: payload.payload, files: payload.files, created_at: new Date().toISOString() });
    showToast('Tersimpan offline', 'offline');
    form.reset();
    await setBadge();
    navigator.serviceWorker?.ready.then(reg => reg.sync?.register(form.dataset.syncTag || 'absensi-sync')).catch(() => {});
}

window.addEventListener('online', replayOutbox);
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-offline-form]').forEach(form => form.addEventListener('submit', handleFormSubmit));
    replayOutbox();
    setBadge();
});
