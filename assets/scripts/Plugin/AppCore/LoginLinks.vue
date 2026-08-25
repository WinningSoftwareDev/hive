<script setup lang="ts">
  import IUser from '../AuthCore/Interface/IUser';

  interface IProps {
    user: IUser | null;
  }

  withDefaults(defineProps<IProps>(), {
    user: null,
  });
</script>

<template>
  <div v-if="!user">
    <div class="flex flex-col sm:flex-row gap-4">
      <a
        href="/authenticate?form=LoginForm"
        class="button-center bg-primary hover:bg-accent text-dark-text hover:text-light-text transform hover:-translate-y-0.5 shadow-lg shadow-primary/20">
        <i class="fa-duotone fa-right-to-bracket"></i>
        Login to Account
      </a>

      <a
        href="/authenticate?form=RegistrationForm"
        class="button-center bg-gray-800 hover:bg-gray-700 text-white border border-gray-600">
        <i class="fa-duotone fa-user-plus text-gray-400"></i>
        Create Account
      </a>
    </div>
  </div>
</template>

<style scoped>
  @reference 'tailwindcss/theme';

  .button-center {
    @apply flex items-center justify-center gap-2 rounded-xl font-bold transition-all px-8 py-3;
  }
</style>
