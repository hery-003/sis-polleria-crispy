<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  href: { type: String, required: true },
  active: { type: Boolean, default: false },
})
</script>

<template>
  <Link
    :href="href"
    class="block px-4 py-2 text-sm font-semibold transition-colors duration-150"
    :class="active ? 'text-primary bg-orange-50' : 'text-gray-700 hover:text-primary hover:bg-gray-50'"
  >
    <slot />
  </Link>
</template>
