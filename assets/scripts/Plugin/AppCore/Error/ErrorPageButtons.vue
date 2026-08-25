<template>
  <div class="flex flex-col gap-3">
    <a
      href="/"
      class="flex items-center justify-center gap-2 bg-primary hover:bg-accent text-dark-text hover:text-light-text px-6 py-3 rounded-xl font-bold transition-all transform hover:-translate-y-0.5 shadow-lg shadow-primary/20">
      <i class="fa-duotone fa-house"></i>
      Go Home
    </a>

    <button
      onclick="history.back()"
      class="text-sm text-light-text hover:text-white transition duration-150 mt-2 cursor-pointer">
      <i class="fa-light fa-arrow-left mr-1"></i> Go Back
    </button>
  </div>
</template>
