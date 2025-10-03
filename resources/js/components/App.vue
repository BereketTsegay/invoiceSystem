<template>
  <div id="app">
    <Navigation v-if="authStore.isAuthenticated" />
    <main :class="{ 'p-6': authStore.isAuthenticated }">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '../store';
import Navigation from './layout/Navigation.vue';

const authStore = useAuthStore();

onMounted(() => {
  authStore.init();
});
</script>