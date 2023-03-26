/**
 * --------------------------------------------------------------------------
 * floating-input.js
 * Version: 1.0.1
 * Builds a floating input label
 * --------------------------------------------------------------------------
 */

const Selector = {
  INPUT_FLOAT        : '.input-float',
  INPUT_FIELD        : 'input',
};
const ClassName = {
  ACTIVE            : 'active',
  SHOW              : 'show',
  HIDE              : 'hide',
  BLOCK_SPACES      : 'input-float-block-spaces',
};

class FloatingInputClass {
  constructor() {
    document.addEventListener('DOMContentLoaded', this.init.bind(this));
    document.addEventListener('initFloatingInput', this.init.bind(this));
  }

  init() {
    const floatContainers = document.querySelectorAll(Selector.INPUT_FLOAT); // get DOM elements
    floatContainers.forEach((el) => {
      this.bindEvents(el);
    });
  }

  // register events
  bindEvents(el) {
    let inputField = el.querySelector(Selector.INPUT_FIELD);
    if (inputField) {
      inputField.addEventListener('input', (e) => {this.toggle(e, el)});

      // If on the .input-float element there is also an .input-float-block-spaces class,
      // we block the spaces from being accepted in the input field.
      el.classList.contains(ClassName.BLOCK_SPACES) && inputField.addEventListener('keydown', (e) => {this.blockSpaces(e, el)});

      // // Used for 'paste' event (copy-paste with trailing spaces)
      // inputField.addEventListener('paste', (e)=>{this.trimSpaces(e)});
    }
  }

  // add active class
  focus(e, floatContainer) {
    floatContainer.classList.add(ClassName.SHOW);
  }

  // remove active classes
  blur(e, floatContainer) {
    const target = e.target;
    if(!target.value) {
      floatContainer.classList.remove(ClassName.SHOW);
      floatContainer.classList.remove(ClassName.ACTIVE);
    }
  }
  toggle(e, floatContainer) {
    const target = e.target;
     !target.value ? this.blur(e, floatContainer) : this.focus(e, floatContainer);
  }
  blockSpaces(e, floatContainer) {
    32 === e.keyCode && e.preventDefault();
  }

  // // Used for 'paste' event (copy-paste with trailing spaces)
  // trimSpaces(e) {
  //   e.preventDefault();
  //   let paste = (e.clipboardData || window.clipboardData).getData('text');
  //   e.currentTarget.value = paste.trim();
  //   e.currentTarget.dispatchEvent(new Event('change'))  // 'change' event is used with validate() in Checkout.js
  //   e.currentTarget.dispatchEvent(new Event('input')) // 'input' event is for floating-input.js
  // }
}

const FloatingInput = new FloatingInputClass();
export default FloatingInput;