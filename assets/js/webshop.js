/**
 * --------------------------------------------------------------------------
 * webshop.js
 * Contains the JS actions needed on the Sidebar (eg: loading spinner), etc
 * --------------------------------------------------------------------------
 */

const NAME               = 'webshop';
const VERSION            = '1.0.1';
const DATA_KEY           = 'webshop';

const Selector = {
  WRAPPER   : '[data-wrapper-global]',
  BUTTON    : '[data-webshop-selector="button"]',
  SELECT    : '[data-webshop-selector="select"]',
};
const Wrapper = {
  GLOBAL                  : 'body',
};
const SelectorValue = {
  BUTTON    : 'button',
  SELECT    : 'select',
};
const Event = {
  CLICK     : 'click',
  CHANGE    : 'change',
  SUBMIT    : 'submit'
};

class Webshop {
  constructor() {
    this._proceed = false;
    let _selector = null;
    let _event = null;
    let _fn = null;
    let _fnEval = null;
    let _events = [];
    document.querySelectorAll('[data-webshop-selector]').forEach(function (el) {
      if(el.dataset.webshopSelector === SelectorValue.SELECT) {
        _fn = el.dataset.eval;
        _selector = el;
        _event = 'change'
      }
      if (el.dataset.webshopSelector === SelectorValue.BUTTON) {
        _fn = el.dataset.eval;
        _selector = el;
        _event = 'click'
      }
      if (_fn) {
        _fnEval = eval('this.'+_fn+'.bind(this)');
        let jSelector = `[data-eval="${_selector.dataset.eval}"]`;
        if (!_events.includes(jSelector)) {
          console.log(jSelector);
          $(Wrapper.GLOBAL).on(_event, jSelector, _fnEval);
          _events.push(jSelector);
        }
      }
    }, this);
    // $(Wrapper.GLOBAL).on('click', '[data-eval="showCart"]', this.showCart.bind(this));
    // $(Wrapper.GLOBAL).on('click', '[data-eval="getSender"]', this.getSender.bind(this))  // added manually, mert a contruct lefutasakor meg nincs a HTML-ben!
  }

  showCart(e) { //, selector
    console.log('teete')
    e.preventDefault();
    let $el = $(e.currentTarget);
    // if (this._proceed) { this._proceed = false; return; }
    // this.showOverlay($el, Event.CLICK);

    let url = $el.data('url');
    // let url = $el.attr('href');
    window.location.href = url;
  }

  // Helpers
  showOverlay(el, eventText) {
    el.addClass('loading-spinner-show');
    this._proceed = true;
    el.trigger('click');
  }
  hideOverlay(el) {
    el.removeClass('loading-spinner-show');
    this._proceed = false;
    $('body').trigger('refreshDomAndInitFloatingInputs'); // az input-float miatt szukseges
  }
}

jQuery.fn.webshop = function() {
    const instance = new Webshop();
  }

// jQuery.fn.extend({
//   webshop: function() {
//     const instance = new Webshop();
//   }
// })
;