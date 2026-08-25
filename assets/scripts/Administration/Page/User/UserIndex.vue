<script setup lang="ts">
  import { ref, onMounted } from 'vue';
  import { OAUTH_PROVIDERS } from '../../../Plugin/AuthCore/Constant/OauthProvider';
  import AdminTable from '../../Component/AdminTable.vue';
  import IPagination from '../../../Core/Interface/IPagination';

  const loading = ref(true);
  const responseData = ref({ data: [], meta: {} });

  const getOauthIcon = (handle: string): string => {
    const provider = OAUTH_PROVIDERS.find((p) => p.service === handle);
    return provider?.icon ?? '';
  };

  const fetchUsers = async (page = 1) => {
    try {
      const params = new URLSearchParams({ page: page.toString(), limit: '10' });
      const response = await fetch(`/api/admin/users?${params}`);
      responseData.value = await response.json();
    } catch (error) {
      console.error('Failed to load users', error);
    } finally {
      loading.value = false;
    }
  };

  onMounted(fetchUsers);
</script>

<template>
  <div>
    <AdminTable
      :items="responseData.data"
      :loading="loading"
      :pagination="responseData.meta as IPagination"
      @changePage="(page: number) => fetchUsers(page)">
      <template #headings>
        <th class="p-4 font-bold">Email</th>
        <th class="p-4 font-bold">Roles</th>
        <th class="p-4 font-bold w-24">OAuth</th>
        <th class="p-4 font-bold text-right">Actions</th>
      </template>
      <template #row="{ item: user }">
        <td class="p-4 text-sm font-medium">{{ user.email }}</td>
        <td class="p-4">
          <div class="flex gap-1">
            <span
              v-for="role in user.roles"
              :key="role"
              class="text-[10px] bg-gray-800/50 text-gray-300 border border-gray-700 px-2 py-0.5 rounded uppercase tracking-tighter">
              {{ role.replace('ROLE_', '') }}
            </span>
          </div>
        </td>
        <td class="p-4">
          <div v-if="user.oauthProviders?.length" class="flex gap-2">
            <i
              v-for="handle in user.oauthProviders"
              :key="handle"
              :class="[getOauthIcon(handle), 'text-base text-gray-400']"
              :title="handle" />
          </div>
          <span v-else class="text-[10px] text-gray-600">—</span>
        </td>
        <td class="p-4 text-right">
          <button
            class="text-primary hover:text-accent transition-colors text-sm font-semibold cursor-pointer">
            Edit
          </button>
        </td>
      </template>
    </AdminTable>
  </div>
</template>
