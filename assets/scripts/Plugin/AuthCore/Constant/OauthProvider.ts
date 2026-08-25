interface OauthProviderConfig {
  service: string;
  label: string;
  icon: string;
}

export const OAUTH_PROVIDERS: readonly OauthProviderConfig[] = Object.freeze([
  { service: 'github', label: 'GitHub', icon: 'fa-brands fa-github' },
  { service: 'google', label: 'Google', icon: 'fa-brands fa-google' },
]);
