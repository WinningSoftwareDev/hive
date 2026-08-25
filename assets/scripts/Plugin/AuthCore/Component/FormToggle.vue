<script setup lang="ts">
  interface IProps {
    text: string;
    active: boolean;
  }

  withDefaults(defineProps<IProps>(), {
    active: false,
  });
</script>

<template>
  <span
    class="px-5 py-4 cursor-pointer text-sm uppercase tracking-widest font-bold flex-1 text-center transition-all duration-200"
    :class="{
      'bg-primary text-dark-text shadow-inner': active,
      'text-gray-400 hover:text-white hover:bg-white/5': !active,
    }">
    {{ text }}
  </span>
</template>
