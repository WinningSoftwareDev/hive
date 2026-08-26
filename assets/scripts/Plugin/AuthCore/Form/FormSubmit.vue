<script setup lang="ts">
  interface IProps {
    text: string;
  }

  withDefaults(defineProps<IProps>(), {
    text: '',
  });
</script>

<template>
  <button :class="[
    'w-full cursor-pointer flex items-center justify-center gap-2 px-8 py-3 mt-6 transform',
    'font-bold bg-primary text-dark-text shadow-lg transition-all rounded-xl',
    'hover:bg-accent hover:text-light-text hover:-translate-y-0.5'
  ]">
    {{ text }}
  </button>
</template>

