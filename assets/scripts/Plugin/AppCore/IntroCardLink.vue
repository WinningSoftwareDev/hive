<script setup lang="ts">
  interface IProps {
    url: string;
    text?: string;
    classes?: string[];
  }

  withDefaults(defineProps<IProps>(), {
    url: '#',
    text: 'Link',
  });
</script>

<template>
  <a :href="url" class="font-medium hover:text-accent transition duration-150">
    <span v-if="text !== 'Link'">{{ text }}</span>
    <slot></slot>
  </a>
</template>
