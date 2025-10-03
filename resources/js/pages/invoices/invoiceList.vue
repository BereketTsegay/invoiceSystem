<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
        <p class="text-gray-600">Manage and track all your invoices</p>
      </div>
      <button
        v-if="authStore.can('invoice.create')"
        @click="$router.push('/invoices/create')"
        class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Invoice
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" @change="fetchInvoices" class="w-full rounded-md border-gray-300 shadow-sm">
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
          <select v-model="filters.client_id" @change="fetchInvoices" class="w-full rounded-md border-gray-300 shadow-sm">
            <option value="">All Clients</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input v-model="filters.date_from" @change="fetchInvoices" type="date" class="w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input v-model="filters.date_to" @change="fetchInvoices" type="date" class="w-full rounded-md border-gray-300 shadow-sm">
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        <p class="mt-2 text-gray-600">Loading invoices...</p>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Invoice
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Client
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Due Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ invoice.client.name }}</div>
                <div class="text-sm text-gray-500">{{ invoice.client.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(invoice.issue_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(invoice.due_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatCurrency(invoice.total_amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(invoice.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ invoice.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  @click="viewInvoice(invoice)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  View
                </button>
                <button
                  v-if="authStore.can('invoice.edit')"
                  @click="editInvoice(invoice)"
                  class="text-green-600 hover:text-green-900"
                >
                  Edit
                </button>
                <button
                  v-if="authStore.can('invoice.send') && invoice.status === 'draft'"
                  @click="sendInvoice(invoice)"
                  class="text-orange-600 hover:text-orange-900"
                >
                  Send
                </button>
                <button
                  v-if="authStore.can('invoice.delete')"
                  @click="deleteInvoice(invoice)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="invoices.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new invoice.</p>
          <div class="mt-6">
            <button
              v-if="authStore.can('invoice.create')"
              @click="$router.push('/invoices/create')"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              New Invoice
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="invoices.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm text-gray-700">
              Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} results
            </p>
          </div>
          <div class="space-x-2">
            <button
              v-if="invoices.prev_page_url"
              @click="fetchInvoices(invoices.current_page - 1)"
              class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              v-if="invoices.next_page_url"
              @click="fetchInvoices(invoices.current_page + 1)"
              class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

const invoices = ref({ data: [] });
const clients = ref([]);
const loading = ref(false);

const filters = reactive({
  status: '',
  client_id: '',
  date_from: '',
  date_to: ''
});

const fetchInvoices = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: page,
      ...filters
    });
    
    const response = await axios.get(`/api/invoices?${params}`);
    invoices.value = response.data;
  } catch (error) {
    console.error('Error fetching invoices:', error);
  } finally {
    loading.value = false;
  }
};

const fetchClients = async () => {
  try {
    const response = await axios.get('/api/clients');
    clients.value = response.data.data;
  } catch (error) {
    console.error('Error fetching clients:', error);
  }
};

const viewInvoice = (invoice) => {
  router.push(`/invoices/${invoice.id}`);
};

const editInvoice = (invoice) => {
  router.push(`/invoices/edit/${invoice.id}`);
};

const sendInvoice = async (invoice) => {
  if (!confirm(`Send invoice ${invoice.invoice_number} to ${invoice.client.name}?`)) return;

  try {
    await axios.post(`/api/invoices/${invoice.id}/send`);
    alert('Invoice sent successfully!');
    fetchInvoices();
  } catch (error) {
    alert('Failed to send invoice: ' + (error.response?.data?.message || 'Unknown error'));
  }
};

const deleteInvoice = async (invoice) => {
  if (!confirm(`Are you sure you want to delete invoice ${invoice.invoice_number}? This action cannot be undone.`)) return;

  try {
    await axios.delete(`/api/invoices/${invoice.id}`);
    alert('Invoice deleted successfully!');
    fetchInvoices();
  } catch (error) {
    alert('Failed to delete invoice: ' + (error.response?.data?.message || 'Unknown error'));
  }
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
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
    cancelled: 'bg-gray-100 text-gray-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  fetchInvoices();
  fetchClients();
});
</script>