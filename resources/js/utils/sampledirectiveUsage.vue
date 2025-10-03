<template>
  <div>
    <!-- Show based on permission -->
    <button v-can="'invoice.create'">Create Invoice</button>
    
    <!-- Show based on role -->
    <div v-role="'admin'">Admin Only Content</div>
    
    <!-- Conditional rendering in template -->
    <button v-if="authStore.can('invoice.delete')">Delete</button>
  </div>
</template>

<script setup>
import { useAuthStore } from '../store';

const authStore = useAuthStore();
</script>