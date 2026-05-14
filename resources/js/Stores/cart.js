import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        customer: null,
        orderType: 'dine_in', // dine_in, take_out, delivery
        notes: '',
    }),

    getters: {
        totalItems: (state) => state.items.reduce((acc, item) => acc + item.quantity, 0),
        subtotal: (state) => state.items.reduce((acc, item) => acc + (item.price * item.quantity), 0),
        total: (getters) => getters.subtotal, // Aquí se podrían sumar impuestos o delivery fee
    },

    actions: {
        addItem(product, variant) {
            const existingItem = this.items.find(
                (item) => item.product_id === product.id && item.variant_id === variant.id
            );

            if (existingItem) {
                existingItem.quantity++;
            } else {
                this.items.push({
                    product_id: product.id,
                    variant_id: variant.id,
                    name: product.name,
                    variant_name: variant.name,
                    price: parseFloat(variant.price),
                    quantity: 1,
                    image: product.image
                });
            }
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        updateQuantity(index, quantity) {
            if (quantity <= 0) {
                this.removeItem(index);
            } else {
                this.items[index].quantity = quantity;
            }
        },

        clearCart() {
            this.items = [];
            this.notes = '';
            this.orderType = 'dine_in';
        }
    }
});
