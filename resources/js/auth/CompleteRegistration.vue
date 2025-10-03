<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Complete Your Registration
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Welcome, {{ user.name }}! Please set your password to complete your registration.
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="completeRegistration">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              readonly
              class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-500 focus:outline-none sm:text-sm"
            >
          </div>
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              placeholder="Enter your new password"
            >
          </div>
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              placeholder="Confirm your new password"
            >
          </div>
        </div>

        <div v-if="error" class="text-red-600 text-sm text-center bg-red-50 p-3 rounded-md">
          {{ error }}
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="loading">Completing Registration...</span>
            <span v-else>Complete Registration</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../store';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const user = ref({ name: '', email: '' });
const form = reactive({
  email: route.query.email || '',
  password: '',
  password_confirmation: ''
});
const loading = ref(false);
const error = ref('');

onMounted(async () => {
  // Validate the invitation
  if (!route.query.email) {
    error.value = 'Invalid registration link.';
    return;
  }

  try {
    const response = await axios.post('/api/registration/validate', {
      email: route.query.email
    });
    
    if (response.data.valid) {
      user.value = response.data.user;
      form.email = response.data.user.email;
    } else {
      error.value = response.data.message;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Invalid registration link.';
  }
});

const completeRegistration = async () => {
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match.';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const response = await axios.post('/api/registration/complete', form);
    
    // Update auth store
    authStore.user = response.data.user;
    localStorage.setItem('auth_token', response.data.access_token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
    
    // Redirect to dashboard
    router.push('/');
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to complete registration. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>