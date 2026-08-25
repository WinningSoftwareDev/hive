<script setup lang="ts">
  import { computed, reactive } from 'vue';
  import AuthForm from './AuthForm.vue';
  import FormField from './FormField.vue';
  import FormSubmit from './FormSubmit.vue';
  import IAuthFormInternalProps from '../Interface/IAuthFormInternalProps';
  import IFormField from '../Interface/IFormField';
  import IntroCardLink from '../../AppCore/IntroCardLink.vue';

  const props = withDefaults(defineProps<IAuthFormInternalProps>(), {
    name: 'registration_form',
    title: 'Register',
    token: '',
  });
  const formFields = reactive<Record<string, IFormField>>({
    email: {
      value: 'testuser@example.com',
      errors: [],
    },
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
  const confirmPassword = computed({
    get: () => formFields.confirm_password.value,
    set: (val: string) => {
      formFields.confirm_password.value = val;
    },
  });

  const getFormData = (): Record<string, IFormField> => {
    return formFields;
  };

  const clearValidationErrors = () => {
    formFields.email.errors = [];
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
  const handleFailedSubmit = (errors: Record<string, string[]>) => {
    if (errors.email !== undefined) {
      formFields.email.errors.push(...errors.email);
    }
  };
</script>

<template>
  <AuthForm
    title="Register"
    endpoint="/authenticate/register"
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
    <div class="text-center">
      Already have an account?
      <IntroCardLink url="/authenticate?form=LoginForm" text="Login to your account" />.
    </div>
  </AuthForm>
</template>
