import { createApp, h, reactive } from 'vue'

const state = reactive({
    notifications: []
})

let toastContainer = null;

function ensureContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
        document.body.appendChild(toastContainer);
    }
    return toastContainer;
}

function renderToast(id, type, message, duration) {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6',
        warning: '#f59e0b',
    };
    const container = ensureContainer();
    const el = document.createElement('div');
    el.id = `toast-${id}`;
    el.style.cssText = `background:${colors[type] || '#333'};color:#fff;padding:12px 20px;border-radius:12px;font-weight:700;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,0.15);transition:all 0.3s;animation:slideIn 0.3s ease;max-width:400px;word-break:break-word;font-family:sans-serif;`;
    el.textContent = message;
    container.appendChild(el);
    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateX(100%)';
        setTimeout(() => el.remove(), 300);
    }, duration);
}

function addNotification({ type = 'success', message = '', duration = 3000 }) {
    const id = Date.now() + Math.random()
    state.notifications.push({ id, type, message })
    renderToast(id, type, message, duration)
    setTimeout(() => {
        state.notifications = state.notifications.filter(n => n.id !== id)
    }, duration)
}

export function useToast() {
    return {
        success: (msg) => addNotification({ type: 'success', message: msg }),
        error: (msg) => addNotification({ type: 'error', message: msg }),
        info: (msg) => addNotification({ type: 'info', message: msg }),
        warning: (msg) => addNotification({ type: 'warning', message: msg }),
    }
}

export function toastPlugin(app) {
    app.config.globalProperties.$toast = {
        success: (msg) => addNotification({ type: 'success', message: msg }),
        error: (msg) => addNotification({ type: 'error', message: msg }),
        info: (msg) => addNotification({ type: 'info', message: msg }),
        warning: (msg) => addNotification({ type: 'warning', message: msg }),
    }
}

export { state as toastState }
