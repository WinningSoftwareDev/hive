<script setup lang="ts">
  import RequestPasswordResetForm from '../Form/RequestPasswordResetForm.vue';

  interface IProps {
    csrfToken: string;
  }
  withDefaults(defineProps<IProps>(), {});
</script>

<template>
  <div
    class="border-2 border-gray-800 rounded-md overflow-hidden w-[96vw] mx-auto sm:w-[500px] min-h-[580px]">
    <div class="py-4 px-8">
      <RequestPasswordResetForm
        title="Reset Your Password"
        name="request_password_reset_link_form"
        :csrfToken="csrfToken" />
    </div>
  </div>
</template>
