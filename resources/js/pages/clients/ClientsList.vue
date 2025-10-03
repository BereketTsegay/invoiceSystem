<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Clients</h1>
        <p class="text-gray-600">Manage your client database</p>
      </div>
      <button
        v-if="authStore.can('client.create')"
        @click="showClientForm = true"
        class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Client
      </button>
    </div>

    <!-- Clients Grid -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
      <p class="mt-2 text-gray-600">Loading clients...</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="client in clients.data"
        :key="client.id"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ client.name }}</h3>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {{ client.invoices_count }} invoices
          </span>
        </div>
        
        <div class="space-y-2 text-sm text-gray-600">
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            {{ client.email }}
          </div>
          
          <div v-if="client.phone" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
            </svg>
            {{ client.phone }}
          </div>

          <div v-if="client.tax_number" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Tax: {{ client.tax_number }}
          </div>
        </div>

        <div class="mt-4 flex space-x-2">
          <button
            @click="viewClient(client)"
            class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded-md text-sm hover:bg-gray-200"
          >
            View
          </button>
          <button
            v-if="authStore.can('client.edit')"
            @click="editClient(client)"
            class="flex-1 bg-blue-100 text-blue-700 px-3 py-2 rounded-md text-sm hover:bg-blue-200"
          >
            Edit
          </button>
          <button
            v-if="authStore.can('client.delete')"
            @click="deleteClient(client)"
            class="flex-1 bg-red-100 text-red-700 px-3 py-2 rounded-md text-sm hover:bg-red-200"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && clients.data.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No clients</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating a new client.</p>
      <div class="mt-6">
        <button
          v-if="authStore.can('client.create')"
          @click="showClientForm = true"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
        >
          <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          New Client
        </button>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="clients.data.length > 0" class="mt-6 bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
      <div class="flex justify-between items-center">
        <div>
          <p class="text-sm text-gray-700">
            Showing {{ clients.from }} to {{ clients.to }} of {{ clients.total }} results
          </p>
        </div>
        <div class="space-x-2">
          <button
            v-if="clients.prev_page_url"
            @click="fetchClients(clients.current_page - 1)"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Previous
          </button>
          <button
            v-if="clients.next_page_url"
            @click="fetchClients(clients.current_page + 1)"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Client Form Modal -->
    <ClientForm
      v-if="showClientForm"
      :client="selectedClient"
      @saved="handleClientSaved"
      @cancel="showClientForm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../store';
import axios from 'axios';
import ClientForm from './ClientForm.vue';

const authStore = useAuthStore();

const clients = ref({ data: [] });
const loading = ref(false);
const showClientForm = ref(false);
const selectedClient = ref(null);

const fetchClients = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/clients?page=${page}`);
    clients.value = response.data;
  } catch (error) {
    console.error('Error fetching clients:', error);
  } finally {
    loading.value = false;
  }
};

const viewClient = (client) => {
  // Implement client view
  console.log('View client:', client);
};

const editClient = (client) => {
  selectedClient.value = client;
  showClientForm.value = true;
};

const deleteClient = async (client) => {
  if (!confirm(`Are you sure you want to delete ${client.name}? This will also delete all associated invoices.`)) return;

  try {
    await axios.delete(`/api/clients/${client.id}`);
    alert('Client deleted successfully!');
    fetchClients();
  } catch (error) {
    alert('Failed to delete client: ' + (error.response?.data?.message || 'Unknown error'));
  }
};

const handleClientSaved = () => {
  showClientForm.value = false;
  selectedClient.value = null;
  fetchClients();
};

onMounted(() => {
  fetchClients();
});
</script>