<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Clients</h1>
        <p class="text-gray-600">Manage your client database</p>
      </div>
      <!-- Enhanced Filters -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6 border border-gray-200">
        <div class="flex flex-wrap gap-4 items-center">
            <!-- Status Filter -->
            <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="filters.status" @change="fetchClients" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
                <option value="lead">Lead</option>
            </select>
            </div>

            <!-- Priority Filter -->
            <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
            <select v-model="filters.priority" @change="fetchClients" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Priority</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="vip">VIP</option>
            </select>
            </div>

            <!-- Industry Filter -->
            <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
            <select v-model="filters.industry" @change="fetchClients" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Industries</option>
                <option value="technology">Technology</option>
                <option value="healthcare">Healthcare</option>
                <option value="finance">Finance</option>
                <option value="education">Education</option>
                <option value="retail">Retail</option>
                <option value="manufacturing">Manufacturing</option>
                <option value="other">Other</option>
            </select>
            </div>

            <!-- Country Filter -->
            <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
            <select v-model="filters.country" @change="fetchClients" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Countries</option>
                <option value="US">United States</option>
                <option value="CA">Canada</option>
                <option value="GB">United Kingdom</option>
                <option value="AU">Australia</option>
                <option value="other">Other</option>
            </select>
            </div>

            <!-- Existing filters... -->
        </div>
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
        <!-- Enhanced Client Card in Grid View -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
        <!-- Client Header -->
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                <span class="text-white font-medium text-sm">
                    {{ getInitials(client.name) }}
                </span>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 truncate max-w-[150px]">
                {{ client.name }}
                </h3>
                <p class="text-sm text-gray-500 truncate max-w-[150px]">{{ client.email }}</p>
                <div class="flex space-x-2 mt-1">
                <span :class="getStatusBadgeClass(client.status)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                    {{ client.status }}
                </span>
                <span :class="getPriorityBadgeClass(client.priority)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                    {{ client.priority }}
                </span>
                </div>
            </div>
            </div>
        </div>

        <!-- Enhanced Client Details -->
        <div class="space-y-2 text-sm text-gray-600 mb-4">
            <div v-if="client.contact_person" class="flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Contact: {{ client.contact_person }}
            </div>

            <div v-if="client.company_name" class="flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            {{ client.company_name }}
            </div>

            <div v-if="client.industry" class="flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            {{ client.industry }}
            </div>

            <div v-if="client.city && client.country" class="flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            {{ client.city }}, {{ client.country }}
            </div>

            <div class="flex items-center justify-between">
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ client.total_invoices || 0 }} invoices
            </span>
            <span class="font-medium text-blue-600">
                {{ formatCurrency(client.total_revenue || 0) }}
            </span>
            </div>

            <!-- Credit Limit Warning -->
            <div v-if="client.credit_limit && client.outstanding_balance >= client.credit_limit * 0.8" class="bg-yellow-50 border border-yellow-200 rounded p-2 mt-2">
            <div class="flex items-center">
                <svg class="w-4 h-4 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <span class="text-xs text-yellow-700">
                Near credit limit ({{ ((client.outstanding_balance / client.credit_limit) * 100).toFixed(0) }}%)
                </span>
            </div>
            </div>
        </div>
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
import { ref, onMounted, reactive } from 'vue';
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

// Utility methods for badges and formatting
const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    suspended: 'bg-red-100 text-red-800',
    lead: 'bg-blue-100 text-blue-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: 'bg-gray-100 text-gray-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    vip: 'bg-purple-100 text-purple-800',
  };
  return classes[priority] || 'bg-gray-100 text-gray-800';
};

// Enhanced filters object
const filters = reactive({
  status: '',
  priority: '',
  industry: '',
  country: '',
  sort_field: 'created_at',
  sort_direction: 'desc'
});
const getInitials = (name) => {
  return name
    .split(' ')
    .map(part => part.charAt(0))
    .join('')
    .toUpperCase()
    .substring(0, 2);
};
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};
</script>
