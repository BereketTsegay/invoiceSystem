<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ client ? 'Edit Client' : 'Create New Client' }}
          </h2>
          <button
            @click="$emit('cancel')"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Client Form -->
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Basic Information -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Full Name *
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="Enter client's full name"
                  :class="{ 'border-red-500': errors.name }"
                >
                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
              </div>

              <!-- Contact Person -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Contact Person
                </label>
                <input
                  v-model="form.contact_person"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="Primary contact person"
                  :class="{ 'border-red-500': errors.contact_person }"
                >
                <p v-if="errors.contact_person" class="mt-1 text-sm text-red-600">{{ errors.contact_person[0] }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Primary Email *
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="client@example.com"
                  :class="{ 'border-red-500': errors.email }"
                >
                <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
              </div>

              <!-- Secondary Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Secondary Email
                </label>
                <input
                  v-model="form.secondary_email"
                  type="email"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="alternate@example.com"
                  :class="{ 'border-red-500': errors.secondary_email }"
                >
                <p v-if="errors.secondary_email" class="mt-1 text-sm text-red-600">{{ errors.secondary_email[0] }}</p>
              </div>
            </div>

            <!-- Company Name -->
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Company Name
              </label>
              <input
                v-model="form.company_name"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="Enter company name (if applicable)"
                :class="{ 'border-red-500': errors.company_name }"
              >
              <p v-if="errors.company_name" class="mt-1 text-sm text-red-600">{{ errors.company_name[0] }}</p>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Primary Phone -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Primary Phone
                </label>
                <input
                  v-model="form.phone"
                  type="tel"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="+1 (555) 123-4567"
                  :class="{ 'border-red-500': errors.phone }"
                >
                <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</p>
              </div>

              <!-- Secondary Phone -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Secondary Phone
                </label>
                <input
                  v-model="form.secondary_phone"
                  type="tel"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="+1 (555) 123-4568"
                  :class="{ 'border-red-500': errors.secondary_phone }"
                >
                <p v-if="errors.secondary_phone" class="mt-1 text-sm text-red-600">{{ errors.secondary_phone[0] }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
              <!-- Website -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Website
                </label>
                <input
                  v-model="form.website"
                  type="url"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="https://example.com"
                  :class="{ 'border-red-500': errors.website }"
                >
                <p v-if="errors.website" class="mt-1 text-sm text-red-600">{{ errors.website[0] }}</p>
              </div>

              <!-- Tax Number -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Tax Number / VAT ID
                </label>
                <input
                  v-model="form.tax_number"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="Enter tax identification number"
                  :class="{ 'border-red-500': errors.tax_number }"
                >
                <p v-if="errors.tax_number" class="mt-1 text-sm text-red-600">{{ errors.tax_number[0] }}</p>
              </div>
            </div>
          </div>

          <!-- Business Information -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Business Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Business Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Business Type
                </label>
                <select
                  v-model="form.business_type"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  :class="{ 'border-red-500': errors.business_type }"
                >
                  <option value="">Select Business Type</option>
                  <option value="sole_proprietorship">Sole Proprietorship</option>
                  <option value="partnership">Partnership</option>
                  <option value="corporation">Corporation</option>
                  <option value="llc">LLC</option>
                  <option value="non_profit">Non-Profit</option>
                  <option value="government">Government</option>
                  <option value="other">Other</option>
                </select>
                <p v-if="errors.business_type" class="mt-1 text-sm text-red-600">{{ errors.business_type[0] }}</p>
              </div>

              <!-- Industry -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Industry
                </label>
                <select
                  v-model="form.industry"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  :class="{ 'border-red-500': errors.industry }"
                >
                  <option value="">Select Industry</option>
                  <option value="technology">Technology</option>
                  <option value="healthcare">Healthcare</option>
                  <option value="finance">Finance</option>
                  <option value="education">Education</option>
                  <option value="retail">Retail</option>
                  <option value="manufacturing">Manufacturing</option>
                  <option value="construction">Construction</option>
                  <option value="hospitality">Hospitality</option>
                  <option value="transportation">Transportation</option>
                  <option value="other">Other</option>
                </select>
                <p v-if="errors.industry" class="mt-1 text-sm text-red-600">{{ errors.industry[0] }}</p>
              </div>

              <!-- Employee Count -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Employee Count
                </label>
                <select
                  v-model="form.employee_count"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  :class="{ 'border-red-500': errors.employee_count }"
                >
                  <option value="">Select Size</option>
                  <option value="1">1 (Self-employed)</option>
                  <option value="2-10">2-10</option>
                  <option value="11-50">11-50</option>
                  <option value="51-200">51-200</option>
                  <option value="201-500">201-500</option>
                  <option value="501-1000">501-1000</option>
                  <option value="1001+">1001+</option>
                </select>
                <p v-if="errors.employee_count" class="mt-1 text-sm text-red-600">{{ errors.employee_count[0] }}</p>
              </div>
            </div>
          </div>

          <!-- Address Information -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>

            <!-- Street Address -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Street Address
              </label>
              <input
                v-model="form.street_address"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="123 Main Street"
                :class="{ 'border-red-500': errors.street_address }"
              >
              <p v-if="errors.street_address" class="mt-1 text-sm text-red-600">{{ errors.street_address[0] }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- City -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  City
                </label>
                <input
                  v-model="form.city"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="City"
                  :class="{ 'border-red-500': errors.city }"
                >
                <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city[0] }}</p>
              </div>

              <!-- State -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  State/Province
                </label>
                <input
                  v-model="form.state"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="State"
                  :class="{ 'border-red-500': errors.state }"
                >
                <p v-if="errors.state" class="mt-1 text-sm text-red-600">{{ errors.state[0] }}</p>
              </div>

              <!-- Postal Code -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Postal Code
                </label>
                <input
                  v-model="form.postal_code"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="ZIP/Postal Code"
                  :class="{ 'border-red-500': errors.postal_code }"
                >
                <p v-if="errors.postal_code" class="mt-1 text-sm text-red-600">{{ errors.postal_code[0] }}</p>
              </div>

              <!-- Country -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Country
                </label>
                <select
                  v-model="form.country"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  :class="{ 'border-red-500': errors.country }"
                >
                  <option value="US">United States</option>
                  <option value="CA">Canada</option>
                  <option value="GB">United Kingdom</option>
                  <option value="AU">Australia</option>
                  <option value="DE">Germany</option>
                  <option value="FR">France</option>
                  <option value="JP">Japan</option>
                  <option value="other">Other</option>
                </select>
                <p v-if="errors.country" class="mt-1 text-sm text-red-600">{{ errors.country[0] }}</p>
              </div>
            </div>

            <!-- Full Address (Legacy) -->
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Full Address (Legacy)
              </label>
              <textarea
                v-model="form.address"
                rows="2"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="Complete address (for backward compatibility)"
                :class="{ 'border-red-500': errors.address }"
              ></textarea>
              <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address[0] }}</p>
            </div>
          </div>

          <!-- Financial Settings -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Settings</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Currency -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Currency
                </label>
                <select
                  v-model="form.currency"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  :class="{ 'border-red-500': errors.currency }"
                >
                  <option value="USD">USD ($)</option>
                  <option value="EUR">EUR (€)</option>
                  <option value="GBP">GBP (£)</option>
                  <option value="CAD">CAD (C$)</option>
                  <option value="AUD">AUD (A$)</option>
                  <option value="JPY">JPY (¥)</option>
                </select>
                <p v-if="errors.currency" class="mt-1 text-sm text-red-600">{{ errors.currency[0] }}</p>
              </div>

              <!-- Credit Limit -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Credit Limit
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">$</span>
                  </div>
                  <input
                    v-model="form.credit_limit"
                    type="number"
                    min="0"
                    step="0.01"
                    class="pl-7 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="0.00"
                    :class="{ 'border-red-500': errors.credit_limit }"
                  >
                </div>
                <p v-if="errors.credit_limit" class="mt-1 text-sm text-red-600">{{ errors.credit_limit[0] }}</p>
              </div>

              <!-- Payment Terms -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Payment Terms
                </label>
                <select
                  v-model="form.payment_terms"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  :class="{ 'border-red-500': errors.payment_terms }"
                >
                  <option value="due_on_receipt">Due on Receipt</option>
                  <option value="net_7">Net 7</option>
                  <option value="net_15">Net 15</option>
                  <option value="net_30">Net 30</option>
                  <option value="net_45">Net 45</option>
                  <option value="net_60">Net 60</option>
                </select>
                <p v-if="errors.payment_terms" class="mt-1 text-sm text-red-600">{{ errors.payment_terms[0] }}</p>
              </div>
            </div>
          </div>

          <!-- Client Settings -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Client Settings</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Status -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Status
                </label>
                <select
                  v-model="form.status"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  :class="{ 'border-red-500': errors.status }"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="suspended">Suspended</option>
                  <option value="lead">Lead</option>
                </select>
                <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status[0] }}</p>
              </div>

              <!-- Priority -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Priority
                </label>
                <select
                  v-model="form.priority"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  :class="{ 'border-red-500': errors.priority }"
                >
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="vip">VIP</option>
                </select>
                <p v-if="errors.priority" class="mt-1 text-sm text-red-600">{{ errors.priority[0] }}</p>
              </div>

              <!-- Source -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Source
                </label>
                <select
                  v-model="form.source"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  :class="{ 'border-red-500': errors.source }"
                >
                  <option value="">How did they find us?</option>
                  <option value="referral">Referral</option>
                  <option value="website">Website</option>
                  <option value="social_media">Social Media</option>
                  <option value="advertising">Advertising</option>
                  <option value="cold_call">Cold Call</option>
                  <option value="existing_relationship">Existing Relationship</option>
                  <option value="other">Other</option>
                </select>
                <p v-if="errors.source" class="mt-1 text-sm text-red-600">{{ errors.source[0] }}</p>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Notes
              </label>
              <textarea
                v-model="form.notes"
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="Any additional notes about this client..."
                :class="{ 'border-red-500': errors.notes }"
              ></textarea>
              <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes[0] }}</p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="$emit('cancel')"
              class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-6 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ client ? 'Updating...' : 'Creating...' }}
              </span>
              <span v-else>
                {{ client ? 'Update Client' : 'Create Client' }}
              </span>
            </button>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  {{ client ? 'Failed to update client' : 'Failed to create client' }}
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  <p>{{ error }}</p>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  client: Object
});

