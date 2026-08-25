<script setup lang="ts">
  import { onMounted, ref } from 'vue';
  import IUser from '../AuthCore/Interface/IUser';
  import WelcomeBlock from './WelcomeBlock.vue';
  import LoggedInUserPanel from './LoggedInUserPanel.vue';
  import LoginLinks from './LoginLinks.vue';

  interface IProps {
    name: string;
    csrfToken: string;
    appVersion: string;
  }

  const user = ref<IUser | null>(null);
  const isLoading = ref<boolean>(true);

  withDefaults(defineProps<IProps>(), {});

  onMounted(() => {
    fetch('/authenticate/current-user')
      .then((response: Response) => {
        return response.json();
      })
      .then((json: IUser) => {
        isLoading.value = false;

        if (!json.email.length) {
          return;
        }

        user.value = json;
      });
  });
</script>

<template>
  <div class="w-full max-w-6xl mx-auto mt-12">
    <div class="space-y-6">
      <div class="bg-gray-800/40 p-8 rounded-2xl border border-gray-700 shadow-xl">
        <WelcomeBlock :appVersion="appVersion" />

        <div class="mt-8 pt-8 border-t border-gray-700/50">
          <LoggedInUserPanel :user="user" :csrfToken="csrfToken" />
          <LoginLinks v-if="!isLoading" :user="user" />
        </div>
      </div>
    </div>
  </div>
</template>
