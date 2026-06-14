<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import TheHeader from './components/TheHeader.vue'
import AppFooter from './components/AppFooter.vue'

const route = useRoute()
const showHeader = computed(() => route.path !== '/login' && route.path !== '/')
const showFooter = computed(() => route.path !== '/login' && route.path !== '/')
</script>

<template>
  <div class="app-shell">
    <TheHeader v-if="showHeader" />
    <main class="app-main">
      <router-view />
    </main>
    <AppFooter v-if="showFooter" />
  </div>
</template>

<style>
.app-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.app-main {
  flex: 1 1 auto;
}
</style>
