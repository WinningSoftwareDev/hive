<script setup lang="ts">
  import { computed, Ref, ref, ShallowRef, shallowRef } from 'vue';
  import { OAUTH_PROVIDERS } from '../Constant/OauthProvider';
  import FormToggle from '../Component/FormToggle.vue';
  import IAuthFormInternalProps from '../Interface/IAuthFormInternalProps';
  import LoginForm from '../Form/LoginForm.vue';
  import RegistrationForm from '../Form/RegistrationForm.vue';

  interface IProps {
    form: string | null;
    loginToken: string;
    registrationToken: string;
  }

  type AuthenticationFormComponent = typeof LoginForm | typeof RegistrationForm;

  const props = withDefaults(defineProps<IProps>(), {});
  const activeComponent: ShallowRef<AuthenticationFormComponent> = shallowRef(
    props.form === 'LoginForm' ? LoginForm : RegistrationForm,
  );
  const activeIntroText = computed(() =>
    activeComponent.value.__name === 'LoginForm'
      ? 'Welcome back. Sign in to your account to access your information and members-only features.'
      : 'Register to create your own account and access members-only features.',
  );
  const componentProps: Record<string, IAuthFormInternalProps> = {
    RegistrationForm: {
      name: 'registration_form',
      title: 'Register',
      csrfToken: props.registrationToken,
    },
    LoginForm: {
      name: 'login_form',
      title: 'Login',
      csrfToken: props.loginToken,
    },
  };
  const activeComponentProps: Ref<IAuthFormInternalProps> = ref<IAuthFormInternalProps>(
    props.form === 'LoginForm' ? componentProps.LoginForm : componentProps.RegistrationForm,
  );

  const setActiveComponent = (component: AuthenticationFormComponent): void => {
    activeComponent.value = component;

    Object.entries(componentProps).forEach((c: [string, IAuthFormInternalProps]) => {
      if (c[0] === component.__name) {
        activeComponentProps.value = c[1];
      }
    });
  };
  setActiveComponent(props.form === 'LoginForm' ? LoginForm : RegistrationForm);
</script>

<template>
  <div
    class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl overflow-hidden w-[96vw] mx-auto sm:w-[500px] min-h-[580px] shadow-2xl mt-12">
    <div class="flex border-b border-gray-700/50 bg-gray-900/20">
      <FormToggle
        text="Login"
        :active="activeComponent === LoginForm"
        @click="setActiveComponent(LoginForm)" />
      <FormToggle
        text="Register"
        :active="activeComponent === RegistrationForm"
        @click="setActiveComponent(RegistrationForm)" />
    </div>
    <div class="py-10 px-10">
      <h1 class="text-2xl font-bold text-center">{{ activeComponentProps.title }}</h1>
      <p class="my-3 mb-6 text-center text-gray-400">
        {{ activeIntroText }}
      </p>

      <div class="space-y-3 mb-6">
        <a
          v-for="provider in OAUTH_PROVIDERS"
          :key="provider.service"
          :href="'/connect/' + provider.service"
          class="flex items-center justify-center gap-3 w-full px-4 py-3 rounded-xl border border-gray-700/50 bg-gray-800/40 hover:bg-gray-700/40 transition-colors text-sm font-semibold text-gray-300 hover:text-white">
          <i :class="provider.icon + ' text-lg'"></i>
          Continue with {{ provider.label }}
        </a>
      </div>

      <div class="relative mb-6">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-700/50"></div>
        </div>
        <div class="relative flex justify-center text-xs uppercase">
          <span class="bg-gray-800/40 px-3 text-gray-500 tracking-widest"
            >Or continue with email</span
          >
        </div>
      </div>

      <component :is="activeComponent" v-bind="activeComponentProps" />
    </div>
  </div>
</template>
