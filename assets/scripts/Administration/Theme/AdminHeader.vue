<script setup lang="ts">
  defineProps<{
    pageTitle: string;
  }>();
</script>

<template>
  <header
    class="h-16 shrink-0 border-b border-gray-800 flex justify-between items-center px-8 bg-page/50 backdrop-blur-md">
    <h2 class="font-semibold text-lg">{{ pageTitle }}</h2>

    <div class="flex items-center gap-4">
      <button class="text-gray-400 hover:text-white transition-colors">
        <i class="fas fa-bell"></i>
      </button>
      <a
        href="/authenticate/logout"
        class="text-xs bg-gray-800 hover:bg-gray-700 px-3 py-1.5 rounded transition-colors"
        >Logout</a
      >
    </div>
  </header>
</template>
