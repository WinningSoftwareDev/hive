<script setup lang="ts">
  import { computed, reactive } from 'vue';
  import AuthForm from './AuthForm.vue';
  import FormField from './FormField.vue';
  import FormSubmit from './FormSubmit.vue';
  import IFormField from '../Interface/IFormField';

  interface IProps {
    name: string;
    title: string;
    token: string;
    csrfToken: string;
  }

  const props = withDefaults(defineProps<IProps>(), {
    name: 'password_reset_form',
    title: 'Reset Your Password',
    token: '',
  });
  const formFields = reactive<Record<string, IFormField>>({
    password: {
      value: 'noodlepot',
      errors: [],
    },
    confirm_password: {
      value: 'noodlepot',
      errors: [],
    },
    _token: {
      value: props.csrfToken,
      errors: [],
    },
  });
  const password = computed({
    get: () => formFields.password.value,
    set: (val: string) => {
      formFields.password.value = val;
    },
  });
  const confirmPassword = computed({
    get: () => formFields.confirm_password.value,
    set: (val: string) => {
      formFields.confirm_password.value = val;
    },
  });

  const url = `/authenticate/password-reset/reset?token=${props.token}`;

  const getFormData = (): Record<string, IFormField> => {
    return formFields;
  };

  const clearValidationErrors = () => {
    formFields.password.errors = [];
    formFields.confirm_password.errors = [];
  };

  const validate = (): boolean => {
    if (formFields.password.value.length < 8) {
      formFields.password.errors.push('Password must contain a minimum of 8 characters.');
    }

    if (formFields.password.value !== formFields.confirm_password.value) {
      formFields.password.errors.push('Passwords do not match.');
      formFields.confirm_password.errors.push('Passwords do not match.');
    }

    return !(formFields.password.errors.length || formFields.confirm_password.errors.length);
  };
  const handleSubmit = async (): Promise<boolean> => {
    clearValidationErrors();

    return validate();
  };
</script>

<template>
  <AuthForm
    :title="title"
    text="Choose a new password"
    :endpoint="url"
    :handler="handleSubmit"
    @submission:failed="() => {}"
    :name="name"
    :data="getFormData()"
    :csrfToken="csrfToken">
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
    <FormField
      type="password"
      label="Confirm Password"
      v-model="confirmPassword"
      :name="`${name}[confirm_password]`"
      :id="`${name}_confirm_password`"
      :required="true"
      :errors="formFields.confirm_password.errors"
      placeholder="Confirm Your Password"
      @input="clearValidationErrors" />
    <FormSubmit :text="title" />
  </AuthForm>
</template>
