<template>
  <a href="/" class="return-to-home-button group">
    <i
      class="fa-duotone fa-house text-sm w-4 text-center group-hover:text-primary transition-colors"></i>
    <span
      class="text-[10px] font-black uppercase tracking-[0.2em] leading-none flex items-center pt-[1px]">
      Back
    </span>
    <i class="fa-solid fa-chevron-left"></i>
  </a>
</template>

<style scoped>
  @reference 'tailwindcss/theme';

  .return-to-home-button {
    @apply fixed top-6 left-6 flex items-center justify-center gap-3 py-2 px-5 rounded-full bg-gray-800/40 border
    border-gray-700/50 text-gray-400 hover:text-white hover:bg-gray-800 transition-all duration-300
    backdrop-blur-md shadow-xl hover:border-[color-mix(in_srgb,var(--color-primary)_50%,transparent)];

    i.fa-chevron-left {
      @apply text-[8px] absolute -left-2 opacity-0 group-hover:opacity-100 group-hover:left-2 transition-all duration-300;
    }
  }
</style>
