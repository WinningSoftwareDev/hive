<script setup lang="ts">
  import { computed } from 'vue';
  import { useRoute } from 'vue-router';
  import AdminHeader from './Theme/AdminHeader.vue';
  import IRouteMeta from '../Core/Interface/IRouteMeta';
  import SidePanel from './Theme/SidePanel.vue';

  const currentRoute = useRoute();
  const pageTitle = computed(() => {
    const meta = currentRoute.meta as unknown as IRouteMeta;

    return meta.title || 'Administration Panel';
  });
</script>
<template>
  <div class="flex h-screen overflow-hidden bg-page text-light-text">
    <SidePanel />

    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <AdminHeader :pageTitle="pageTitle" />

      <div class="flex-1 overflow-y-auto p-8">
        <router-view />
      </div>
    </main>
  </div>
</template>
