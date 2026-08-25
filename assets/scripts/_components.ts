/*
This file provides a convenient entrypoint for including all components that are required for use within Latte
templates. If the component is only ever used inside other Vue components, no need to include it here.

This is called by ComponentLoader.watch() inside the default app.ts to automatically load Vue templates included in
your Latte templates.
*/
import { components as AppCoreComponents } from './Plugin/AppCore/_components';
import { components as AuthCoreComponents } from './Plugin/AuthCore/_components';

export const components = {
  ...AppCoreComponents,
  ...AuthCoreComponents,
};
