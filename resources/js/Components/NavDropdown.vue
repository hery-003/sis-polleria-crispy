<script setup>
import { ref } from 'vue'

defineProps({
  label: { type: String, required: true },
  active: { type: Boolean, default: false },
})

const open = ref(false)
let timeout = null

const show = () => {
  clearTimeout(timeout)
  open.value = true
}

const hide = () => {
  timeout = setTimeout(() => { open.value = false }, 150)
}
</script>

<template>
  <div class="relative" @mouseenter="show" @mouseleave="hide">
    <button
      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold leading-5 whitespace-nowrap transition duration-150 ease-in-out"
      :class="active
        ? 'bg-white text-primary shadow-md shadow-black/10'
        : 'text-white/85 hover:text-white hover:bg-white/10'"
    >
      {{ label }}
      <svg class="w-3 h-3 mt-0.5" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <Transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div v-if="open" class="absolute top-full left-0 mt-1 min-w-[160px] bg-white rounded-xl shadow-xl ring-1 ring-black/5 py-2 z-50">
        <slot />
      </div>
    </Transition>
  </div>
</template>
