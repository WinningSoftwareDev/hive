<script setup lang="ts">
  import IPagination from '../../Core/Interface/IPagination';
  import TablePagination from './TablePagination.vue';

  defineProps<{
    items: [];
    pagination?: IPagination;
    loading?: boolean;
  }>();

  const emit = defineEmits(['changePage']);
</script>
<template>
  <div class="bg-secondary-bg rounded-xl border border-gray-800 overflow-hidden shadow-sm">
    <table class="w-full">
      <thead>
        <tr class="bg-black/20 text-left text-xs uppercase text-gray-500 border-b border-gray-800">
          <slot name="headings"></slot>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        <tr v-if="loading && items.length === 0">
          <td
            colspan="100%"
            class="p-12 text-center text-primary animate-pulse text-sm uppercase tracking-widest font-bold">
            Loading Data...
          </td>
        </tr>

        <tr v-else-if="items.length === 0">
          <td colspan="100%" class="p-12 text-center text-gray-500 italic text-sm">
            No records found.
          </td>
        </tr>

        <tr
          v-for="(item, index) in items"
          :key="index"
          class="hover:bg-white/[0.02] transition-colors">
          <slot name="row" :item="item"></slot>
        </tr>
      </tbody>
    </table>

    <TablePagination
      :items="items"
      :loading="loading ?? false"
      :pagination="pagination"
      @changePage="(page) => emit('changePage', page)" />
  </div>
</template>
