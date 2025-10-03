<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <h2 class="text-xl font-bold mb-4">{{ client ? 'Edit Client' : 'New Client' }}</h2>
        
        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name *</label>
              <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Email *</label>
              <input v-model="form.email" type="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Phone</label>
              <input v-model="form.phone" type="tel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Tax Number</label>
              <input v-model="form.tax_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Address</label>
              <textarea v-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end space-x-3">
            <button type="button" @click="$emit('cancel')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 border border-gray-300 rounded-md">
              Cancel
            </button>
            <button type="submit" :disabled="loading" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50">
              {{ loading ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  client: Object
});

const emit = defineEmits(['saved', 'cancel']);

const form = reactive({
  name: '',
  email: '',
  phone: '',
  tax_number: '',
  address: ''
});

const loading = ref(false);

const submitForm = async () => {
  loading.value = true;
  try {
    if (props.client) {
      await axios.put(`/api/clients/${props.client.id}`, form);
    } else {
      await axios.post('/api/clients', form);
    }
    emit('saved');
  } catch (error) {
    console.error('Error saving client:', error);
    alert('Failed to save client');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (props.client) {
    Object.assign(form, props.client);
  }
});
</script>