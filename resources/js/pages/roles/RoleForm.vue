<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <h2 class="text-xl font-bold mb-4">{{ role ? 'Edit Role' : 'New Role' }}</h2>
        
        <form @submit.prevent="submitForm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name *</label>
              <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Level</label>
              <input v-model="form.level" type="number" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
          </div>
          
          <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="group in permissionGroups" :key="group.name" class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-3">{{ group.name }}</h4>
                <div class="space-y-2">
                  <label
                    v-for="permission in group.permissions"
                    :key="permission.id"
                    class="flex items-center"
                  >
                    <input
                      v-model="form.permissions"
                      :value="permission.name"
                      type="checkbox"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <span class="ml-2 text-sm text-gray-700">{{ permission.description }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button type="button" @click="$emit('cancel')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 border border-gray-300 rounded-md">
              Cancel
            </button>
            <button type="submit" :disabled="loading" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50">
              {{ loading ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  role: Object
});

const emit = defineEmits(['saved', 'cancel']);

const form = reactive({
  name: '',
  description: '',
  level: 50,
  permissions: []
});

const permissionGroups = ref({});
const loading = ref(false);

const fetchPermissions = async () => {
  try {
    const response = await axios.get('/api/permissions');
    permissionGroups.value = response.data;
  } catch (error) {
    console.error('Error fetching permissions:', error);
  }
};

const submitForm = async () => {
  loading.value = true;
  try {
    if (props.role) {
      await axios.put(`/api/roles/${props.role.id}`, form);
    } else {
      await axios.post('/api/roles', form);
    }
    emit('saved');
  } catch (error) {
    console.error('Error saving role:', error);
    alert('Failed to save role');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchPermissions();
  if (props.role) {
    Object.assign(form, {
      name: props.role.name,
      description: props.role.description,
      level: props.role.level,
      permissions: props.role.permissions.map(p => p.name)
    });
  }
});
</script>