const emit = defineEmits(['saved', 'cancel']);

const form = reactive({
  // Basic Information
  name: '',
  contact_person: '',
  email: '',
  secondary_email: '',
  phone: '',
  secondary_phone: '',
  company_name: '',
  website: '',
  tax_number: '',

  // Business Information
  business_type: '',
  industry: '',
  employee_count: '',

  // Address Information
  address: '',
  street_address: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'US',

  // Financial Settings
  currency: 'USD',
  credit_limit: null,
  payment_terms: 'net_30',

  // Client Settings
  status: 'active',
  priority: 'medium',
  source: '',

  // Additional Information
  notes: ''
});

const loading = ref(false);
const error = ref('');
const errors = ref({});

// Auto-generate full address from components
const autoAddress = computed(() => {
  const parts = [
    form.street_address,
    form.city,
    form.state,
    form.postal_code,
    form.country
  ].filter(part => part && part.trim());

  return parts.join(', ');
});

// Watch address components and update full address
watch(() => [
  form.street_address,
  form.city,
  form.state,
  form.postal_code,
  form.country
], () => {
  form.address = autoAddress.value;
}, { deep: true });

const submitForm = async () => {
  loading.value = true;
  error.value = '';
  errors.value = {};

  try {
    // Prepare data for submission
    const submitData = { ...form };

    // Convert empty strings to null for numeric fields
    if (submitData.credit_limit === '') {
      submitData.credit_limit = null;
    }
    if (submitData.employee_count === '') {
      submitData.employee_count = null;
    }

    let response;

    if (props.client) {
      // Update existing client
      response = await axios.put(`/api/clients/${props.client.id}`, submitData);
    } else {
      // Create new client
      response = await axios.post('/api/clients', submitData);
    }

    // Show success message
    const action = props.client ? 'updated' : 'created';
    const message = `Client ${action} successfully!`;

    // Emit saved event with the client data
    emit('saved', response.data.client);

    // Reset form if creating new client
    if (!props.client) {
      Object.keys(form).forEach(key => {
        if (key === 'country') {
          form[key] = 'US';
        } else if (key === 'currency') {
          form[key] = 'USD';
        } else if (key === 'payment_terms') {
          form[key] = 'net_30';
        } else if (key === 'status') {
          form[key] = 'active';
        } else if (key === 'priority') {
          form[key] = 'medium';
        } else {
          form[key] = '';
        }
      });
    }

  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {};
      error.value = 'Please fix the validation errors above.';
    } else {
      error.value = err.response?.data?.message ||
        (props.client ? 'Failed to update client.' : 'Failed to create client.');
    }
  } finally {
    loading.value = false;
  }
};

