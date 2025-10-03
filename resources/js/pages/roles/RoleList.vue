<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
        <p class="text-gray-600">Manage user roles and permissions</p>
      </div>
      <button
        v-if="authStore.can('role.create')"
        @click="showRoleForm = true"
        class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Role
      </button>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="role in roles.data"
        :key="role.id"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ role.name }}</h3>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            Level {{ role.level }}
          </span>
        </div>
        
        <p class="text-sm text-gray-600 mb-4">{{ role.description }}</p>
        
        <div class="mb-4">
          <h4 class="text-sm font-medium text-gray-700 mb-2">Permissions ({{ role.permissions.length }})</h4>
          <div class="space-y-1">
            <span
              v-for="permission in role.permissions.slice(0, 3)"
              :key="permission.id"
              class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1"
            >
              {{ permission.name }}
            </span>
            <span v-if="role.permissions.length > 3" class="text-xs text-gray-500">
              +{{ role.permissions.length - 3 }} more
            </span>
          </div>
        </div>

        <div class="flex space-x-2">
          <button
            v-if="authStore.can('role.edit')"
            @click="editRole(role)"
            class="flex-1 bg-blue-100 text-blue-700 px-3 py-2 rounded-md text-sm hover:bg-blue-200"
          >
            Edit
          </button>
          <button
            v-if="authStore.can('role.delete') && role.name !== 'super-admin'"
            @click="deleteRole(role)"
            class="flex-1 bg-red-100 text-red-700 px-3 py-2 rounded-md text-sm hover:bg-red-200"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="roles.data.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No roles</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating a new role.</p>
    </div>

    <!-- Role Form Modal -->
    <RoleForm
      v-if="showRoleForm"
      :role="selectedRole"
      @saved="handleRoleSaved"
      @cancel="showRoleForm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../store';
import axios from 'axios';
import RoleForm from './RoleForm.vue';

const authStore = useAuthStore();

const roles = ref({ data: [] });
const showRoleForm = ref(false);
const selectedRole = ref(null);

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles');
    roles.value = response.data;
  } catch (error) {
    console.error('Error fetching roles:', error);
  }
};

const editRole = (role) => {
  selectedRole.value = role;
  showRoleForm.value = true;
};

const deleteRole = async (role) => {
  if (!confirm(`Are you sure you want to delete the role "${role.name}"? This action cannot be undone.`)) return;

  try {
    await axios.delete(`/api/roles/${role.id}`);
    alert('Role deleted successfully!');
    fetchRoles();
  } catch (error) {
    alert('Failed to delete role: ' + (error.response?.data?.message || 'Unknown error'));
  }
};

const handleRoleSaved = () => {
  showRoleForm.value = false;
  selectedRole.value = null;
  fetchRoles();
};

onMounted(() => {
  fetchRoles();
});
</script>