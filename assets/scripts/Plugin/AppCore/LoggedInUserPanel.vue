<script setup lang="ts">
  import { ref } from 'vue';
  import IntroCardLink from './IntroCardLink.vue';
  import IUser from '../AuthCore/Interface/IUser';

  enum SendingStatus {
    IDLE,
    SUCCESS,
    ERROR,
  }

  interface IProps {
    user: IUser | null;
    csrfToken: string;
  }

  const props = withDefaults(defineProps<IProps>(), {
    user: null,
  });

  const isSending = ref(false);
  const resendStatus = ref<SendingStatus>(SendingStatus.IDLE);

  interface ISimpleResponse {
    message: string;
    success: boolean;
  }

  const resendEmail = async () => {
    if (isSending.value) {
      return;
    }

    isSending.value = true;
    resendStatus.value = SendingStatus.IDLE;

    try {
      await fetch('/authenticate/resend-verification-email', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          _token: props.csrfToken,
        }),
      })
        .then((response: Response) => {
          return response.json();
        })
        .then((json: ISimpleResponse) => {
          resendStatus.value = json.success ? SendingStatus.SUCCESS : SendingStatus.ERROR;
        });
    } catch {
      resendStatus.value = SendingStatus.ERROR;
    } finally {
      isSending.value = false;
    }
  };
</script>

<template>
  <div v-if="user" class="space-y-4">
    <div
      class="flex items-center gap-3 text-green-400 bg-green-400/10 w-fit px-4 py-2 rounded-full text-sm font-medium">
      <span class="relative flex h-2 w-2">
        <span
          class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
      </span>
      Authenticated as {{ user.email }}
    </div>

    <div
      v-if="!user.verified"
      class="bg-amber-500/10 border border-amber-500/50 p-4 rounded-xl text-amber-200 text-sm">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <span>
          <i class="fa-duotone fa-triangle-exclamation mr-2"></i>
          Please check your inbox to verify your email address.
        </span>

        <button
          @click="resendEmail"
          :disabled="isSending || resendStatus === SendingStatus.SUCCESS"
          class="text-xs font-bold uppercase cursor-pointer tracking-wider bg-amber-500/20 hover:bg-amber-500/30 disabled:opacity-50 disabled:cursor-not-allowed px-3 py-1.5 rounded-lg border border-amber-500/30 transition-all">
          <span v-if="isSending">Sending...</span>
          <span v-else-if="resendStatus === SendingStatus.SUCCESS">Link Sent!</span>
          <span v-else-if="resendStatus === SendingStatus.ERROR">Try Again?</span>
          <span v-else>Resend Email</span>
        </button>
      </div>
    </div>

    <div class="flex gap-4 items-center">
      <a
        href="/user/account"
        class="flex items-center gap-2 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 px-5 py-2 rounded-xl text-sm font-bold transition-all border border-indigo-500/20">
        <i class="fa-duotone fa-objects-column"></i>
        Go to Account Dashboard
      </a>
      <a
        v-if="user.roles.includes('ROLE_ADMIN')"
        href="/admin"
        class="flex items-center gap-2 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 px-5 py-2 rounded-xl text-sm font-bold transition-all border border-indigo-500/20">
        <i class="fa-duotone fa-solid fa-screwdriver-wrench"></i>
        Admin Panel
      </a>
      <IntroCardLink
        url="/authenticate/logout"
        text="Logout"
        class="text-gray-400 hover:text-white" />
    </div>
  </div>
</template>
