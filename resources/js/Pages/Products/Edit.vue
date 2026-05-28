<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
  product: Object,
  categories: Array
})

const imagePreview = ref(null)
const imageRemoved = ref(false)

const form = useForm({
  _method: 'PUT',
  name: props.product.name || '',
  description: props.product.description || '',
  category_id: props.product.category_id != null ? String(props.product.category_id) : '',
  is_active: props.product.is_active ?? true,
  image: null,
  remove_image: false,
  variants: props.product.variants?.length
    ? props.product.variants.map(v => ({ id: v.id, name: v.name, price: String(v.price) }))
    : []
})

const handleImage = (e) => {
  const file = e.target.files[0]
  if (!file) return
  form.image = file
  form.remove_image = false
  imageRemoved.value = false
  imagePreview.value = URL.createObjectURL(file)
}

const clearImage = () => {
  form.image = null
  form.remove_image = true
  imagePreview.value = null
  imageRemoved.value = true
}

const addVariant = () => {
  form.variants.push({ name: '', price: '' })
}

const removeVariant = (index) => {
  form.variants.splice(index, 1)
}

const submit = () => {
  form.post(route('products.update', props.product.id), {
    preserveScroll: true,
    onSuccess: () => {
      if (!imageRemoved.value) imagePreview.value = null
    }
  })
}
</script>

<template>
  <AuthenticatedLayout title="Editar Producto">
    <template #header>
      <div class="flex items-center gap-4">
        <Link :href="route('products.index')"
          class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow hover:shadow-md transition-all">
          <svg class="w-5 h-5 text-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </Link>
        <div>
          <h2 class="text-2xl font-black text-text tracking-tight uppercase">Editar Producto</h2>
          <p class="text-xs text-gray-400 font-bold">Modifica los datos del producto</p>
        </div>
      </div>
    </template>

    <div>
      <div class="max-w-6xl mx-auto">
        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- Sidebar: Imagen + Info -->
          <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
              <div class="p-4 border-b bg-gray-50">
                <h3 class="text-sm font-black text-text uppercase">Imagen del Producto</h3>
              </div>
              <div class="p-4">
                <div class="relative aspect-square bg-gray-100 rounded-2xl overflow-hidden border-2 border-dashed border-gray-300 hover:border-primary transition-colors group">
                  <template v-if="imagePreview">
                    <img :src="imagePreview" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                      <span class="text-white font-bold text-sm">Click para cambiar</span>
                    </div>
                  </template>
                  <template v-else-if="product.image && !imageRemoved">
                    <img :src="'/storage/' + product.image" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                      <span class="text-white font-bold text-sm">Click para cambiar</span>
                    </div>
                  </template>
                  <template v-else>
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                      <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <span class="text-xs font-bold">Click para subir</span>
                    </div>
                  </template>
                  <input type="file" accept="image/*" @change="handleImage"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                </div>

                <button v-if="product.image || imagePreview" type="button" @click="clearImage"
                  class="mt-3 w-full py-2.5 bg-red-50 text-danger rounded-xl text-xs font-bold uppercase hover:bg-red-100 transition-colors flex items-center justify-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3a4 4 0 00-4 4v3a4 4 0 004 4h9a4 4 0 004-4V7a4 4 0 00-4-4h-4z" />
                  </svg>
                  Quitar imagen
                </button>
              </div>
            </div>

            <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
              <div class="p-4 border-b bg-gray-50">
                <h3 class="text-sm font-black text-text uppercase">Información</h3>
              </div>
              <div class="p-4 space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-xs text-gray-400 font-bold uppercase">ID</span>
                  <span class="text-sm font-black text-text">#{{ product.id }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-gray-400 font-bold uppercase">Categoría</span>
                  <span class="text-sm font-bold text-text">{{ product.category?.name || 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-gray-400 font-bold uppercase">Variantes</span>
                  <span class="text-sm font-bold text-text">{{ product.variants?.length || 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-gray-400 font-bold uppercase">Estado</span>
                  <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase"
                    :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'">
                    {{ product.is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Main: Formulario -->
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
              <div class="p-4 border-b bg-gray-50 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <h3 class="text-sm font-black text-text uppercase">Datos Generales</h3>
              </div>
              <div class="p-6 space-y-5">
                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Nombre del Producto</label>
                  <input v-model="form.name" type="text"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-medium focus:ring-4 focus:ring-orange-100 focus:border-primary transition-all"
                    placeholder="Ej: Pollo Broster Crispy" />
                  <p v-if="form.errors.name" class="text-danger text-xs mt-1 font-bold">{{ form.errors.name }}</p>
                </div>

                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Descripción</label>
                  <textarea v-model="form.description" rows="3"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-medium focus:ring-4 focus:ring-orange-100 focus:border-primary transition-all resize-none"
                    placeholder="Describe el producto..."></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Price is now in variants, no base price field needed -->
                  <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Categoría</label>
                    <select v-model="form.category_id"
                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-medium focus:ring-4 focus:ring-orange-100 focus:border-primary transition-all min-h-[52px]">
                      <option value="">Seleccionar categoría...</option>
                      <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="text-danger text-xs mt-1 font-bold">{{ form.errors.category_id }}</p>
                  </div>
                </div>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors">
                  <input type="checkbox" v-model="form.is_active"
                    class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary" />
                  <div>
                    <span class="text-sm font-black text-gray-700 uppercase">Producto Activo</span>
                    <p class="text-[10px] text-gray-400 font-bold">Visible en el punto de venta</p>
                  </div>
                </label>
              </div>
            </div>

            <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
              <div class="p-4 border-b bg-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                  <h3 class="text-sm font-black text-text uppercase">Variantes</h3>
                </div>
                <button type="button" @click="addVariant"
                  class="flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold uppercase hover:bg-orange-700 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Agregar
                </button>
              </div>
              <div class="p-6">
                <div v-if="form.variants.length === 0" class="text-center py-8 bg-gray-50 rounded-2xl">
                  <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                  <p class="text-gray-400 text-sm font-bold">Sin variantes</p>
                  <p class="text-xs text-gray-300 mt-1">Agrega tamaños, porciones o presentaciones</p>
                </div>

                <div class="space-y-3">
                  <div v-for="(variant, index) in form.variants" :key="index"
                    class="flex gap-3 items-center p-4 bg-gray-50 rounded-xl group hover:bg-gray-100 transition-colors">
                    <span class="w-7 h-7 flex items-center justify-center bg-primary/10 text-primary rounded-lg text-xs font-black">{{ index + 1 }}</span>
                    <input v-model="variant.name" type="text" placeholder="Nombre (ej: 1/4, 1/2, Entero)"
                      class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg font-medium text-sm focus:ring-2 focus:ring-orange-100 focus:border-primary transition-all" />
                    <div class="relative">
                      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-bold">Bs.</span>
                      <input v-model="variant.price" type="number" step="0.01" min="0" placeholder="0.00"
                        class="w-28 pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-lg font-bold text-sm focus:ring-2 focus:ring-orange-100 focus:border-primary transition-all" />
                    </div>
                    <button type="button" @click="removeVariant(index)"
                      class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-danger hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-between bg-white rounded-2xl shadow-lg p-4">
              <Link :href="route('products.index')"
                class="px-6 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm uppercase hover:bg-gray-200 transition-all">
                Cancelar
              </Link>
              <PrimaryButton type="submit" :disabled="form.processing" class="px-8">
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Actualizar Producto
              </PrimaryButton>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
