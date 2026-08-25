<script setup lang="ts">
  import { computed, reactive } from 'vue';
  import AuthForm from './AuthForm.vue';
  import FormField from './FormField.vue';
  import FormSubmit from './FormSubmit.vue';
  import IAuthFormInternalProps from '../Interface/IAuthFormInternalProps';
  import IFormField from '../Interface/IFormField';
  import IntroCardLink from '../../AppCore/IntroCardLink.vue';

  const props = withDefaults(defineProps<IAuthFormInternalProps>(), {});
  const formFields = reactive<Record<string, IFormField>>({
    email: {
      value: 'testuser@example.com',
      errors: [],
    },
    password: {
      value: 'noodlepot',
      errors: [],
    },
    _token: {
      value: props.csrfToken,
      errors: [],
    },
  });
  const email = computed({
    get: () => formFields.email.value,
    set: (val: string) => {
      formFields.email.value = val;
    },
  });
  const password = computed({
    get: () => formFields.password.value,
    set: (val: string) => {
      formFields.password.value = val;
    },
  });

  const getFormData = (): Record<string, IFormField> => {
    return formFields;
  };

  const clearValidationErrors = () => {
    formFields.email.errors = [];
    formFields.password.errors = [];
  };

  const validate = (): boolean => {
    if (formFields.password.value.length < 8) {
      formFields.password.errors.push('Password must contain a minimum of 8 characters.');
    }

    return !formFields.password.errors.length;
  };
  const handleSubmit = async (): Promise<boolean> => {
    clearValidationErrors();

    return validate();
  };
  const handleFailedSubmit = (errors: Record<string, string[]>) => {
    if (errors.email !== undefined) {
      formFields.email.errors.push(...errors.email);
    }
  };
</script>

<template>
  <AuthForm
    :title="title"
    endpoint="/authenticate/login"
    :handler="handleSubmit"
    @submission:failed="handleFailedSubmit"
    :name="name"
    :data="getFormData()"
    :csrfToken="csrfToken">
    <FormField
      type="email"
      label="Email"
      v-model="email"
      :name="`${name}[email]`"
      :id="`${name}_email`"
      :required="true"
      :errors="formFields.email.errors"
      placeholder="Enter Your Email Address"
      @input="clearValidationErrors" />
    <FormField
      type="password"
      label="Password"
      v-model="password"
      :name="`${name}[password]`"
      :id="`${name}_password`"
      :required="true"
      :errors="formFields.password.errors"
      placeholder="Enter Your Password"
      @input="clearValidationErrors" />
    <FormSubmit :text="title" />
    <div class="text-center">
      Forgotten your password?
      <IntroCardLink url="/authenticate/password-reset" text="Request a password reset" />.
    </div>
  </AuthForm>
</template>
