<script setup lang="ts">
  import img from '../../../../images/bee.png';
</script>

<template>
  <div class="relative inline-block">
    <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full"></div>
    <img
      :src="img"
      class="relative w-64 h-auto mx-auto drop-shadow-2xl animate-float"
      alt="Sad Bee" />
  </div>
</template>

<style scoped>
  @keyframes float {
    0% {
      transform: translateY(0px);
    }
    50% {
      transform: translateY(-15px);
    }
    100% {
      transform: translateY(0px);
    }
  }
  .animate-float {
    animation: float 4s ease-in-out infinite;
  }
</style>
