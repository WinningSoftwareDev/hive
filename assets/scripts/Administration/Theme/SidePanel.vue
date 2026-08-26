<script setup lang="ts">
  import { onMounted, ref } from 'vue';
  import { IUser } from '../../Plugin/AuthCore/interface';

  const applicationName = ref<string>('');
  const currentUser = ref<IUser>();
  const logoUrl = `${import.meta.env.BASE_URL}favicon.png`;

  onMounted(() => {
    fetch('/api/admin/app-meta')
      .then((response: Response) => {
        return response.json();
      })
      .then((json: { name: string; currentUser: IUser }) => {
        applicationName.value = json.name;
        currentUser.value = json.currentUser;
      });
  });
</script>

<template>
  <aside class="w-64 bg-secondary-bg border-r border-gray-800 flex flex-col">
    <a class="p-6 flex items-center gap-3" href="/">
      <img :src="logoUrl" :alt="`${applicationName} logo`" class="w-7 h-7 object-contain" />
      <span class="font-bold text-xl tracking-tight">{{ applicationName }}</span>
    </a>

    <nav class="flex-1 px-4 space-y-1 mt-4">
      <router-link to="/admin" class="nav-link" active-class="active-nav">
        <i class="fas fa-chart-pie w-5"></i> Dashboard
      </router-link>
    </nav>
  </aside>
</template>
