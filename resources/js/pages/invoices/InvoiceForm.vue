<template>
  <div class="max-w-4xl mx-auto p-6">
    <form @submit.prevent="submitForm" class="bg-white shadow-md rounded-lg p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Client</label>
          <select v-model="form.client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">Select Client</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.name }}
            </option>
          </select>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Issue Date</label>
            <input type="date" v-model="form.issue_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Due Date</label>
            <input type="date" v-model="form.due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
          </div>
        </div>
      </div>

      <div class="mb-6">
        <h3 class="text-lg font-medium mb-4">Invoice Items</h3>
        <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-12 gap-4 mb-4">
          <div class="col-span-5">
            <input type="text" v-model="item.description" placeholder="Description" 
                   class="block w-full rounded-md border-gray-300 shadow-sm">
          </div>
          <div class="col-span-2">
            <input type="number" v-model="item.quantity" @change="calculateTotal" 
                   class="block w-full rounded-md border-gray-300 shadow-sm" min="1">
          </div>
          <div class="col-span-2">
            <input type="number" v-model="item.unit_price" @change="calculateTotal" 
                   class="block w-full rounded-md border-gray-300 shadow-sm" step="0.01" min="0">
          </div>
          <div class="col-span-2">
            <input type="text" :value="formatCurrency(item.quantity * item.unit_price)" 
                   class="block w-full rounded-md border-gray-300 bg-gray-50" readonly>
          </div>
          <div class="col-span-1">
            <button type="button" @click="removeItem(index)" 
                    class="text-red-600 hover:text-red-800">Remove</button>
          </div>
        </div>
        
        <button type="button" @click="addItem" 
                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
          Add Item
        </button>
      </div>

      <div class="bg-gray-50 p-4 rounded-md mb-6">
        <div class="flex justify-between mb-2">
          <span>Subtotal:</span>
          <span>{{ formatCurrency(subtotal) }}</span>
        </div>
        <div class="flex justify-between mb-2">
          <span>Tax (10%):</span>
          <span>{{ formatCurrency(taxAmount) }}</span>
        </div>
        <div class="flex justify-between text-lg font-bold">
          <span>Total:</span>
          <span>{{ formatCurrency(totalAmount) }}</span>
        </div>
      </div>

      <div class="flex justify-end space-x-4">
        <button type="button" @click="$emit('cancel')" 
                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
          Cancel
        </button>
        <button type="submit" 
                class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600">
          {{ invoiceId ? 'Update' : 'Create' }} Invoice
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    invoiceId: Number,
    clients: Array
  },
  data() {
    return {
      form: {
        client_id: '',
        issue_date: new Date().toISOString().split('T')[0],
        due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        items: [
          { description: '', quantity: 1, unit_price: 0 }
        ],
        notes: ''
      },
      subtotal: 0,
      taxAmount: 0,
      totalAmount: 0
    }
  },
  methods: {
    addItem() {
      this.form.items.push({ description: '', quantity: 1, unit_price: 0 });
    },
    removeItem(index) {
      if (this.form.items.length > 1) {
        this.form.items.splice(index, 1);
        this.calculateTotal();
      }
    },
    calculateTotal() {
      this.subtotal = this.form.items.reduce((sum, item) => {
        return sum + (item.quantity * item.unit_price);
      }, 0);
      
      this.taxAmount = this.subtotal * 0.1;
      this.totalAmount = this.subtotal + this.taxAmount;
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount);
    },
    async submitForm() {
      try {
        const url = this.invoiceId 
          ? `/api/invoices/${this.invoiceId}`
          : '/api/invoices';
        
        const method = this.invoiceId ? 'put' : 'post';
        
        await axios[method](url, this.form);
        this.$emit('saved');
      } catch (error) {
        console.error('Error saving invoice:', error);
      }
    }
  },
  mounted() {
    this.calculateTotal();
  }
}
</script>