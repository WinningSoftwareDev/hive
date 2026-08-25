<script setup lang="ts">
  import { ref } from 'vue';
  import IAuthFormProps from '../Interface/IAuthFormProps';

  const props = withDefaults(defineProps<IAuthFormProps>(), {});
  const isSubmitting = ref(false);
  const emit = defineEmits(['submission:failed']);

  interface IAuthenticationResponse {
    success: boolean;
    errors: Record<string, string[]>;
    redirect?: string;
  }

  const handleSubmission = async (e: Event) => {
    e.preventDefault();
    isSubmitting.value = true;

    props.handler().then((result: boolean) => {
      if (!result) {
        isSubmitting.value = false;
        emit('submission:failed');
        return;
      }

      const FormData = new URLSearchParams();

      Object.entries(props.data).forEach(([key, value]) => {
        FormData.append(`${props.name}[${key}]`, value.value);
      });

      fetch(props.endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: FormData.toString(),
      })
        .then((response: Response) => {
          return response.json();
        })
        .then((json: IAuthenticationResponse) => {
          if (!json.success) {
            emit('submission:failed', json.errors);
          }

          isSubmitting.value = false;

          if (json.redirect) {
            console.log(json.redirect);
            window.location.href = json.redirect;
          }
        });
    });
  };
</script>

<template>
  <form
    method="POST"
    class="space-y-5 mt-6"
    autocomplete="off"
    @submit.prevent
    @submit="handleSubmission">
    <slot v-if="!isSubmitting"></slot>
    <div v-if="isSubmitting" class="text-center">
      <i class="fa-duotone fa-light mx-auto fa-fan fa-spin fa-3x mt-2"></i>
    </div>
    <input type="text" class="hidden" :name="`${name}[_token]`" :value="csrfToken" />
  </form>
</template>
