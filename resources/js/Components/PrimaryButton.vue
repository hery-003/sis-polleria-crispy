<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  href: { type: String, default: null },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false }
})

const baseClass = 'inline-flex items-center justify-center rounded-2xl px-6 py-3.5 text-sm font-black uppercase tracking-wider transition-all active:scale-95 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 overflow-hidden relative group'

const variantClass = 'bg-gradient-to-br from-primary to-orange-600 text-white shadow-xl shadow-orange-500/20 hover:shadow-orange-500/40 hover:-translate-y-1'

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
