<script setup>
import { toastState } from '@/plugins/toast'
import { watch, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const icons = {
    success: '✓',
    error: '✕',
    warning: '⚠',
    info: 'ℹ'
}

const colors = {
    success: 'bg-green-600',
    error: 'bg-danger',
    warning: 'bg-yellow-500',
    info: 'bg-blue-600'
}

const page = usePage()
const flashNotifications = ref([])

watch(() => page.props.flash, (flash) => {
    if (flash.success) {
        flashNotifications.value.push({
            id: Date.now(),
            type: 'success',
            message: flash.success
        })
        setTimeout(() => {
            flashNotifications.value = flashNotifications.value.filter(n => n.type !== 'success')
        }, 4000)
    }
    if (flash.error) {
        flashNotifications.value.push({
            id: Date.now() + 1,
            type: 'error',
            message: flash.error
        })
        setTimeout(() => {
            flashNotifications.value = flashNotifications.value.filter(n => n.type !== 'error')
        }, 6000)
    }
}, { deep: true })

const allNotifications = ref([])

watch(() => toastState.notifications, (notifs) => {
    allNotifications.value = [...notifs, ...flashNotifications.value]
}, { deep: true, immediate: true })

watch(flashNotifications, (notifs) => {
    allNotifications.value = [...toastState.notifications, ...notifs]
}, { deep: true, immediate: true })
</script>

<template>
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
        <TransitionGroup name="toast">
            <div v-for="n in allNotifications" :key="n.id"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white shadow-xl min-w-[300px]"
                :class="colors[n.type]">
                <span class="text-xl">{{ icons[n.type] }}</span>
                <span class="font-medium">{{ n.message }}</span>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from { opacity: 0; transform: translateX(100%); }
.toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>
