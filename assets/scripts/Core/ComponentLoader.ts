import { components } from '../_components';
import { createApp } from 'vue';

export default class ComponentLoader {
  public static watch(): void {
    Object.entries(components).forEach(([name, component]): void => {
      document.querySelectorAll(name).forEach((el: Element): void => {
        const props: Record<string, string> = {};

        Array.from(el.attributes).forEach((attr) => {
          props[attr.name] = attr.value;
        });

        createApp(component, { ...props, ...(el as HTMLElement).dataset }).mount(el);
      });
    });
  }
}
