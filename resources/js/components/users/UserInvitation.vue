<template>
  <div class="max-w-2xl mx-auto p-6">
    <div class="bg-white shadow-md rounded-lg p-6">
      <h2 class="text-2xl font-bold mb-6">Invite New User</h2>
      
      <form @submit.prevent="sendInvitation" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Full Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Enter full name"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Email Address *</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Enter email address"
            >
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Role *</label>
          <select
            v-model="form.role"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">Select Role</option>
            <option v-for="role in roles" :key="role.id" :value="role.name">
              {{ role.name }}
            </option>
          </select>
        </div>

        <div class="flex items-center">
          <input
            v-model="form.send_invitation"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          >
          <label class="ml-2 block text-sm text-gray-900">
            Send email invitation
          </label>
        </div>

        <div v-if="!form.send_invitation" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <p class="text-yellow-800 text-sm">
            If you don't send an email invitation, you'll need to manually share the temporary password with the user.
          </p>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
          <p class="text-red-800 text-sm">{{ error }}</p>
        </div>

        <div class="flex justify-end space-x-4">
          <button
            type="button"
            @click="$emit('cancel')"
            class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 disabled:opacity-50"
          >
            {{ loading ? 'Sending...' : 'Send Invitation' }}
          </button>
        </div>
      </form>

      <!-- Success Modal -->
      <div v-if="success" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
          <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
              <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Invitation Sent!</h3>
            <p class="text-sm text-gray-500 mt-2">
              {{ successMessage }}
            </p>
            
            <div v-if="tempPassword" class="mt-4 p-4 bg-gray-100 rounded-md">
              <p class="text-sm font-medium text-gray-700">Temporary Password:</p>
              <p class="text-lg font-mono text-gray-900">{{ tempPassword }}</p>
              <p class="text-xs text-gray-500 mt-2">Please share this password securely with the user.</p>
            </div>
            
            <div class="mt-6">
              <button
                @click="closeSuccess"
                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['cancel', 'success']);

const form = reactive({
  name: '',
  email: '',
  role: '',
  send_invitation: true
});

const roles = ref([]);
const loading = ref(false);
const error = ref('');
const success = ref(false);
const successMessage = ref('');
const tempPassword = ref('');

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles');
    roles.value = response.data.data;
  } catch (err) {
    console.error('Error fetching roles:', err);
  }
};

const sendInvitation = async () => {
  loading.value = true;
  error.value = '';

  try {
    const response = await axios.post('/api/users/invite', form);
    
    successMessage.value = response.data.message;
    tempPassword.value = response.data.temp_password || null;
    success.value = true;
    
    // Reset form
    Object.assign(form, {
      name: '',
      email: '',
      role: '',
      send_invitation: true
    });
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to send invitation. Please try again.';
  } finally {
    loading.value = false;
  }
};

const closeSuccess = () => {
  success.value = false;
  tempPassword.value = '';
  emit('success');
};

onMounted(() => {
  fetchRoles();
});
</script>