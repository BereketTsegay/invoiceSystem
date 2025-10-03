<template>
  <nav class="bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <h1 class="text-white text-lg font-bold">Invoice System</h1>
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-4">
              <router-link
                to="/"
                class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              >
                Dashboard
              </router-link>
              
              <router-link
                v-if="authStore.can('invoice.view')"
                to="/invoices"
                class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              >
                Invoices
              </router-link>
              
              <router-link
                v-if="authStore.can('client.view')"
                to="/clients"
                class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              >
                Clients
              </router-link>
              
              <router-link
                v-if="authStore.can('report.view')"
                to="/reports"
                class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              >
                Reports
              </router-link>
              
              <router-link
                v-if="authStore.can('user.view')"
                to="/users"
                class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              >
                Users
              </router-link>
              
              <router-link
                v-if="authStore.can('role.view')"
                to="/roles"
                class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
              >
                Roles
              </router-link>
            </div>
          </div>
        </div>
        
        <div class="hidden md:block">
          <div class="ml-4 flex items-center md:ml-6">
            <span class="text-gray-300 mr-4">
              {{ authStore.user?.name }} ({{ authStore.user?.roles[0]?.name }})
            </span>
            <button
              @click="handleLogout"
              class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store';

const router = useRouter();
const authStore = useAuthStore();

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>