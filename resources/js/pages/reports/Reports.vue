<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
      <p class="text-gray-600 mt-2">Comprehensive insights into your business performance</p>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
          <select v-model="filters.range" @change="handleDateRangeChange" class="w-full rounded-md border-gray-300 shadow-sm">
            <option value="7days">Last 7 Days</option>
            <option value="30days">Last 30 Days</option>
            <option value="90days">Last 90 Days</option>
            <option value="1year">Last Year</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        <div v-if="filters.range === 'custom'">
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input v-model="filters.date_from" type="date" class="w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div v-if="filters.range === 'custom'">
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input v-model="filters.date_to" type="date" class="w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div class="flex items-end">
          <button
            @click="generateReports"
            class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
          >
            Generate Report
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Invoices</p>
            <p class="text-2xl font-bold text-gray-900">{{ salesReport.total_invoices || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(salesReport.total_revenue || 0) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-yellow-100 rounded-lg">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Average Invoice</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(salesReport.average_invoice || 0) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-purple-100 rounded-lg">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Active Clients</p>
            <p class="text-2xl font-bold text-gray-900">{{ clientReport.total_clients || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts and Detailed Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Sales Trend -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Sales Trend</h3>
        <div v-if="salesReport.daily_data && salesReport.daily_data.length > 0" class="h-64">
          <!-- Simple bar chart using CSS -->
          <div class="flex items-end justify-between h-48 space-x-1">
            <div
              v-for="(day, index) in salesReport.daily_data.slice(-7)"
              :key="index"
              class="flex-1 flex flex-col items-center"
            >
              <div
                class="w-full bg-blue-500 rounded-t transition-all duration-300"
                :style="{ height: (day.total_sales / salesReport.max_sales) * 100 + '%' }"
              ></div>
              <span class="text-xs text-gray-500 mt-1">{{ formatShortDate(day.date) }}</span>
              <span class="text-xs font-medium">{{ formatCurrency(day.total_sales) }}</span>
            </div>
          </div>
        </div>
        <div v-else class="h-64 flex items-center justify-center text-gray-500">
          No data available for the selected period
        </div>
      </div>

      <!-- Client Performance -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Clients</h3>
        <div class="space-y-4">
          <div
            v-for="client in clientReport.top_clients"
            :key="client.id"
            class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0 h-8 w-8 bg-blue-500 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                  {{ client.name.charAt(0).toUpperCase() }}
                </span>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ client.name }}</p>
                <p class="text-xs text-gray-500">{{ client.invoice_count }} invoices</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ formatCurrency(client.total_amount) }}</p>
              <p class="text-xs text-gray-500">Revenue</p>
            </div>
          </div>
        </div>
        <div v-if="!clientReport.top_clients || clientReport.top_clients.length === 0" class="text-center py-8 text-gray-500">
          No client data available
        </div>
      </div>
    </div>

    <!-- Export Options -->
    <div class="mt-6 bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Export Reports</h3>
      <div class="flex space-x-4">
        <button
          v-if="authStore.can('report.export')"
          @click="exportReport('pdf')"
          class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Export PDF
        </button>
        <button
          v-if="authStore.can('report.export')"
          @click="exportReport('excel')"
          class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Export Excel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '../../store';
import axios from 'axios';

const authStore = useAuthStore();

const salesReport = ref({});
const clientReport = ref({});
const loading = ref(false);

const filters = reactive({
  range: '30days',
  date_from: '',
  date_to: ''
});

const handleDateRangeChange = () => {
  const now = new Date();
  switch (filters.range) {
    case '7days':
      filters.date_from = new Date(now.setDate(now.getDate() - 7)).toISOString().split('T')[0];
      filters.date_to = new Date().toISOString().split('T')[0];
      break;
    case '30days':
      filters.date_from = new Date(now.setDate(now.getDate() - 30)).toISOString().split('T')[0];
      filters.date_to = new Date().toISOString().split('T')[0];
      break;
    case '90days':
      filters.date_from = new Date(now.setDate(now.getDate() - 90)).toISOString().split('T')[0];
      filters.date_to = new Date().toISOString().split('T')[0];
      break;
    case '1year':
      filters.date_from = new Date(now.setFullYear(now.getFullYear() - 1)).toISOString().split('T')[0];
      filters.date_to = new Date().toISOString().split('T')[0];
      break;
  }
};

const generateReports = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (filters.date_from) params.append('date_from', filters.date_from);
    if (filters.date_to) params.append('date_to', filters.date_to);

    // Fetch sales report
    const salesResponse = await axios.get(`/api/reports/sales?${params}`);
    salesReport.value = salesResponse.data;

    // Fetch client report
    const clientResponse = await axios.get(`/api/reports/clients?${params}`);
    clientReport.value = clientResponse.data;

  } catch (error) {
    console.error('Error generating reports:', error);
  } finally {
    loading.value = false;
  }
};

const exportReport = async (format) => {
  try {
    const params = new URLSearchParams({ format });
    if (filters.date_from) params.append('date_from', filters.date_from);
    if (filters.date_to) params.append('date_to', filters.date_to);

    const response = await axios.get(`/api/reports/export?${params}`, {
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `report-${new Date().toISOString().split('T')[0]}.${format}`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Error exporting report:', error);
    alert('Failed to export report');
  }
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const formatShortDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
};

onMounted(() => {
  handleDateRangeChange();
  generateReports();
});
</script>