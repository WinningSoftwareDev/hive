<script setup lang="ts">
  interface IProps {
    type: string;
    label: string;
    placeholder: string;
    name: string;
    id: string;
    required?: boolean;
    errors?: string[];
  }

  const model = defineModel<string>();

  withDefaults(defineProps<IProps>(), {
    placeholder: 'Enter',
    required: false,
  });
</script>

<template>
  <div class="space-y-1.5">
    <label :for="id" class="text-sm font-medium text-gray-300 ml-1">
      {{ label }} <span v-if="required" class="text-primary">*</span>
    </label>
    <input
      :type="type"
      class="block w-full px-4 py-3 rounded-xl border transition-all duration-200 bg-gray-900/50 border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary"
      :class="{ 'border-red-500/50 bg-red-500/5': errors?.length }"
      :name="name"
      :id="id"
      :placeholder="placeholder"
      v-model="model"
      :required="required" />

    <div v-if="errors?.length" class="px-1">
      <p
        v-for="(err, key) in errors"
        :key="key"
        class="text-xs text-red-400 mt-1 flex items-center gap-1">
        <i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ err }}
      </p>
    </div>
  </div>
</template>
