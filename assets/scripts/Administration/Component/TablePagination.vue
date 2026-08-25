<script setup lang="ts">
  import IPagination from '../../Core/Interface/IPagination';

  defineProps<{
    items: [];
    pagination?: IPagination;
    loading: boolean;
  }>();

  const emit = defineEmits(['changePage']);
</script>

<template>
  <div
    v-if="pagination && pagination.lastPage > 1"
    class="bg-black/10 px-4 py-3 border-t border-gray-800 flex items-center justify-between">
    <div class="text-xs text-gray-500">
      Showing <span class="text-gray-300">{{ items.length }}</span> of
      <span class="text-gray-300">{{ pagination.total }}</span>
    </div>

    <div class="flex gap-2">
      <button
        @click="emit('changePage', pagination.currentPage - 1)"
        :disabled="pagination.currentPage === 1 || loading"
        class="px-3 py-1.5 rounded-md border border-gray-700 text-xs font-medium transition-all disabled:opacity-30 hover:bg-gray-800 cursor-pointer text-gray-300">
        Previous
      </button>

      <div class="flex items-center gap-1">
        <button
          v-for="page in pagination.lastPage"
          :key="page"
          @click="emit('changePage', page)"
          :class="[
            'w-8 h-8 rounded-md text-xs font-bold transition-all border cursor-pointer',
            pagination.currentPage === page
              ? 'bg-primary/10 border-primary/50 text-primary shadow-[0_0_10px_rgba(255,179,102,0.1)]'
              : 'border-transparent text-gray-500 hover:border-gray-700 hover:text-gray-300',
          ]">
          {{ page }}
        </button>
      </div>

      <button
        @click="emit('changePage', pagination.currentPage + 1)"
        :disabled="pagination.currentPage === pagination.lastPage || loading"
        class="px-3 py-1.5 rounded-md border border-gray-700 text-xs font-medium transition-all disabled:opacity-30 hover:bg-gray-800 cursor-pointer text-gray-300">
        Next
      </button>
    </div>
  </div>
</template>
