/**
 * --------------------------------------------------------------------------
 * loading-overlay.js  - NOT IN USE !!? ??????
 *
 * Adds .btn-loading class to Buttons and Links
 * which triggers a loading spinner on top of them.
 * --------------------------------------------------------------------------
 */

const NAME               = 'loading-overlay';
const VERSION            = '1.0.1';
const DATA_KEY           = 'loading-overlay';

const Selector = {
  LOADING_OVERLAY    : '[data-toggle="loading-overlay"]',
};

class LoadingOverlay {
  constructor() {
    this._selectors = document.querySelectorAll(Selector.LOADING_OVERLAY)
    this._isShown = false;
    document.addEventListener('DOMContentLoaded', this.init());
  }

  init() {
    this._selectors.forEach(function(e) {
      this.attachListeners(e)
    }, this);
  }

  attachListeners(selector) {
    selector.addEventListener('click', this.toggle.bind(this, selector), false)
  }

  // detachListeners(selector) {
  //   selector.removeEventListener('click', this.toggle(selector))
  // }

  toggle(selector) {
    return this._isShown ? function () {  } : setTimeout(this.show(selector), 3000);
  }

  show(selector) {
    selector.removeAttribute('aria-hidden');
    selector.setAttribute('aria-loading', true);
    selector.classList.add('btn-loading');
    this._isShown = true;
  }
}

const instance = new LoadingOverlay();
export { instance as LoadingOverlay };