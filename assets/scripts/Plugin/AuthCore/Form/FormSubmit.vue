<script setup lang="ts">
  interface IProps {
    text: string;
  }

  withDefaults(defineProps<IProps>(), {
    text: '',
  });
</script>

<template>
  <button>
    {{ text }}
  </button>
</template>

<style scoped>
  @reference 'tailwindcss/theme';

  button {
    @apply w-full cursor-pointer flex items-center justify-center gap-2 bg-[var(--color-primary)] font-bold px-8 py-3 mt-6
    hover:bg-[var(--color-accent)] text-[var(--color-dark-text)] hover:text-[var(--color-light-text)] rounded-xl transform
    hover:-translate-y-0.5 shadow-lg transition-all shadow-[color-mix(in_srgb,var(--color-primary)_20%,transparent)];
  }
</style>
