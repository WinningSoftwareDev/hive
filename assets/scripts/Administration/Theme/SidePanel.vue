<script setup lang="ts">
  import { onMounted, ref } from 'vue';

  interface IUserApiResponse {
    id: number;
    email: string;
    createdAt: string;
    verified: boolean;
  }
  const applicationName = ref<string>('');
  const currentUser = ref<IUserApiResponse>();

  onMounted(() => {
    fetch('/api/admin/app-meta')
      .then((response: Response) => {
        return response.json();
      })
      .then((json: { name: string; currentUser: IUserApiResponse }) => {
        applicationName.value = json.name;
        currentUser.value = json.currentUser;
      });
  });
</script>

<template>
  <aside class="w-64 bg-secondary-bg border-r border-gray-800 flex flex-col">
    <a class="p-6 flex items-center gap-3" href="/">
      🚀 <span class="font-bold text-xl tracking-tight">{{ applicationName }}</span>
    </a>

    <nav class="flex-1 px-4 space-y-1 mt-4">
      <router-link to="/admin" class="nav-link" active-class="active-nav">
        <i class="fas fa-chart-pie w-5"></i> Dashboard
      </router-link>

      <router-link to="/admin/users" class="nav-link" active-class="active-nav">
        <i class="fas fa-users w-5"></i> Users
      </router-link>

      <router-link to="/admin/settings" class="nav-link" active-class="active-nav">
        <i class="fa-solid fa-dial"></i> Settings
      </router-link>
    </nav>

    <div class="p-4 border-t border-gray-800 bg-black/20">
      <div class="flex items-center gap-3">
        <div
          class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-xs font-bold text-black">
          <i class="fa-solid fa-user"></i>
        </div>
        <div class="overflow-hidden">
          <p class="text-xs font-medium truncate">{{ currentUser?.email }}</p>
          <p class="text-[10px] text-gray-400 uppercase">Administrator</p>
        </div>
      </div>
    </div>
  </aside>
</template>
