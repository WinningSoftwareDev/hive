export default class FlashHandler {
  private static DEFAULT_TIMEOUT: number = 4000;
  private static DEFAULT_DELAY: number = 500;

  private flashes: NodeListOf<HTMLElement>;

  constructor() {
    this.flashes = document.querySelectorAll('.flash-message');

    if (!this.flashes.length) return;

    this.manageFlashMessages();
  }

  private manageFlashMessages(): void {
    this.flashes.forEach((el: HTMLElement, index: number) => {
      setTimeout(() => {
        el.classList.remove('opacity-0', 'translate-y-4');
        el.classList.add('opacity-100', 'translate-y-0', 'transition', 'duration-300', 'ease-out');
      }, index * FlashHandler.DEFAULT_DELAY);

      setTimeout(
        () => {
          el.classList.remove('opacity-100', 'translate-y-0');
          el.classList.add('opacity-0', 'translate-y-[-1rem]');
          el.addEventListener('transitionend', () => el.remove(), { once: true });
        },
        FlashHandler.DEFAULT_TIMEOUT + index * FlashHandler.DEFAULT_DELAY,
      );
    });
  }
}
