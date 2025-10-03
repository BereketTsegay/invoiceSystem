<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
        <p class="text-gray-600">Manage system users and permissions</p>
      </div>
      <button
        v-if="authStore.can('user.create')"
        @click="showInvitationForm = true"
        class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Invite User
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700">Total Users</h3>
        <p class="text-3xl font-bold text-blue-600">{{ stats.total_users || 0 }}</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700">Pending Invitations</h3>
        <p class="text-3xl font-bold text-yellow-600">{{ stats.pending_invitations || 0 }}</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700">Recent (7 days)</h3>
        <p class="text-3xl font-bold text-green-600">{{ stats.recent_invitations || 0 }}</p>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              User
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Role
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Invited
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Last Login
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in users.data" :key="user.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                  <span class="text-white font-medium">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                  <div class="text-sm text-gray-500">{{ user.email }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ user.roles[0]?.name }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span v-if="user.email_verified_at" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                Active
              </span>
              <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                Pending
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(user.invited_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ user.last_login_at ? formatDate(user.last_login_at) : 'Never' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button
                v-if="!user.email_verified_at && authStore.can('user.edit')"
                @click="resendInvitation(user)"
                class="text-blue-600 hover:text-blue-900"
              >
                Resend
              </button>
              <button
                v-if="authStore.can('user.edit')"
                @click="editUser(user)"
                class="text-green-600 hover:text-green-900"
              >
                Edit
              </button>
              <button
                v-if="authStore.can('user.delete') && user.id !== authStore.user.id"
                @click="deleteUser(user)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="users.data.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No users</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by inviting a new user.</p>
      </div>

      <!-- Pagination -->
      <div v-if="users.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm text-gray-700">
              Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
            </p>
          </div>
          <div class="space-x-2">
            <button
              v-if="users.prev_page_url"
              @click="fetchUsers(users.current_page - 1)"
              class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              v-if="users.next_page_url"
              @click="fetchUsers(users.current_page + 1)"
              class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Invitation Modal -->
    <UserInvitation
      v-if="showInvitationForm"
      @cancel="showInvitationForm = false"
      @success="handleInvitationSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../store';
import axios from 'axios';
import UserInvitation from './UserInvitation.vue';

const authStore = useAuthStore();

const users = ref({ data: [] });
const stats = ref({});
const showInvitationForm = ref(false);
const loading = ref(false);

const fetchUsers = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/users?page=${page}`);
    users.value = response.data;
  } catch (error) {
    console.error('Error fetching users:', error);
  } finally {
    loading.value = false;
  }
};

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/users/stats/invitations');
    stats.value = response.data;
  } catch (error) {
    console.error('Error fetching stats:', error);
  }
};

const resendInvitation = async (user) => {
  if (!confirm(`Resend invitation to ${user.email}?`)) return;

  try {
    await axios.post(`/api/users/${user.id}/resend-invitation`);
    alert('Invitation resent successfully!');
  } catch (error) {
    alert('Failed to resend invitation: ' + (error.response?.data?.message || 'Unknown error'));
  }
};

const editUser = (user) => {
  // Implement user edit functionality
  console.log('Edit user:', user);
};

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) return;

  try {
    await axios.delete(`/api/users/${user.id}`);
    await fetchUsers();
    alert('User deleted successfully!');
  } catch (error) {
    alert('Failed to delete user: ' + (error.response?.data?.message || 'Unknown error'));
  }
};

const handleInvitationSuccess = () => {
  showInvitationForm.value = false;
  fetchUsers();
  fetchStats();
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

onMounted(() => {
  fetchUsers();
  fetchStats();
});
</script>