<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-start mb-6">
      <div>
        <div class="flex items-center space-x-4">
          <div class="h-16 w-16 bg-blue-500 rounded-full flex items-center justify-center">
            <span class="text-white font-bold text-xl">
              {{ getInitials(client.name) }}
            </span>
          </div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ client.name }}</h1>
            <div class="flex items-center space-x-2 mt-2">
              <span :class="getStatusBadgeClass(client.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                {{ client.status }}
              </span>
              <span :class="getPriorityBadgeClass(client.priority)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                {{ client.priority }}
              </span>
              <span v-if="client.company_name" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                {{ client.company_name }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="flex space-x-3">
        <button
          @click="editClient"
          class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Edit Client
        </button>
        <button
          @click="$router.back()"
          class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Back
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Invoices</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_invoices || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_revenue || 0) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <div class="flex items-center">
          <div class="p-3 bg-yellow-100 rounded-lg">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Outstanding</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.outstanding_balance || 0) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <div class="flex items-center">
          <div class="p-3 bg-purple-100 rounded-lg">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Payment Performance</p>
            <p class="text-2xl font-bold text-gray-900">{{ Math.round(stats.payment_performance || 0) }}%</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Client Information -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Primary Email</label>
              <p class="mt-1 text-sm text-gray-900">{{ client.email }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Secondary Email</label>
              <p class="mt-1 text-sm text-gray-900">{{ client.secondary_email || 'Not provided' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Primary Phone</label>
              <p class="mt-1 text-sm text-gray-900">{{ client.phone || 'Not provided' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Secondary Phone</label>
              <p class="mt-1 text-sm text-gray-900">{{ client.secondary_phone || 'Not provided' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Website</label>
              <p class="mt-1 text-sm text-gray-900">
                <a v-if="client.website" :href="client.website" target="_blank" class="text-blue-600 hover:text-blue-500">
                  {{ client.website }}
                </a>
                <span v-else>Not provided</span>
              </p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Contact Person</label>
              <p class="mt-1 text-sm text-gray-900">{{ client.contact_person || 'Not specified' }}</p>
            </div>
          </div>
        </div>

        <!-- Business Information -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Business Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Company Name</label>
              <p class="mt-1 text-sm text-gray-900">{{ client.company_name || 'Not provided' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Tax Number</label>
              <p class="mt-1 text-sm text-gray-900">{{ client.tax_number || 'Not provided' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Business Type</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatBusinessType(client.business_type) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Industry</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatIndustry(client.industry) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Employee Count</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatEmployeeCount(client.employee_count) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Source</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatSource(client.source) }}</p>
            </div>
          </div>
        </div>

        <!-- Address Information -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
          <div class="space-y-2">
            <p class="text-sm text-gray-900">{{ client.street_address }}</p>
            <p class="text-sm text-gray-900">
              {{ [client.city, client.state, client.postal_code].filter(Boolean).join(', ') }}
            </p>
            <p class="text-sm text-gray-900">{{ formatCountry(client.country) }}</p>
          </div>
        </div>

        <!-- Financial Information -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Currency</label>
              <p class="mt-1 text-sm text-gray-900">{{ client.currency }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Credit Limit</label>
              <p class="mt-1 text-sm text-gray-900">
                {{ client.credit_limit ? formatCurrency(client.credit_limit) : 'Not set' }}
                <span v-if="client.is_near_credit_limit" class="ml-2 text-yellow-600 text-xs">(Near limit)</span>
              </p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Payment Terms</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatPaymentTerms(client.payment_terms) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Average Invoice</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatCurrency(client.average_invoice_amount || 0) }}</p>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="client.notes" class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
          <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ client.notes }}</p>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
          <div class="space-y-3">
            <button
              @click="createInvoice"
              class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center justify-center"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Create Invoice
            </button>
            <button
              @click="sendEmail"
              class="w-full bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center justify-center"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
              Send Email
            </button>
            <button
              @click="markContacted"
              class="w-full bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 flex items-center justify-center"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
              Mark Contacted
            </button>
          </div>
        </div>

        <!-- Recent Invoices -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Invoices</h3>
          <div v-if="recentInvoices.length === 0" class="text-center py-4">
            <p class="text-gray-500 text-sm">No recent invoices</p>
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="invoice in recentInvoices"
              :key="invoice.id"
              class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
            >
              <div>
                <p class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(invoice.issue_date) }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ formatCurrency(invoice.total_amount) }}</p>
                <span :class="getStatusBadgeClass(invoice.status)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                  {{ invoice.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Client Form Modal -->
  <ClientForm
    v-if="showClientForm"
    :client="client"
    @saved="handleClientSaved"
    @cancel="showClientForm = false"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import ClientForm from './ClientForm.vue';

const route = useRoute();
const router = useRouter();

const client = ref({});
const stats = ref({});
const recentInvoices = ref([]);
const showClientForm = ref(false);
const loading = ref(false);

const loadClient = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/clients/${route.params.id}`);
    client.value = response.data.client;
    stats.value = response.data.stats;
    recentInvoices.value = response.data.recent_invoices;
  } catch (error) {
    console.error('Error loading client:', error);
    alert('Failed to load client details.');
  } finally {
    loading.value = false;
  }
};

const editClient = () => {
  showClientForm.value = true;
};

const handleClientSaved = () => {
  showClientForm.value = false;
  loadClient();
};

const createInvoice = () => {
  router.push(`/invoices/create?client_id=${client.value.id}`);
};

const sendEmail = () => {
  // Implement email functionality
  const emailUrl = `mailto:${client.value.email}?subject=Invoice%20Update&body=Dear%20${client.value.name},`;
  window.location.href = emailUrl;
};

const markContacted = async () => {
  try {
    await axios.post(`/api/clients/${client.value.id}/contacted`);
    client.value.last_contacted_at = new Date().toISOString();
    // Show success message
  } catch (error) {
    console.error('Error marking as contacted:', error);
  }
};

// Utility methods
const getInitials = (name) => {
  return name.split(' ').map(part => part.charAt(0)).join('').toUpperCase().substring(0, 2);
};

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    suspended: 'bg-red-100 text-red-800',
    lead: 'bg-blue-100 text-blue-800',
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
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

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

const formatBusinessType = (type) => {
  const types = {
    sole_proprietorship: 'Sole Proprietorship',
    partnership: 'Partnership',
    corporation: 'Corporation',
    llc: 'LLC',
    non_profit: 'Non-Profit',
    government: 'Government',
    other: 'Other'
  };
  return types[type] || type || 'Not specified';
};

const formatIndustry = (industry) => {
  return industry ? industry.charAt(0).toUpperCase() + industry.slice(1) : 'Not specified';
};

const formatEmployeeCount = (count) => {
  const sizes = {
    '1': '1 (Self-employed)',
    '2-10': '2-10',
    '11-50': '11-50',
    '51-200': '51-200',
    '201-500': '201-500',
    '501-1000': '501-1000',
    '1001+': '1001+'
  };
  return sizes[count] || count || 'Not specified';
};

const formatSource = (source) => {
  const sources = {
    referral: 'Referral',
    website: 'Website',
    social_media: 'Social Media',
    advertising: 'Advertising',
    cold_call: 'Cold Call',
    existing_relationship: 'Existing Relationship',
    other: 'Other'
  };
  return sources[source] || source || 'Not specified';
};

const formatCountry = (country) => {
  const countries = {
    US: 'United States',
    CA: 'Canada',
    GB: 'United Kingdom',
    AU: 'Australia',
    DE: 'Germany',
    FR: 'France',
    JP: 'Japan'
  };
  return countries[country] || country;
};

const formatPaymentTerms = (terms) => {
  const termMap = {
    due_on_receipt: 'Due on Receipt',
    net_7: 'Net 7',
    net_15: 'Net 15',
    net_30: 'Net 30',
    net_45: 'Net 45',
    net_60: 'Net 60'
  };
  return termMap[terms] || terms;
};

onMounted(() => {
  loadClient();
});
</script>
