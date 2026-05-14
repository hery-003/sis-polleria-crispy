<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Card from '@/Components/Card.vue'

const props = defineProps({
  products: Object
})

const search = ref('')
const selectedCategory = ref('all')
const showDeleteId = ref(null)
const viewMode = ref('cards')
const showImageModal = ref(false)
const selectedImage = ref('')
const selectedProductName = ref('')

// Debounce para búsqueda de productos
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const debouncedSearch = debounce(() => {
    // Trigger reactive update
    search.value = search.value;
}, 300);

const openImageModal = (imageUrl, productName) => {
    if (!imageUrl) return;
    selectedImage.value = imageUrl
    selectedProductName.value = productName
    showImageModal.value = true
}

const closeImageModal = () => {
    showImageModal.value = false
    selectedImage.value = ''
    selectedProductName.value = ''
}

const categories = computed(() => {
  const cats = new Map()
  props.products.data.forEach(p => {
    if (p.category) cats.set(p.category.id, p.category.name)
  })
  return [{ id: 'all', name: 'Todos' }, ...[...cats].map(([id, name]) => ({ id, name }))]
})

const filteredProducts = computed(() => {
  let items = props.products.data
  if (selectedCategory.value !== 'all') {
    items = items.filter(p => p.category?.id == selectedCategory.value)
  }
  if (search.value) {
    const term = search.value.toLowerCase()
    items = items.filter(p =>
      p.name.toLowerCase().includes(term) ||
      p.category?.name.toLowerCase().includes(term)
    )
  }
  return items
})

const stats = computed(() => {
  const all = props.products.data
  return {
    total: all.length,
    active: all.filter(p => p.is_active).length,
    inactive: all.filter(p => !p.is_active).length,
    categories: new Set(all.map(p => p.category?.id).filter(Boolean)).size
  }
})

const getProductPrice = (product) => {
  if (product.price) return Number(product.price).toFixed(2)
  if (product.variants?.length > 0) return Number(product.variants[0].price).toFixed(2)
  return '0.00'
}

const hasVariants = (product) => product.variants?.length > 0

const destroy = (id) => {
  router.delete(route('products.destroy', id), {
    onSuccess: () => { showDeleteId.value = null }
  })
}
</script>

