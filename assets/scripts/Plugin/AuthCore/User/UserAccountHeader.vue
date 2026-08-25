<script setup lang="ts">
  interface IProps {
    email: string;
  }

  withDefaults(defineProps<IProps>(), {
    email: '',
  });
</script>

<template>
  <div>
    <span class="text-primary uppercase tracking-[0.3em] text-[10px] font-bold mb-2 block"
      >Account Overview</span
    >
    <h1 class="text-4xl font-extrabold text-light-text tracking-tight">
      Welcome back,
      <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-accent"
        >{{ email }}!</span
      >
    </h1>
  </div>
</template>
