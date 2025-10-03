<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ item ? 'Edit Item' : 'Add New Item' }}
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

        <!-- Item Form -->
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Description *
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              required
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              placeholder="Enter detailed item description..."
              :class="{ 'border-red-500': errors.description }"
              ref="descriptionInput"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
            <p class="mt-1 text-xs text-gray-500">
              Be specific about the product or service provided
            </p>
          </div>

          <!-- Quantity and Unit Price -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Quantity -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Quantity *
              </label>
              <div class="relative">
                <input
                  v-model="form.quantity"
                  type="number"
                  min="0.01"
                  step="0.01"
                  required
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="1.00"
                  :class="{ 'border-red-500': errors.quantity }"
                >
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">units</span>
                </div>
              </div>
              <p v-if="errors.quantity" class="mt-1 text-sm text-red-600">{{ errors.quantity[0] }}</p>
              <div class="flex space-x-2 mt-2">
                <button
                  type="button"
                  @click="adjustQuantity(1)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  +1
                </button>
                <button
                  type="button"
                  @click="adjustQuantity(0.5)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  +0.5
                </button>
                <button
                  type="button"
                  @click="adjustQuantity(0.25)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  +0.25
                </button>
              </div>
            </div>

            <!-- Unit Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Unit Price *
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  v-model="form.unit_price"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                  class="pl-7 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="0.00"
                  :class="{ 'border-red-500': errors.unit_price }"
                >
              </div>
              <p v-if="errors.unit_price" class="mt-1 text-sm text-red-600">{{ errors.unit_price[0] }}</p>
              <div class="flex space-x-2 mt-2">
                <button
                  type="button"
                  @click="setCommonPrice(10)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  $10
                </button>
                <button
                  type="button"
                  @click="setCommonPrice(25)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  $25
                </button>
                <button
                  type="button"
                  @click="setCommonPrice(50)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  $50
                </button>
                <button
                  type="button"
                  @click="setCommonPrice(100)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  $100
                </button>
              </div>
            </div>
          </div>

          <!-- Tax and Discount -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Tax Rate -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Tax Rate
              </label>
              <div class="relative">
                <input
                  v-model="form.tax_rate"
                  type="number"
                  min="0"
                  max="100"
                  step="0.01"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="0.00"
                  :class="{ 'border-red-500': errors.tax_rate }"
                >
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">%</span>
                </div>
              </div>
              <p v-if="errors.tax_rate" class="mt-1 text-sm text-red-600">{{ errors.tax_rate[0] }}</p>
              <div class="flex space-x-2 mt-2">
                <button
                  type="button"
                  @click="setCommonTax(0)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  0%
                </button>
                <button
                  type="button"
                  @click="setCommonTax(10)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  10%
                </button>
                <button
                  type="button"
                  @click="setCommonTax(15)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  15%
                </button>
                <button
                  type="button"
                  @click="setCommonTax(20)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                >
                  20%
                </button>
              </div>
            </div>

            <!-- Discount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Discount
              </label>
              <div class="space-y-2">
                <div class="flex space-x-2">
                  <div class="flex-1">
                    <input
                      v-model="form.discount"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                      placeholder="0.00"
                      :class="{ 'border-red-500': errors.discount }"
                    >
                  </div>
                  <select
                    v-model="form.discount_type"
                    class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    :class="{ 'border-red-500': errors.discount_type }"
                  >
                    <option value="percentage">%</option>
                    <option value="fixed">$</option>
                  </select>
                </div>
                <p v-if="errors.discount" class="mt-1 text-sm text-red-600">{{ errors.discount[0] }}</p>

                <!-- Discount Presets -->
                <div class="flex flex-wrap gap-1">
                  <button
                    v-if="form.discount_type === 'percentage'"
                    type="button"
                    @click="setCommonDiscount(5)"
                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                  >
                    5%
                  </button>
                  <button
                    v-if="form.discount_type === 'percentage'"
                    type="button"
                    @click="setCommonDiscount(10)"
                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                  >
                    10%
                  </button>
                  <button
                    v-if="form.discount_type === 'percentage'"
                    type="button"
                    @click="setCommonDiscount(15)"
                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                  >
                    15%
                  </button>
                  <button
                    v-if="form.discount_type === 'fixed'"
                    type="button"
                    @click="setCommonDiscount(10)"
                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                  >
                    $10
                  </button>
                  <button
                    v-if="form.discount_type === 'fixed'"
                    type="button"
                    @click="setCommonDiscount(25)"
                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                  >
                    $25
                  </button>
                  <button
                    v-if="form.discount_type === 'fixed'"
                    type="button"
                    @click="setCommonDiscount(50)"
                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded"
                  >
                    $50
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Advanced Options -->
          <div class="border-t border-gray-200 pt-6">
            <button
              type="button"
              @click="showAdvanced = !showAdvanced"
              class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900"
            >
              <span>Advanced Options</span>
              <svg
                class="ml-2 h-5 w-5 transition-transform"
                :class="{ 'rotate-180': showAdvanced }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <div v-if="showAdvanced" class="mt-4 space-y-4">
              <!-- Item Category -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Category
                </label>
                <select
                  v-model="form.category"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                >
                  <option value="">Select Category</option>
                  <option value="product">Product</option>
                  <option value="service">Service</option>
                  <option value="consulting">Consulting</option>
                  <option value="software">Software</option>
                  <option value="hardware">Hardware</option>
                  <option value="maintenance">Maintenance</option>
                  <option value="training">Training</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <!-- SKU/Product Code -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  SKU / Product Code
                </label>
                <input
                  v-model="form.sku"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="Optional product identifier"
                >
              </div>

              <!-- Unit of Measure -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Unit of Measure
                </label>
                <select
                  v-model="form.unit_of_measure"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                >
                  <option value="">Select Unit</option>
                  <option value="hours">Hours</option>
                  <option value="days">Days</option>
                  <option value="units">Units</option>
                  <option value="pieces">Pieces</option>
                  <option value="kg">Kilograms</option>
                  <option value="lb">Pounds</option>
                  <option value="meters">Meters</option>
                  <option value="feet">Feet</option>
                  <option value="liters">Liters</option>
                  <option value="gallons">Gallons</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Price Preview -->
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Price Preview</h4>
            <div class="space-y-2 text-sm">
              <!-- Subtotal -->
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium">{{ formatCurrency(preview.subTotal) }}</span>
              </div>

              <!-- Discount -->
              <div v-if="preview.discountAmount > 0" class="flex justify-between">
                <span class="text-gray-600">Discount:</span>
                <span class="font-medium text-red-600">-{{ formatCurrency(preview.discountAmount) }}</span>
              </div>

              <!-- Tax -->
              <div v-if="preview.taxAmount > 0" class="flex justify-between">
                <span class="text-gray-600">Tax ({{ form.tax_rate }}%):</span>
                <span class="font-medium">{{ formatCurrency(preview.taxAmount) }}</span>
              </div>

              <!-- Total -->
              <div class="flex justify-between font-bold border-t border-gray-200 pt-2">
                <span class="text-gray-900">Total:</span>
                <span class="text-gray-900">{{ formatCurrency(preview.total) }}</span>
              </div>

              <!-- Breakdown -->
              <div class="text-xs text-gray-500 space-y-1 mt-2">
                <div>Quantity: {{ formatNumber(form.quantity) }} × {{ formatCurrency(form.unit_price) }}</div>
                <div v-if="preview.discountAmount > 0">
                  Discount: {{ formatNumber(form.discount) }} {{ form.discount_type === 'percentage' ? '%' : '$' }}
                </div>
                <div>Taxable Amount: {{ formatCurrency(preview.taxableAmount) }}</div>
              </div>
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
                {{ item ? 'Updating...' : 'Creating...' }}
              </span>
              <span v-else>
                {{ item ? 'Update Item' : 'Add Item' }}
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
                  {{ item ? 'Failed to update item' : 'Failed to create item' }}
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
import { reactive, ref, computed, watch, onMounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
  item: Object,
  invoiceId: [Number, String],
  globalTaxRate: {
    type: Number,
    default: 0
  },
  useGlobalTax: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['saved', 'cancel']);

