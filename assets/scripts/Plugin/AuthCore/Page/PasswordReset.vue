<script setup lang="ts">
  import PasswordResetForm from '../Form/PasswordResetForm.vue';

  interface IProps {
    token: string;
    csrfToken: string;
  }

  withDefaults(defineProps<IProps>(), {
    token: '',
    csrfToken: '',
  });
</script>

<template>
  <div
    class="border-2 border-gray-800 rounded-md overflow-hidden w-[96vw] mx-auto sm:w-[500px] min-h-[580px]">
    <div class="py-4 px-8">
      <PasswordResetForm
        name="password_reset_form"
        title="Reset Your Password"
        :token="token"
        :csrfToken="csrfToken" />
    </div>
  </div>
</template>