// Auto-fill demo data in development
const fillDemoData = () => {
  if (process.env.NODE_ENV === 'development') {
    Object.assign(form, {
      name: 'John Smith',
      contact_person: 'John Smith',
      email: 'john.smith@example.com',
      secondary_email: 'jsmith@company.com',
      phone: '+1 (555) 123-4567',
      secondary_phone: '+1 (555) 123-4568',
      company_name: 'Smith Enterprises Inc.',
      website: 'https://smithenterprises.com',
      tax_number: 'US123456789',
      business_type: 'corporation',
      industry: 'technology',
      employee_count: '51-200',
      street_address: '123 Business Avenue, Suite 100',
      city: 'New York',
      state: 'NY',
      postal_code: '10001',
      country: 'US',
      currency: 'USD',
      credit_limit: 50000,
      payment_terms: 'net_30',
      status: 'active',
      priority: 'high',
      source: 'referral',
      notes: 'Preferred contact method: Email\nPayment terms: Net 30\nImportant client - handle with care'
    });
  }
};

// Initialize form with client data if editing
onMounted(() => {
  if (props.client) {
    Object.assign(form, props.client);
  }

  // Add demo button in development
  if (process.env.NODE_ENV === 'development' && !props.client) {
    const demoButton = document.createElement('button');
    demoButton.textContent = 'Fill Demo Data';
    demoButton.className = 'fixed bottom-4 left-4 bg-green-500 text-white px-4 py-2 rounded-md text-sm hover:bg-green-600 z-50 shadow-lg';
    demoButton.onclick = fillDemoData;
    document.body.appendChild(demoButton);
  }
});
</script>