// Form data
const form = reactive({
  description: '',
  quantity: 1,
  unit_price: 0,
  tax_rate: 0,
  discount: 0,
  discount_type: 'percentage',
  category: '',
  sku: '',
  unit_of_measure: ''
});

// UI state
const loading = ref(false);
const error = ref('');
const errors = ref({});
const showAdvanced = ref(false);

// Refs
const descriptionInput = ref(null);

// Calculate preview totals
const preview = computed(() => {
  const quantity = parseFloat(form.quantity) || 0;
  const unitPrice = parseFloat(form.unit_price) || 0;
  const taxRate = parseFloat(form.tax_rate) || 0;
  const discount = parseFloat(form.discount) || 0;

  // Calculate subtotal
  const subTotal = quantity * unitPrice;

  // Calculate discount amount
  const discountAmount = form.discount_type === 'percentage'
    ? subTotal * (discount / 100)
    : discount;

  // Calculate taxable amount after discount
  const taxableAmount = subTotal - discountAmount;

  // Calculate tax amount
  const taxAmount = taxableAmount * (taxRate / 100);

  // Calculate total
  const total = taxableAmount + taxAmount;

  return {
    subTotal,
    discountAmount,
    taxableAmount,
    taxAmount,
    total
  };
});

// Watch form changes and update preview
watch(form, () => {
  // Preview is automatically updated via computed property
}, { deep: true });

// Quick adjustment methods
const adjustQuantity = (amount) => {
  form.quantity = (parseFloat(form.quantity) || 0) + amount;
  if (form.quantity < 0.01) form.quantity = 0.01;
};

const setCommonPrice = (price) => {
  form.unit_price = price;
};

const setCommonTax = (rate) => {
  form.tax_rate = rate;
};

const setCommonDiscount = (discount) => {
  form.discount = discount;
};

