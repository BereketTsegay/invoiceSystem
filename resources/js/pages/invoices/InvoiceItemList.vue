<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">Invoice Items</h3>
        <div class="flex items-center space-x-3">
          <!-- Tax Rate Toggle -->
          <div class="flex items-center">
            <input
              id="global-tax"
              v-model="useGlobalTax"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label for="global-tax" class="ml-2 block text-sm text-gray-700">
              Apply {{ globalTaxRate }}% tax to all items
            </label>
          </div>

          <!-- Add Item Button -->
          <button
            v-if="editable"
            @click="addItem"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Item
          </button>
        </div>
      </div>
    </div>

    <!-- Items Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
              #
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Description
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
              Qty
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
              Unit Price
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
              Tax
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
              Discount
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
              Total
            </th>
            <th v-if="editable" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Loading State -->
          <tr v-if="loading">
            <td colspan="8" class="px-6 py-8 text-center">
              <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
              <p class="mt-2 text-sm text-gray-500">Loading items...</p>
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-else-if="items.length === 0">
            <td colspan="8" class="px-6 py-12 text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No items</h3>
              <p class="mt-1 text-sm text-gray-500">Get started by adding your first item.</p>
              <div class="mt-6" v-if="editable">
                <button
                  @click="addItem"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                  <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                  Add Item
                </button>
              </div>
            </td>
          </tr>

          <!-- Items -->
          <tr
            v-for="(item, index) in items"
            :key="item.id || `new-${index}`"
            class="hover:bg-gray-50 transition-colors"
            :class="{ 'bg-yellow-50': item._destroy }"
          >
            <!-- Item Number -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ index + 1 }}
            </td>

            <!-- Description -->
            <td class="px-6 py-4 text-sm text-gray-900 max-w-md">
              <div v-if="editable && item.editing" class="space-y-2">
                <textarea
                  v-model="item.description"
                  @blur="updateItem(item)"
                  @keydown.enter="updateItem(item)"
                  rows="2"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="Item description"
                  ref="descriptionInputs"
                ></textarea>
                <div v-if="item.errors && item.errors.description" class="text-red-500 text-xs">
                  {{ item.errors.description[0] }}
                </div>
              </div>
              <div v-else>
                <p class="font-medium text-gray-900">{{ item.description || 'No description' }}</p>
                <div v-if="editable" class="mt-1">
                  <button
                    @click="startEdit(item)"
                    class="text-blue-600 hover:text-blue-900 text-xs"
                  >
                    Edit
                  </button>
                </div>
              </div>
            </td>

            <!-- Quantity -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="editable && item.editing" class="space-y-2">
                <input
                  v-model="item.quantity"
                  @blur="updateItem(item)"
                  @keydown.enter="updateItem(item)"
                  type="number"
                  min="0.01"
                  step="0.01"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                >
                <div v-if="item.errors && item.errors.quantity" class="text-red-500 text-xs">
                  {{ item.errors.quantity[0] }}
                </div>
              </div>
              <span v-else class="text-sm text-gray-900">{{ formatNumber(item.quantity) }}</span>
            </td>

            <!-- Unit Price -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="editable && item.editing" class="space-y-2">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">$</span>
                  </div>
                  <input
                    v-model="item.unit_price"
                    @blur="updateItem(item)"
                    @keydown.enter="updateItem(item)"
                    type="number"
                    min="0"
                    step="0.01"
                    class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  >
                </div>
                <div v-if="item.errors && item.errors.unit_price" class="text-red-500 text-xs">
                  {{ item.errors.unit_price[0] }}
                </div>
              </div>
              <span v-else class="text-sm text-gray-900">{{ formatCurrency(item.unit_price) }}</span>
            </td>

            <!-- Tax Rate -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="editable && item.editing" class="space-y-2">
                <div class="relative">
                  <input
                    v-model="item.tax_rate"
                    @blur="updateItem(item)"
                    @keydown.enter="updateItem(item)"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm pr-8"
                    :disabled="useGlobalTax"
                  >
                  <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">%</span>
                  </span>
                </div>
                <div v-if="item.errors && item.errors.tax_rate" class="text-red-500 text-xs">
                  {{ item.errors.tax_rate[0] }}
                </div>
              </div>
              <span v-else class="text-sm text-gray-900">{{ formatNumber(item.tax_rate) }}%</span>
            </td>

            <!-- Discount -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="editable && item.editing" class="space-y-2">
                <div class="flex space-x-2">
                  <div class="flex-1">
                    <input
                      v-model="item.discount"
                      @blur="updateItem(item)"
                      @keydown.enter="updateItem(item)"
                      type="number"
                      min="0"
                      step="0.01"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    >
                  </div>
                  <select
                    v-model="item.discount_type"
                    @change="updateItem(item)"
                    class="block w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  >
                    <option value="percentage">%</option>
                    <option value="fixed">$</option>
                  </select>
                </div>
                <div v-if="item.errors && item.errors.discount" class="text-red-500 text-xs">
                  {{ item.errors.discount[0] }}
                </div>
              </div>
              <span v-else class="text-sm text-gray-900">
                <span v-if="item.discount > 0">
                  {{ formatNumber(item.discount) }} {{ item.discount_type === 'percentage' ? '%' : '$' }}
                </span>
                <span v-else class="text-gray-400">-</span>
              </span>
            </td>

            <!-- Total -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ formatCurrency(calculateItemTotal(item)) }}
              <div v-if="item.editing" class="text-xs text-gray-500 mt-1">
                <div>Sub: {{ formatCurrency(calculateItemSubtotal(item)) }}</div>
                <div>Tax: {{ formatCurrency(calculateItemTax(item)) }}</div>
              </div>
            </td>

            <!-- Actions -->
            <td v-if="editable" class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <template v-if="!item.editing">
                <button
                  @click="startEdit(item)"
                  class="text-blue-600 hover:text-blue-900"
                  title="Edit item"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button
                  @click="duplicateItem(item)"
                  class="text-green-600 hover:text-green-900"
                  title="Duplicate item"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                  </svg>
                </button>
                <button
                  @click="toggleDeleteItem(item)"
                  class="text-red-600 hover:text-red-900"
                  :title="item._destroy ? 'Restore item' : 'Delete item'"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="!item._destroy" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                </button>
              </template>
              <template v-else>
                <button
                  @click="saveItem(item)"
                  class="text-green-600 hover:text-green-900"
                  title="Save item"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </button>
                <button
                  @click="cancelEdit(item)"
                  class="text-gray-600 hover:text-gray-900"
                  title="Cancel edit"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Quick Add Item -->
    <div v-if="editable && showQuickAdd" class="border-t border-gray-200 bg-gray-50 p-4">
      <div class="grid grid-cols-12 gap-2 items-end">
        <div class="col-span-5">
          <input
            v-model="quickAddItem.description"
            type="text"
            placeholder="Item description"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            @keydown.enter="addQuickItem"
          >
        </div>
        <div class="col-span-1">
          <input
            v-model="quickAddItem.quantity"
            type="number"
            min="0.01"
            step="0.01"
            placeholder="Qty"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            @keydown.enter="addQuickItem"
          >
        </div>
        <div class="col-span-2">
          <input
            v-model="quickAddItem.unit_price"
            type="number"
            min="0"
            step="0.01"
            placeholder="Price"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            @keydown.enter="addQuickItem"
          >
        </div>
        <div class="col-span-2">
          <button
            @click="addQuickItem"
            class="w-full bg-green-500 text-white px-3 py-2 rounded-md text-sm hover:bg-green-600"
          >
            Add Item
          </button>
        </div>
        <div class="col-span-2">
          <button
            @click="showQuickAdd = false"
            class="w-full bg-gray-500 text-white px-3 py-2 rounded-md text-sm hover:bg-gray-600"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Totals Section -->
    <div v-if="items.length > 0" class="border-t border-gray-200 bg-gray-50">
      <div class="px-6 py-4">
        <div class="flex justify-end">
          <div class="w-80 space-y-3">
            <!-- Subtotal -->
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Subtotal:</span>
              <span class="font-medium">{{ formatCurrency(totals.subTotal) }}</span>
            </div>

            <!-- Discount Total -->
            <div v-if="totals.discountAmount > 0" class="flex justify-between text-sm">
              <span class="text-gray-600">Discount:</span>
              <span class="font-medium text-red-600">-{{ formatCurrency(totals.discountAmount) }}</span>
            </div>

            <!-- Tax Breakdown -->
            <div v-if="Object.keys(taxBreakdown).length > 0" class="border-t border-gray-200 pt-2">
              <div
                v-for="(amount, rate) in taxBreakdown"
                :key="rate"
                class="flex justify-between text-sm"
              >
                <span class="text-gray-600">Tax ({{ rate }}%):</span>
                <span class="font-medium">{{ formatCurrency(amount) }}</span>
              </div>
            </div>

            <!-- Tax Total -->
            <div class="flex justify-between text-sm border-t border-gray-200 pt-2">
              <span class="text-gray-600">Total Tax:</span>
              <span class="font-medium">{{ formatCurrency(totals.taxAmount) }}</span>
            </div>

            <!-- Grand Total -->
            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
              <span class="text-gray-900">Total:</span>
              <span class="text-gray-900">{{ formatCurrency(totals.grandTotal) }}</span>
            </div>

            <!-- Item Count -->
            <div class="flex justify-between text-xs text-gray-500 mt-2">
              <span>Items:</span>
              <span>{{ items.length }} item(s)</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions Footer -->
    <div v-if="editable" class="px-6 py-4 border-t border-gray-200 bg-white">
      <div class="flex justify-between items-center">
        <div class="flex space-x-3">
          <button
            @click="showQuickAdd = !showQuickAdd"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            {{ showQuickAdd ? 'Hide Quick Add' : 'Quick Add Item' }}
          </button>
          <button
            @click="addMultipleItems"
            class="text-green-600 hover:text-green-800 text-sm font-medium"
          >
            Add Multiple Items
          </button>
        </div>
        <div class="flex space-x-3">
          <button
            v-if="hasDeletedItems"
            @click="restoreAllItems"
            class="text-yellow-600 hover:text-yellow-800 text-sm font-medium"
          >
            Restore All ({{ deletedItemsCount }})
          </button>
          <button
            @click="saveAllItems"
            :disabled="savingAll"
            class="bg-blue-500 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-600 disabled:opacity-50"
          >
            {{ savingAll ? 'Saving...' : 'Save All Changes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Item Form Modal -->
    <InvoiceItemForm
      v-if="showItemForm"
      :item="selectedItem"
      :invoiceId="invoiceId"
      @saved="handleItemSaved"
      @cancel="showItemForm = false"
    />

    <!-- Multiple Items Modal -->
    <div v-if="showMultipleItemsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h3 class="text-lg font-bold mb-4">Add Multiple Items</h3>
          <div class="space-y-4">
            <div v-for="(multiItem, index) in multipleItems" :key="index" class="grid grid-cols-12 gap-4 items-end border-b border-gray-200 pb-4">
              <div class="col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input
                  v-model="multiItem.description"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  placeholder="Item description"
                >
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input
                  v-model="multiItem.quantity"
                  type="number"
                  min="0.01"
                  step="0.01"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                >
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                <input
                  v-model="multiItem.unit_price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                >
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate %</label>
                <input
                  v-model="multiItem.tax_rate"
                  type="number"
                  min="0"
                  max="100"
                  step="0.01"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                >
              </div>
              <div class="col-span-1">
                <button
                  @click="removeMultipleItem(index)"
                  class="w-full bg-red-500 text-white p-2 rounded-md hover:bg-red-600"
                >
                  <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>

            <button
              @click="addMultipleItemRow"
              class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600"
            >
              Add Another Item
            </button>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button
              @click="showMultipleItemsModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 border border-gray-300 rounded-md"
            >
              Cancel
            </button>
            <button
              @click="saveMultipleItems"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md"
            >
              Add All Items
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import InvoiceItemForm from './InvoiceItemForm.vue';

const props = defineProps({
  invoiceId: {
    type: [Number, String],
    required: true
  },
  editable: {
    type: Boolean,
    default: false
  },
  initialItems: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['items-updated', 'totals-changed']);

// State
const items = ref([]);
const loading = ref(false);
const showItemForm = ref(false);
const selectedItem = ref(null);
const savingAll = ref(false);
const showQuickAdd = ref(false);
const showMultipleItemsModal = ref(false);

// Global tax settings
const useGlobalTax = ref(false);
const globalTaxRate = ref(10); // Default 10% tax

// Quick add item
const quickAddItem = reactive({
  description: '',
  quantity: 1,
  unit_price: 0,
  tax_rate: 0,
  discount: 0,
  discount_type: 'percentage'
});

// Multiple items
const multipleItems = ref([{
  description: '',
  quantity: 1,
  unit_price: 0,
  tax_rate: 0
}]);

// Refs for focusing inputs
const descriptionInputs = ref([]);

// Computed properties
const totals = computed(() => {
  const subTotal = items.value.reduce((sum, item) => {
    if (item._destroy) return sum;
    return sum + calculateItemSubtotal(item);
  }, 0);

  const taxAmount = items.value.reduce((sum, item) => {
    if (item._destroy) return sum;
    return sum + calculateItemTax(item);
  }, 0);

  const discountAmount = items.value.reduce((sum, item) => {
    if (item._destroy) return sum;
    return sum + calculateItemDiscount(item);
  }, 0);

  const grandTotal = subTotal - discountAmount + taxAmount;

  return {
    subTotal,
    taxAmount,
    discountAmount,
    grandTotal
  };
});

const taxBreakdown = computed(() => {
  const breakdown = {};
  items.value.forEach(item => {
    if (item._destroy) return;
    const rate = useGlobalTax.value ? globalTaxRate.value : item.tax_rate;
    if (rate > 0) {
      const tax = calculateItemTax(item);
      if (!breakdown[rate]) {
        breakdown[rate] = 0;
      }
      breakdown[rate] += tax;
    }
  });
  return breakdown;
});

const hasDeletedItems = computed(() => {
  return items.value.some(item => item._destroy);
});

const deletedItemsCount = computed(() => {
  return items.value.filter(item => item._destroy).length;
});

// Watch totals and emit changes
watch(totals, (newTotals) => {
  emit('totals-changed', newTotals);
});

// Watch global tax and update items
watch(useGlobalTax, (newValue) => {
  if (newValue) {
    items.value.forEach(item => {
      if (!item._destroy) {
        item.tax_rate = globalTaxRate.value;
        updateItem(item);
      }
    });
  }
});

watch(globalTaxRate, (newRate) => {
  if (useGlobalTax.value) {
    items.value.forEach(item => {
      if (!item._destroy) {
        item.tax_rate = newRate;
        updateItem(item);
      }
    });
  }
});

// Methods
const loadItems = async () => {
  if (!props.invoiceId) return;

  loading.value = true;
  try {
    const response = await axios.get(`/api/invoices/${props.invoiceId}/items`);
    items.value = response.data.items.map(item => ({
      ...item,
      editing: false,
      errors: {}
    }));
    emit('totals-changed', response.data);
  } catch (error) {
    console.error('Error loading invoice items:', error);
  } finally {
    loading.value = false;
  }
};

const addItem = () => {
  selectedItem.value = null;
  showItemForm.value = true;
};

const startEdit = (item) => {
  // Store original values for cancel
  item.originalValues = { ...item };
  item.editing = true;
  item.errors = {};

  nextTick(() => {
    // Focus the description input
    const index = items.value.indexOf(item);
    if (descriptionInputs.value[index]) {
      descriptionInputs.value[index].focus();
    }
  });
};

const cancelEdit = (item) => {
  if (item.originalValues) {
    Object.assign(item, item.originalValues);
  }
  item.editing = false;
  item.errors = {};
  delete item.originalValues;
};

const saveItem = async (item) => {
  await updateItem(item);
};

const updateItem = async (item) => {
  if (!item.editing) return;

  // Validate required fields
  item.errors = {};
  if (!item.description?.trim()) {
    item.errors.description = ['Description is required'];
  }
  if (!item.quantity || item.quantity <= 0) {
    item.errors.quantity = ['Quantity must be greater than 0'];
  }
  if (!item.unit_price || item.unit_price < 0) {
    item.errors.unit_price = ['Unit price must be 0 or greater'];
  }

  if (Object.keys(item.errors).length > 0) {
    return;
  }

  item.editing = false;

  if (!item.id) {
    // This is a new item being edited inline
    await createItem(item);
    return;
  }

  try {
    const response = await axios.put(
      `/api/invoices/${props.invoiceId}/items/${item.id}`,
      item
    );

    Object.assign(item, response.data.item);
    item.errors = {};
    emit('items-updated');
  } catch (error) {
    console.error('Error updating item:', error);
    if (error.response?.status === 422) {
      item.errors = error.response.data.errors || {};
    } else {
      alert('Failed to update item. Please try again.');
    }
  }
};

const createItem = async (itemData) => {
  try {
    const response = await axios.post(
      `/api/invoices/${props.invoiceId}/items`,
      itemData
    );

    items.value.push({
      ...response.data.item,
      editing: false,
      errors: {}
    });
    emit('items-updated');
  } catch (error) {
    console.error('Error creating item:', error);
    alert('Failed to create item. Please try again.');
  }
};

const duplicateItem = async (item) => {
  try {
    const response = await axios.post(
      `/api/invoices/${props.invoiceId}/items/${item.id}/duplicate`
    );

    items.value.push({
      ...response.data.item,
      editing: false,
      errors: {}
    });
    emit('items-updated');
  } catch (error) {
    console.error('Error duplicating item:', error);
    alert('Failed to duplicate item. Please try again.');
  }
};

const toggleDeleteItem = (item) => {
  if (item._destroy) {
    // Restore item
    delete item._destroy;
  } else {
    // Mark for deletion
    item._destroy = true;
    item.editing = false;
  }
  emit('items-updated');
};

const restoreAllItems = () => {
  items.value.forEach(item => {
    if (item._destroy) {
      delete item._destroy;
    }
  });
  emit('items-updated');
};

const saveAllItems = async () => {
  savingAll.value = true;
  try {
    // Save all items that are being edited
    for (const item of items.value) {
      if (item.editing && !item._destroy) {
        await updateItem(item);
      }
    }

    // Perform bulk update for all items
    const itemsToUpdate = items.value.map(item => ({
      id: item.id,
      description: item.description,
      quantity: item.quantity,
      unit_price: item.unit_price,
      tax_rate: useGlobalTax.value ? globalTaxRate.value : item.tax_rate,
      discount: item.discount,
      discount_type: item.discount_type,
      _destroy: item._destroy
    }));

    const response = await axios.post(
      `/api/invoices/${props.invoiceId}/items/bulk-update`,
      { items: itemsToUpdate }
    );

    items.value = response.data.items.map(item => ({
      ...item,
      editing: false,
      errors: {}
    }));

    emit('items-updated');
  } catch (error) {
    console.error('Error saving all items:', error);
    alert('Failed to save items. Please try again.');
  } finally {
    savingAll.value = false;
  }
};

// Quick add functionality
const addQuickItem = () => {
  if (!quickAddItem.description.trim()) {
    alert('Please enter an item description');
    return;
  }

  const newItem = {
    description: quickAddItem.description,
    quantity: parseFloat(quickAddItem.quantity) || 1,
    unit_price: parseFloat(quickAddItem.unit_price) || 0,
    tax_rate: useGlobalTax.value ? globalTaxRate.value : 0,
    discount: 0,
    discount_type: 'percentage',
    editing: true
  };

  items.value.push(newItem);

  // Reset quick add form
  quickAddItem.description = '';
  quickAddItem.quantity = 1;
  quickAddItem.unit_price = 0;

  emit('items-updated');
};

// Multiple items functionality
const addMultipleItems = () => {
  showMultipleItemsModal.value = true;
  multipleItems.value = [{
    description: '',
    quantity: 1,
    unit_price: 0,
    tax_rate: useGlobalTax.value ? globalTaxRate.value : 0
  }];
};

const addMultipleItemRow = () => {
  multipleItems.value.push({
    description: '',
    quantity: 1,
    unit_price: 0,
    tax_rate: useGlobalTax.value ? globalTaxRate.value : 0
  });
};

const removeMultipleItem = (index) => {
  if (multipleItems.value.length > 1) {
    multipleItems.value.splice(index, 1);
  } else {
    // Reset the single row instead of removing it
    multipleItems.value[0] = {
      description: '',
      quantity: 1,
      unit_price: 0,
      tax_rate: useGlobalTax.value ? globalTaxRate.value : 0
    };
  }
};

const saveMultipleItems = async () => {
  const validItems = multipleItems.value.filter(item =>
    item.description.trim() &&
    item.quantity > 0 &&
    item.unit_price >= 0
  );

  if (validItems.length === 0) {
    alert('Please add at least one valid item');
    return;
  }

  try {
    for (const itemData of validItems) {
      await createItem(itemData);
    }

    showMultipleItemsModal.value = false;
    multipleItems.value = [{
      description: '',
      quantity: 1,
      unit_price: 0,
      tax_rate: useGlobalTax.value ? globalTaxRate.value : 0
    }];
  } catch (error) {
    console.error('Error creating multiple items:', error);
    alert('Failed to add items. Please try again.');
  }
};

const handleItemSaved = (newItem) => {
  showItemForm.value = false;
  selectedItem.value = null;

  const existingIndex = items.value.findIndex(item => item.id === newItem.id);
  if (existingIndex >= 0) {
    items.value[existingIndex] = {
      ...newItem,
      editing: false,
      errors: {}
    };
  } else {
    items.value.push({
      ...newItem,
      editing: false,
      errors: {}
    });
  }

  emit('items-updated');
};

// Calculation functions
const calculateItemSubtotal = (item) => {
  return (item.quantity || 0) * (item.unit_price || 0);
};

const calculateItemDiscount = (item) => {
  const subtotal = calculateItemSubtotal(item);
  if (item.discount_type === 'percentage') {
    return subtotal * ((item.discount || 0) / 100);
  } else {
    return item.discount || 0;
  }
};

const calculateItemTax = (item) => {
  const subtotal = calculateItemSubtotal(item);
  const discount = calculateItemDiscount(item);
  const taxableAmount = subtotal - discount;
  const taxRate = useGlobalTax.value ? globalTaxRate.value : (item.tax_rate || 0);
  return taxableAmount * (taxRate / 100);
};

const calculateItemTotal = (item) => {
  const subtotal = calculateItemSubtotal(item);
  const discount = calculateItemDiscount(item);
  const tax = calculateItemTax(item);
  return subtotal - discount + tax;
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

// Initialize
onMounted(() => {
  if (props.initialItems && props.initialItems.length > 0) {
    items.value = props.initialItems.map(item => ({
      ...item,
      editing: false,
      errors: {}
    }));
  } else {
    loadItems();
  }
});

// Watch for invoiceId changes
watch(() => props.invoiceId, () => {
  loadItems();
});
</script>

<style scoped>
/* Custom styles for better UX */
.bg-yellow-50 {
  background-color: #fffbeb;
}

/* Smooth transitions */
tr {
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
</style>
