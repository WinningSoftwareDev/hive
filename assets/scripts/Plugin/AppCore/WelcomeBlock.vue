<script setup lang="ts">
  interface IProps {
    appVersion: string;
  }

  withDefaults(defineProps<IProps>(), {});
</script>
<template>
  <div class="text-left space-y-4">
    <h1 class="text-4xl font-extrabold text-white tracking-tight">
      Launch your
      <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-accent"
        >ideas faster.</span
      >
    </h1>
    <p class="text-lg text-gray-400 leading-relaxed max-w-3xl">
      This is your new <strong>Symfony Base</strong> project. Pre-configured authentication and a
      modern UI stack; so you can focus on building features instead of boilerplate.
    </p>
    <div class="flex items-center gap-4 text-sm">
      <a
        href="https://github.com/WinningSoftwareDev/symfony-base"
        target="_blank"
        class="flex items-center gap-2 text-indigo-400 hover:text-indigo-300 transition font-semibold">
        <i class="fa-brands fa-github"></i>
        View the Repo
      </a>
      <span class="text-gray-700">|</span>
      <span class="text-gray-500 italic">v{{ appVersion }}</span>
    </div>
    <div>
      <a
        href="/monitor/health"
        class="inline-flex items-center gap-2 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 mt-3 px-5 py-2 rounded-xl text-sm font-bold transition-all border border-indigo-500/20"
        target="_blank">
        <i class="fa-duotone fa-solid fa-book-medical"></i>
        Health Checks
      </a>
    </div>
  </div>
</template>