const submitForm = async () => {
  if (!validateForm()) return;

  loading.value = true;
  error.value = '';
  errors.value = {};

  try {
    let response;

    if (props.item) {
      // Update existing item
      response = await axios.put(
        `/api/invoices/${props.invoiceId}/items/${props.item.id}`,
        form
      );
    } else {
      // Create new item
      response = await axios.post(
        `/api/invoices/${props.invoiceId}/items`,
        form
      );
    }

    emit('saved', response.data.item);
  } catch (err) {
    handleFormError(err);
  } finally {
    loading.value = false;
  }
};

const validateForm = () => {
  errors.value = {};

  // Required fields
  if (!form.description.trim()) {
    errors.value.description = ['Description is required'];
  }

  if (!form.quantity || form.quantity <= 0) {
    errors.value.quantity = ['Quantity must be greater than 0'];
  }

  if (!form.unit_price && form.unit_price !== 0) {
    errors.value.unit_price = ['Unit price is required'];
  } else if (form.unit_price < 0) {
    errors.value.unit_price = ['Unit price cannot be negative'];
  }

  // Numeric validation
  if (form.tax_rate < 0 || form.tax_rate > 100) {
    errors.value.tax_rate = ['Tax rate must be between 0 and 100'];
  }

  if (form.discount < 0) {
    errors.value.discount = ['Discount cannot be negative'];
  }

  if (form.discount_type === 'percentage' && form.discount > 100) {
    errors.value.discount = ['Discount percentage cannot exceed 100%'];
  }

  return Object.keys(errors.value).length === 0;
};

const handleFormError = (error) => {
  if (error.response?.status === 422) {
    errors.value = error.response.data.errors || {};
    error.value = 'Please fix the validation errors above.';
  } else if (error.response?.status === 404) {
    error.value = 'Invoice not found. Please refresh the page and try again.';
  } else if (error.response?.status === 403) {
    error.value = 'You do not have permission to modify this invoice.';
  } else {
    error.value = error.response?.data?.message ||
      (props.item ? 'Failed to update item.' : 'Failed to create item.');
  }
};

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0);
};

const formatNumber = (number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number || 0);
};

// Initialize form with item data if editing
onMounted(() => {
  if (props.item) {
    Object.assign(form, {
      description: props.item.description,
      quantity: props.item.quantity,
      unit_price: props.item.unit_price,
      tax_rate: props.useGlobalTax ? props.globalTaxRate : props.item.tax_rate,
      discount: props.item.discount,
      discount_type: props.item.discount_type,
      category: props.item.category || '',
      sku: props.item.sku || '',
      unit_of_measure: props.item.unit_of_measure || ''
    });
  } else if (props.useGlobalTax) {
    form.tax_rate = props.globalTaxRate;
  }

  // Focus description input
  nextTick(() => {
    if (descriptionInput.value) {
      descriptionInput.value.focus();
      descriptionInput.value.select();
    }
  });
});

// Auto-fill demo data in development
const fillDemoData = () => {
  if (process.env.NODE_ENV === 'development' && !props.item) {
    const demoItems = [
      {
        description: 'Website Development Services - 40 hours',
        quantity: 40,
        unit_price: 75,
        tax_rate: 10,
        discount: 0,
        category: 'service',
        unit_of_measure: 'hours'
      },
      {
        description: 'Premium Web Hosting - 12 months',
        quantity: 1,
        unit_price: 299,
        tax_rate: 0,
        discount: 10,
        category: 'service',
        unit_of_measure: 'months'
      },
      {
        description: 'Custom WordPress Theme Development',
        quantity: 1,
        unit_price: 1500,
        tax_rate: 10,
        discount: 0,
        category: 'software',
        unit_of_measure: 'units'
      }
    ];

    const randomItem = demoItems[Math.floor(Math.random() * demoItems.length)];
    Object.assign(form, randomItem);
  }
};

// Add demo button in development
if (process.env.NODE_ENV === 'development' && !props.item) {
  onMounted(() => {
    const demoButton = document.createElement('button');
    demoButton.textContent = 'Fill Demo Data';
    demoButton.className = 'fixed bottom-4 left-4 bg-green-500 text-white px-4 py-2 rounded-md text-sm hover:bg-green-600 z-50 shadow-lg';
    demoButton.onclick = fillDemoData;
    document.body.appendChild(demoButton);
  });
}
</script>

<style scoped>
/* Smooth transitions for form elements */
input, textarea, select {
  transition: all 0.2s ease;
}

/* Focus styles */
input:focus, textarea:focus, select:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Loading animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Rotate transition for chevron */
.rotate-180 {
  transform: rotate(180deg);
}

/* Custom styles for price preview */
.bg-gray-50 {
  background-color: #f9fafb;
}

/* Button hover effects */
button {
  transition: all 0.2s ease;
}

/* Quick action buttons */
button.text-xs {
  transition: all 0.15s ease;
}

button.text-xs:hover {
  transform: translateY(-1px);
}
</style>
