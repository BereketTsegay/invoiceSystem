<template>
  <div class="max-w-6xl mx-auto p-6">
    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Header -->
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6">
          {{ invoiceId ? 'Edit Invoice' : 'Create New Invoice' }}
        </h2>

        <!-- Client and Dates -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <!-- Client Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
            <div class="relative">
              <select
                v-model="form.client_id"
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.client_id }"
                @change="onClientChange"
              >
                <option value="">Select Client</option>
                <option
                  v-for="client in clients"
                  :key="client.id"
                  :value="client.id"
                >
                  {{ client.name }} - {{ client.email }}
                </option>
              </select>
              <!-- Loading indicator -->
              <div v-if="loadingClients" class="absolute inset-y-0 right-0 flex items-center pr-3">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
              </div>
            </div>
            <p v-if="errors.client_id" class="mt-1 text-sm text-red-600">{{ errors.client_id[0] }}</p>

            <!-- Client Quick Info -->
            <div v-if="selectedClient" class="mt-2 p-3 bg-blue-50 rounded-md border border-blue-200">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-blue-900">{{ selectedClient.name }}</p>
                  <p class="text-xs text-blue-700">{{ selectedClient.company_name || 'No company' }}</p>
                  <p class="text-xs text-blue-600">{{ selectedClient.email }}</p>
                </div>
                <div class="text-right">
                  <p class="text-xs text-blue-700">Payment Terms: {{ getPaymentTermsLabel(selectedClient.payment_terms) }}</p>
                  <p v-if="selectedClient.credit_limit" class="text-xs text-blue-700">
                    Credit Limit: {{ formatCurrency(selectedClient.credit_limit) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Issue Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date *</label>
            <input
              v-model="form.issue_date"
              type="date"
              required
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.issue_date }"
            >
            <p v-if="errors.issue_date" class="mt-1 text-sm text-red-600">{{ errors.issue_date[0] }}</p>
          </div>

          <!-- Due Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date *</label>
            <input
              v-model="form.due_date"
              type="date"
              required
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.due_date }"
            >
            <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">{{ errors.due_date[0] }}</p>
          </div>
        </div>

        <!-- Client Search and Quick Add -->
        <div class="mb-6">
          <div class="flex space-x-4">
            <!-- Client Search -->
            <div class="flex-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Quick Client Search</label>
              <div class="relative">
                <input
                  v-model="clientSearch"
                  @input="searchClients"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10"
                  placeholder="Search clients by name, email, or company..."
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>

                <!-- Search Results Dropdown -->
                <div v-if="searchResults.length > 0 && clientSearch" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                  <div
                    v-for="client in searchResults"
                    :key="client.id"
                    @click="selectClientFromSearch(client)"
                    class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                  >
                    <div class="flex justify-between items-center">
                      <div>
                        <p class="text-sm font-medium text-gray-900">{{ client.name }}</p>
                        <p class="text-xs text-gray-500">{{ client.email }}</p>
                      </div>
                      <span class="text-xs text-gray-400">{{ client.company_name }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Add Client Button -->
            <div class="flex items-end">
              <button
                type="button"
                @click="showQuickClientForm = true"
                class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center text-sm"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Quick Add Client
              </button>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="Additional notes for the client..."
            :class="{ 'border-red-500': errors.notes }"
          ></textarea>
          <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes[0] }}</p>
        </div>
      </div>

      <!-- Invoice Items -->
      <InvoiceItemList
        :invoiceId="invoiceId"
        :editable="true"
        @items-updated="loadInvoiceTotals"
        @totals-changed="handleTotalsChanged"
      />

      <!-- Summary and Actions -->
      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center">
          <div class="space-y-2">
            <div class="text-sm text-gray-600">
              <strong>Status:</strong>
              <span :class="getStatusBadgeClass(form.status)" class="ml-2 px-2 py-1 rounded-full text-xs">
                {{ form.status }}
              </span>
            </div>
            <div class="text-sm text-gray-600" v-if="invoiceId">
              <strong>Invoice Number:</strong> {{ form.invoice_number }}
            </div>
            <div v-if="selectedClient" class="text-sm text-gray-600">
              <strong>Client Payment Terms:</strong> {{ getPaymentTermsLabel(selectedClient.payment_terms) }}
            </div>
          </div>

          <div class="text-right space-y-2">
            <div class="text-2xl font-bold text-gray-900">
              {{ formatCurrency(totals.grandTotal || 0) }}
            </div>
            <div class="text-sm text-gray-600">
              Due: {{ formatDate(form.due_date) }}
            </div>
            <div v-if="selectedClient && selectedClient.credit_limit" class="text-xs text-gray-500">
              Credit Limit: {{ formatCurrency(selectedClient.credit_limit) }}
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
          <button
            type="button"
            @click="$emit('cancel')"
            class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading || !form.client_id"
            class="px-6 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Saving...' : (invoiceId ? 'Update' : 'Create') }} Invoice
          </button>
        </div>
      </div>
    </form>

    <!-- Quick Client Form Modal -->
    <div v-if="showQuickClientForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
          <h3 class="text-lg font-bold mb-4">Quick Add Client</h3>
          <form @submit.prevent="createQuickClient">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input
                  v-model="quickClientForm.name"
                  type="text"
                  required
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  placeholder="Client name"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input
                  v-model="quickClientForm.email"
                  type="email"
                  required
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  placeholder="client@example.com"
                >
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                  <input
                    v-model="quickClientForm.phone"
                    type="tel"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Phone number"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                  <input
                    v-model="quickClientForm.company_name"
                    type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Company name"
                  >
                </div>
              </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
              <button
                type="button"
                @click="showQuickClientForm = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 border border-gray-300 rounded-md"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="creatingQuickClient"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50"
              >
                {{ creatingQuickClient ? 'Adding...' : 'Add Client' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import InvoiceItemList from './InvoiceItemList.vue';

const props = defineProps({
  invoiceId: [Number, String]
});

const emit = defineEmits(['saved', 'cancel']);

const route = useRoute();

// Form data
const form = reactive({
  client_id: '',
  issue_date: new Date().toISOString().split('T')[0],
  due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  notes: '',
  status: 'draft',
  invoice_number: '',
  items: []
});

// Clients data
const clients = ref([]);
const selectedClient = ref(null);
const loadingClients = ref(false);
const clientSearch = ref('');
const searchResults = ref([]);
const searchTimeout = ref(null);

// Quick client form
const showQuickClientForm = ref(false);
const creatingQuickClient = ref(false);
const quickClientForm = reactive({
  name: '',
  email: '',
  phone: '',
  company_name: ''
});

// Other state
const totals = ref({});
const loading = ref(false);
const errors = ref({});

// Computed properties
const clientOptions = computed(() => {
  return clients.value.map(client => ({
    value: client.id,
    label: `${client.name} - ${client.email}`,
    client: client
  }));
});

// Methods
const getClients = async () => {
  loadingClients.value = true;
  try {
    const response = await axios.get('/api/clients', {
      params: {
        per_page: 100, // Get more clients for dropdown
        sort_field: 'name',
        sort_direction: 'asc'
      }
    });
    clients.value = response.data.data;

    // Pre-select client from query parameter
    if (route.query.client_id) {
      const clientFromQuery = clients.value.find(c => c.id == route.query.client_id);
      if (clientFromQuery) {
        form.client_id = clientFromQuery.id;
        selectedClient.value = clientFromQuery;
      }
    }
  } catch (error) {
    console.error('Error fetching clients:', error);
  } finally {
    loadingClients.value = false;
  }
};

const searchClients = () => {
  clearTimeout(searchTimeout.value);
  searchTimeout.value = setTimeout(async () => {
    if (!clientSearch.value.trim()) {
      searchResults.value = [];
      return;
    }

    try {
      const response = await axios.get('/api/clients/search', {
        params: {
          query: clientSearch.value,
          limit: 10
        }
      });
      searchResults.value = response.data;
    } catch (error) {
      console.error('Error searching clients:', error);
      searchResults.value = [];
    }
  }, 300);
};

const selectClientFromSearch = (client) => {
  form.client_id = client.id;
  selectedClient.value = client;
  clientSearch.value = '';
  searchResults.value = [];
};

const onClientChange = () => {
  selectedClient.value = clients.value.find(client => client.id == form.client_id) || null;
};

const createQuickClient = async () => {
  creatingQuickClient.value = true;
  try {
    const response = await axios.post('/api/clients', quickClientForm);

    // Add the new client to the clients list
    clients.value.push(response.data.client);

    // Select the new client
    form.client_id = response.data.client.id;
    selectedClient.value = response.data.client;

    // Reset and close the form
    Object.keys(quickClientForm).forEach(key => {
      quickClientForm[key] = '';
    });
    showQuickClientForm.value = false;

  } catch (error) {
    console.error('Error creating quick client:', error);
    alert('Failed to create client. Please try again.');
  } finally {
    creatingQuickClient.value = false;
  }
};

const loadInvoice = async () => {
  if (!props.invoiceId) return;

  try {
    const response = await axios.get(`/api/invoices/${props.invoiceId}`);
    Object.assign(form, response.data);

    // Set selected client if client_id is present
    if (form.client_id) {
      selectedClient.value = clients.value.find(client => client.id == form.client_id) || null;
    }
  } catch (error) {
    console.error('Error loading invoice:', error);
  }
};

const loadInvoiceTotals = async () => {
  if (!props.invoiceId) return;

  try {
    const response = await axios.get(`/api/invoices/${props.invoiceId}/items`);
    totals.value = response.data;
  } catch (error) {
    console.error('Error loading invoice totals:', error);
  }
};

const handleTotalsChanged = (newTotals) => {
  totals.value = newTotals;
};

const submitForm = async () => {
  loading.value = true;
  errors.value = {};

  try {
    let response;

    if (props.invoiceId) {
      response = await axios.put(`/api/invoices/${props.invoiceId}`, form);
    } else {
      response = await axios.post('/api/invoices', form);
    }

    emit('saved', response.data);
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      console.error('Error saving invoice:', error);
      alert('Failed to save invoice. Please try again.');
    }
  } finally {
    loading.value = false;
  }
};

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getPaymentTermsLabel = (terms) => {
  const termMap = {
    due_on_receipt: 'Due on Receipt',
    net_7: 'Net 7',
    net_15: 'Net 15',
    net_30: 'Net 30',
    net_45: 'Net 45',
    net_60: 'Net 60'
  };
  return termMap[terms] || terms || 'Net 30';
};

// Initialize
onMounted(async () => {
  await getClients();
  if (props.invoiceId) {
    await loadInvoice();
    await loadInvoiceTotals();
  }
});

// Watch for route changes to handle client pre-selection
watch(() => route.query.client_id, (newClientId) => {
  if (newClientId && clients.value.length > 0) {
    const client = clients.value.find(c => c.id == newClientId);
    if (client) {
      form.client_id = client.id;
      selectedClient.value = client;
    }
  }
});
</script>
