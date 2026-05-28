import { reactive } from 'vue'

const state = reactive({
    notifications: []
})

function addNotification({ type = 'success', message = '', duration = 3000 }) {
    const id = Date.now() + Math.random()
    state.notifications.push({ id, type, message })
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
