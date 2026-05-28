<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  href: { type: String, default: null },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false }
})

const baseClass = 'inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-black uppercase tracking-wider transition-all active:scale-95 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger focus-visible:ring-offset-2'

const variantClass = 'bg-danger hover:bg-red-700 text-white shadow-lg shadow-red-200 hover:shadow-red-300 hover:-translate-y-0.5'

const classes = computed(() => `${baseClass} ${variantClass}`)
</script>

<template>
  <Link v-if="href" :href="href" :class="classes">
    <slot />
  </Link>
  <button v-else :type="type" :class="classes" :disabled="disabled">
    <slot />
  </button>
</template>