<template>
  <Head title="Productos" />

  <AuthenticatedLayout>
    <div class="pb-6">
      <div class="space-y-8">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="text-3xl font-black text-text tracking-tight uppercase font-poppins">Gestión de Productos</h2>
            <PrimaryButton :href="route('products.create')">
                <span class="mr-2">➕</span> Nuevo Producto
            </PrimaryButton>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
          <Card hover class="border-l-8 border-primary">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Productos</p>
            <p class="text-4xl font-black text-text mt-2 font-mono">{{ stats.total }}</p>
          </Card>
          <Card hover class="border-l-8 border-green-500">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Activos</p>
            <p class="text-4xl font-black text-green-600 mt-2 font-mono">{{ stats.active }}</p>
          </Card>
          <Card hover class="border-l-8 border-gray-400">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Inactivos</p>
            <p class="text-4xl font-black text-gray-500 mt-2 font-mono">{{ stats.inactive }}</p>
          </Card>
          <Card hover class="border-l-8 border-blue-500">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Categorías</p>
            <p class="text-4xl font-black text-blue-600 mt-2 font-mono">{{ stats.categories }}</p>
          </Card>
        </div>

        <Card no-padding>
          <div class="p-6 flex flex-col md:flex-row gap-6 items-center bg-gray-50/50">
            <div class="relative flex-1 w-full">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input v-model="search" @input="debouncedSearch" type="text" placeholder="Buscar por nombre o categoría..." 
                  class="w-full pl-12 pr-4 py-3.5 bg-white border-2 border-gray-100 rounded-2xl font-bold focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-sm" />
            </div>
            
            <div class="flex gap-2 p-1.5 bg-white rounded-2xl border border-gray-100">
              <button 
                @click="viewMode = 'cards'"
                :class="viewMode === 'cards' ? 'bg-primary text-white shadow-lg shadow-orange-100' : 'text-gray-400 hover:text-gray-600'"
                class="px-5 py-2.5 text-xs font-black uppercase tracking-widest rounded-xl transition-all"
              >
                📇 Tarjetas
              </button>
              <button 
                @click="viewMode = 'table'"
                :class="viewMode === 'table' ? 'bg-primary text-white shadow-lg shadow-orange-100' : 'text-gray-400 hover:text-gray-600'"
                class="px-5 py-2.5 text-xs font-black uppercase tracking-widest rounded-xl transition-all"
              >
                📊 Tabla
              </button>
            </div>
          </div>

          <!-- Vista de Tarjetas -->
          <div v-if="viewMode === 'cards'" class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="product in filteredProducts" :key="product.id" 
              class="group bg-white rounded-3xl border-2 border-gray-50 hover:border-primary/20 hover:shadow-2xl transition-all overflow-hidden flex flex-col relative">
              
              <div class="aspect-square relative overflow-hidden bg-gray-50 p-6 flex items-center justify-center">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name" 
                  class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-300" 
                  @click="openImageModal(product.image_url, product.name)">
                <span v-else class="text-7xl group-hover:scale-110 transition-transform duration-300">🍗</span>
                
                <div class="absolute top-4 right-4">
                  <span :class="product.is_active ? 'bg-green-500 shadow-green-100' : 'bg-gray-400 shadow-gray-100'" 
                    class="px-3 py-1 rounded-full text-[9px] font-black text-white uppercase tracking-widest shadow-lg">
                    {{ product.is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </div>
              </div>

              <div class="p-6 flex-1 flex flex-col">
                <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-1">{{ product.category?.name || 'Sin Categoría' }}</p>
                <h3 class="text-lg font-black text-text uppercase leading-tight font-poppins">{{ product.name }}</h3>
                
                <div class="mt-4 flex items-center justify-between">
                  <div class="flex flex-col">
                    <span class="text-[10px] font-black text-gray-400 uppercase">Precio</span>
                    <span class="text-2xl font-black text-text font-mono">Bs. {{ getProductPrice(product) }}</span>
                  </div>
                  <div v-if="hasVariants(product)" class="bg-primary/10 px-3 py-1 rounded-lg text-primary text-[10px] font-black uppercase">
                    {{ product.variants.length }} Var.
                  </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-50 flex gap-2">
                  <PrimaryButton :href="route('products.edit', product.id)" class="flex-1 !py-2.5 !text-[10px]">
                    Editar
                  </PrimaryButton>
                  <DangerButton @click="showDeleteId = product.id" class="!py-2.5 !px-4">
                    🗑️
                  </DangerButton>
                </div>
              </div>
            </div>
          </div>

          <!-- Vista de Tabla -->
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left">
              <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                  <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Producto</th>
                  <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Categoría</th>
                  <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Precio</th>
                  <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado</th>
                  <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-orange-50/30 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-4">
                      <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100">
                        <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-contain">
                        <span v-else class="text-xl">🍗</span>
                      </div>
                      <div>
                        <div class="font-black text-text uppercase text-sm">{{ product.name }}</div>
                        <div class="text-[10px] text-gray-400 font-bold">{{ product.variants?.length || 0 }} variantes</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest">
                      {{ product.category?.name || 'Sin Categoría' }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="font-black text-text font-mono">Bs. {{ getProductPrice(product) }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" 
                      class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest">
                      {{ product.is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <Link :href="route('products.edit', product.id)" class="p-2.5 bg-gray-50 hover:bg-primary hover:text-white rounded-xl text-gray-400 transition-all active:scale-95 border border-gray-100">
                            ✏️
                        </Link>
                        <button @click="showDeleteId = product.id" class="p-2.5 bg-gray-50 hover:bg-danger hover:text-white rounded-xl text-gray-400 transition-all active:scale-95 border border-gray-100">
                            🗑️
                        </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Card>

        <div v-if="filteredProducts.length === 0" class="py-20 flex flex-col items-center justify-center text-gray-400">
          <span class="text-7xl mb-6 grayscale opacity-30">📦</span>
          <p class="text-xl font-black uppercase tracking-widest font-poppins">No se encontraron productos</p>
          <PrimaryButton v-if="search || selectedCategory !== 'all'" @click="search = ''; selectedCategory = 'all'" class="mt-6">
            Limpiar filtros
          </PrimaryButton>
        </div>
      </div>
    </div>

    <!-- Modal de Imagen -->
    <Modal :show="showImageModal" @close="closeImageModal">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black text-text uppercase font-poppins">{{ selectedProductName }}</h3>
                <button @click="closeImageModal" class="text-2xl hover:scale-110 transition-transform">✕</button>
            </div>
            <div class="bg-gray-50 rounded-[2rem] p-8 flex items-center justify-center border-4 border-dashed border-gray-200">
                <img :src="selectedImage" class="max-h-[60vh] object-contain rounded-xl shadow-2xl">
            </div>
        </div>
    </Modal>

    <!-- Modal de Confirmación de Eliminación -->
    <Modal :show="!!showDeleteId" @close="showDeleteId = null" max-width="sm">
        <div class="p-8 text-center">
            <div class="w-20 h-20 bg-danger/10 text-danger rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                ⚠️
            </div>
            <h3 class="text-2xl font-black text-text uppercase font-poppins">¿Eliminar Producto?</h3>
            <p class="text-gray-500 mt-4 font-bold">Esta acción no se puede deshacer. El producto será archivado.</p>
            <div class="mt-8 flex gap-4">
                <SecondaryButton @click="showDeleteId = null" class="flex-1">Cancelar</SecondaryButton>
                <DangerButton @click="destroy(showDeleteId)" class="flex-1">Eliminar</DangerButton>
            </div>
        </div>
    </Modal>
  </AuthenticatedLayout>
</template>

