/**
 * --------------------------------------------------------------------------
 * checkout.js    !!!!!!!!   NOT IN USE  !!!!!!!!
 * Contains the JS actions needed on the Checkout pages
 *
 *
 *
 * !!!!!!!!   NOT IN USE  !!!!!!!!
 *
 *
 *
 * --------------------------------------------------------------------------
 */
// import $ from 'jquery'

const NAME               = 'checkout';
const VERSION            = '1.0.1';
const DATA_KEY           = 'checkout';

const Selector = {
  WRAPPER   : '[data-wrapper-global]',
  BUTTON    : '[data-selector="button"]',
  SELECT    : '[data-selector="select"]',
};
const Wrapper = {
  GLOBAL                  : '[data-wrapper-global]',
  ALERT                   : '[data-wrapper-alert]',
  ALERT_MESSAGE           : '[data-alert-message]',
  CART                    : '[data-wrapper-cart]',
  CART_BODY               : '[data-wrapper-cart-body]',
  CARD                    : '[data-wrapper-card-message]',
  CARD_BODY               : '[data-wrapper-card-body]',
  CARD_FORM               : '[data-wrapper-card-form]',
  RECIPIENT               : '[data-wrapper-recipient]',
  RECIPIENT_BODY          : '[data-wrapper-recipient-body]',
  RECIPIENT_LIST          : '[data-wrapper-recipient-list]',
  RECIPIENT_MODAL         : '#modal--recipientList',
  RECIPIENT_FORM          : '[data-wrapper-recipient-form]',
  CUSTOMER                : '[data-wrapper-customer]',
  CUSTOMER_BODY           : '[data-wrapper-customer-body]',
  CUSTOMER_FORM           : '[data-wrapper-customer-form]',
  SHIPPING                : '[data-wrapper-shipping]',
  SHIPPING_FORM           : '[data-wrapper-shipping-form]',
  DELIVERY_DATE           : '[data-wrapper-delivery-date]',
  DELIVERY_DATE_FORM      : '[data-wrapper-delivery-date-form]',
  SENDER                  : '[data-wrapper-sender]',
  SENDER_BODY             : '[data-wrapper-sender-body]',
  SENDER_LIST             : '[data-wrapper-sender-list]',
  SENDER_MODAL            : '#modal--senderList',
  SENDER_FORM             : '[data-wrapper-sender-form]',
  PAYMENT                 : '[data-wrapper-payment]',
  PAYMENT_FORM            : '[data-wrapper-payment-form]',
};
const SelectorValue = {
  BUTTON    : 'button',
  SELECT    : 'select',
};
const Event_ = {
  CLICK     : 'click',
  CHANGE    : 'change',
  SUBMIT    : 'submit'
};
const AlertHtml = {
  pre: function(type) {
    'success' !== type && 'warning' !== type && 'danger' !== type && (type = 'warning');
    return `<div class="JS--alertMessage alert alert-${type} mt-4" role="alert" data-alert-message>
                        <i class="fas fa-exclamation-circle mr-2 text-muted"></i>`;
  },
  post: `</div>`
};
// FYI: AlertMessages is defined in the templates 'webshop/cart/_cart-and-checkout-localization.html.twig
const scrollUp = { block: 'start', behavior: 'smooth'};

class Entity {
  constructor(wrapper) {
    this.wrapper = $(wrapper);
    this.hasSuccess = false;
    this.hasError = this.wrapper.find(Wrapper.ALERT_MESSAGE).length > 0 ? true : false;
  }

  // Set a 'hasError' flag to the Entity.
  // When there is no error, it also removes any error alert msg
  setHasError(hasError) {
    this.hasError = hasError;
    if (this.hasError === false) {
      this.wrapper.find(Wrapper.ALERT_MESSAGE).replaceWith('');
    }
  }
  showAlertAt(position, msg, type) {
    this.hideAlert();
    position.after(AlertHtml.pre(type) + msg + AlertHtml.post);
    position[0].scrollIntoView(scrollUp);
    'success' === type && (this.hasSuccess = true);
    'danger' === type && (this.hasError = true);
  }
  hideAlert() {
    this.wrapper.find(Wrapper.ALERT_MESSAGE).replaceWith('');
    this.hasError = false;
    this.hasSuccess = false;
  }
  hasNoErrors() {
    // let position = this.wrapper.find(Wrapper.ALERT)[0];
    // return this.hasError ? false : (position.scrollIntoView(scrollUp), true);
    return this.hasError ? false : true;
  }
}

class FormValidation {
  constructor(formWrapper, constraints, entity) {
    this.formWrapper = formWrapper;
    this.constraints = constraints;
    this.entity = entity;

    // Listen for the event.
    document.addEventListener('initFormValidation', function (e) {
      this.validateForm();
    }.bind(this), false);

    this.validateForm();
  }

  validateForm() {
    let form = document.querySelector(this.formWrapper);
    let inputs = form.querySelectorAll('input, textarea, select')
    for (var i = 0; i < inputs.length; ++i) {
      inputs.item(i).addEventListener('change', function(e) {
        var errors = validate(form, this.constraints, {fullMessages: false}) || {};
        this.showErrorsForInput(e.currentTarget, errors[e.currentTarget.name])
        !Object.keys(errors).length && this.entity.setHasError(false);
      }.bind(this));
    }
  }

  showErrorsForInput(input, errors) {
    var messages = this.closestParent(input, 'form-group').querySelector('.invalid-feedback'); // Find where the error messages will be insert into
    this.resetInput(input); // First we remove any old messages and resets the classes
    if (errors) {
      input.classList.add("is-invalid"); // we first mark the input has having errors
      errors.forEach(function(error) { // then we append all the errors
        this.addError(messages, error);
      }.bind(this));
    } else {
      input.classList.add("is-valid"); // otherwise we simply mark it as success
    }
  }

  // Recursively finds the closest parent that has the specified class
  closestParent(child, className) {
    if (!child || child == document) {
      return null;
    }
    if (child.classList.contains(className)) {
      return child;
    } else {
      return this.closestParent(child.parentNode, className);
    }
  }

  resetInput(input) {
    // Remove the success and error classes
    input.classList.remove("is-invalid");
    input.classList.remove("is-valid");
    // and remove any old messages
    this.closestParent(input, 'form-group').querySelectorAll(".invalid-feedback").forEach(function(el) {
      el.innerHTML = '';
    });
  }

  addError(messages, error) {
    var block = document.createElement("p");
    block.classList.add("help-block");
    block.classList.add("error");
    block.innerText = error;
    messages.appendChild(block);
    this.entity.hasError || this.entity.setHasError(true);
  }
}

class Checkout {
  constructor() {
    this._proceed = false;
    this.bindDataEval();
    this.initEntities();
  }

  bindDataEval() {
    let _selector = null;
    let _event = null;
    let _fn = null;
    let _fnEval = null;
    let _events = [];
    document.querySelectorAll('[data-selector]').forEach(function (el) {
      if(el.dataset.selector === SelectorValue.SELECT) {
        _fn = el.dataset.eval;
        _selector = el;
        _event = 'change'
      }
      if (el.dataset.selector === SelectorValue.BUTTON) {
        _fn = el.dataset.eval;
        _selector = el;
        _event = 'click'
      }
      if (_fn) {
        console.log(_fn)
        _fnEval = eval('this.'+_fn+'.bind(this)')
        let jSelector = `[data-eval="${_selector.dataset.eval}"]`;
        if (!_events.includes(jSelector)) {
          console.log(jSelector);
          $(Wrapper.GLOBAL).on(_event, jSelector, _fnEval)
          _events.push(jSelector);
        }
      }
    }, this)
    // $(Wrapper.GLOBAL).on('click', '[data-eval="refreshRecipientList"]', this.refreshRecipientList.bind(this)) // added manually, mert nincs sehol a html-ben!
    // $(Wrapper.GLOBAL).on('click', '[data-eval="refreshSenderList"]', this.refreshSenderList.bind(this))

    if (!_events.includes('[data-eval="removeItemFromCart"]')) {
      $(Wrapper.GLOBAL).on('click', '[data-eval="removeItemFromCart"]', this.removeItemFromCart.bind(this))
      _events.push('[data-eval="removeItemFromCart"]')
    }
    if (!_events.includes('[data-eval="setItemQuantity"]')) {
      $(Wrapper.GLOBAL).on('change', '[data-eval="setItemQuantity"]', this.setItemQuantity.bind(this))
      _events.push('[data-eval="setItemQuantity"]')
    }
  }

  initEntities() {
    if (document.querySelector(Wrapper.CART)) {
      this.cart = new Entity(Wrapper.CART);
    }
    if (document.querySelector(Wrapper.RECIPIENT)) {
      this.recipient = new Entity(Wrapper.RECIPIENT);
      new FormValidation(Wrapper.RECIPIENT_FORM, recipientConstraints, this.recipient);
    }
    if (document.querySelector(Wrapper.CUSTOMER) && document.querySelector(Wrapper.CUSTOMER_FORM)) {
      this.customer = new Entity(Wrapper.CUSTOMER);
      new FormValidation(Wrapper.CUSTOMER_FORM, customerConstraints, this.customer);
    }
    if (document.querySelector(Wrapper.SHIPPING)) {
      this.shipping = new Entity(Wrapper.SHIPPING);
    }
    // if (document.querySelector(Wrapper.DELIVERY_DATE)) {
    //   this.deliveryDate = new Entity(Wrapper.DELIVERY_DATE);
    // }
    if (document.querySelector(Wrapper.PAYMENT)) {
      this.payment = new Entity(Wrapper.PAYMENT);
    }
    if (document.querySelector(Wrapper.SENDER)) {
      this.sender = new Entity(Wrapper.SENDER);
      new FormValidation(Wrapper.SENDER_FORM, senderConstraints, this.sender);
    }
  }
  
  hasNoErrors() {
    let hasError = false;
    let firstPosition = null;
    if ('undefined' !== typeof this.cart) {
      if (this.cart.hasError) {
        // this.cart.showAlertAt($(Wrapper.CART).find(Wrapper.ALERT), 'valami hiba', 'danger');
        firstPosition = !firstPosition ? $(Wrapper.CART).find(Wrapper.ALERT)[0] : firstPosition;
        hasError = true;
      }
    }
    if ('undefined' !== typeof this.recipient) {
      if (this.recipient.hasError) {
        // this.recipient.showAlertAt($(Wrapper.RECIPIENT).find(Wrapper.ALERT), AlertMessages.ERROR_RECIPIENT_DATA_NOT_SAVED, 'danger');
        firstPosition = !firstPosition ? $(Wrapper.RECIPIENT).find(Wrapper.ALERT)[0] : firstPosition;
        hasError = true;
      }
    }
    if ('undefined' !== typeof this.customer) {
      if (this.customer.hasError) {
        // this.customer.showAlertAt($(Wrapper.CUSTOMER).find(Wrapper.ALERT), AlertMessages.ERROR_CUSTOMER_DATA_NOT_SAVED, 'danger');
        firstPosition = !firstPosition ? $(Wrapper.CUSTOMER).find(Wrapper.ALERT)[0] : firstPosition;
        hasError = true;
      }
    }
    if ('undefined' !== typeof this.shipping) {
      if (this.shipping.hasError) {
        // this.shipping.showAlertAt($(Wrapper.SHIPPING).find(Wrapper.ALERT), AlertMessages.ERROR_NO_SHIPPING, 'danger');
        firstPosition = !firstPosition ? $(Wrapper.SHIPPING).find(Wrapper.ALERT)[0] : firstPosition;
        hasError = true;
      }
    }
    if ('undefined' !== typeof this.deliveryDate) {
      if (this.deliveryDate.hasError) {
        // this.deliveryDate.showAlertAt($(Wrapper.DELIVERY_DATE).find(Wrapper.ALERT), AlertMessages.ERROR_NO_DELIVERY_DATE, 'danger');
        firstPosition = !firstPosition ? $(Wrapper.DELIVERY_DATE).find(Wrapper.ALERT)[0] : firstPosition;
        hasError = true;
      }
    }
    if ('undefined' !== typeof this.payment) {
      if (this.payment.hasError) {
        this.payment.showAlertAt($(Wrapper.PAYMENT).find(Wrapper.ALERT), AlertMessages.ERROR_NO_PAYMENT, 'danger');
        firstPosition = !firstPosition ? $(Wrapper.PAYMENT).find(Wrapper.ALERT)[0] : firstPosition;
        hasError = true;
      }
    }
    if ('undefined' !== typeof this.sender) {
      if (this.sender.hasError) {
        // this.sender.showAlertAt($(Wrapper.SENDER).find(Wrapper.ALERT), AlertMessages.ERROR_SENDER_DATA_NOT_SAVED, 'danger');
        firstPosition = !firstPosition ? $(Wrapper.SENDER).find(Wrapper.ALERT)[0] : firstPosition;
        hasError = true;
      }
    }
    if (hasError) {
      firstPosition.scrollIntoView(scrollUp);
      return false;
    }
    return true;
  }

  submitGreetingCardForm(e) {
    // if (this.cart.hasNoErrors()) {
    if (this.hasNoErrors()) {
      e.preventDefault();
      let $el = $(e.currentTarget);
      if (this._proceed) { this._proceed = false; return; }
      this.showOverlay($el, Event_.CLICK);
      let url = $el.data('url');
      let $wrapper = $el.closest(Wrapper.GLOBAL);
      let $form = $wrapper.find(Wrapper.CARD_FORM);

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        context: this
      }).done(function() {
        window.location.href = url;
      }).fail(function(jqXHR) {
        $form.replaceWith(jqXHR.responseText);
        this.hideOverlay($el);
      });
    }
  }
  submitRecipientAndCustomer(e) {
    if (this.hasNoErrors()) {
      // if (this.recipient.hasNoErrors()) {
      e.preventDefault();
      let $el = $(e.currentTarget);
      if (this._proceed) { this._proceed = false; return; }
      this.showOverlay($el, Event_.CLICK);
      let url = $el.data('url');
      let $wrapper = $el.closest(Wrapper.GLOBAL);
      let $recipientForm = $wrapper.find(Wrapper.RECIPIENT_FORM);
      let $recipientBody = $recipientForm.closest(Wrapper.RECIPIENT_BODY);
      let $customerBody = $wrapper.find(Wrapper.CUSTOMER_BODY);
      let $customerForm = $wrapper.find(Wrapper.CUSTOMER_FORM);

      let recipientForm = null;
      let customerForm = null;

      let a1 = $.ajax({
        url: $recipientForm.attr('action'),
        method: 'POST',
        data: $recipientForm.serialize(),
        context: this
      });
      let a2 = $.ajax({
        url: $customerForm.attr('action'),
        method: 'POST',
        data: $customerForm.serialize(),
        context: this,
      });

      a1.done(function(data) {
          $recipientForm.replaceWith(data);
      })
        .fail(function (jqXHR) {
          recipientForm = jqXHR.responseText;
          this.recipient.setHasError(true);
      })
        .always(function() {
          a2.done(function(data){
              $customerForm.replaceWith(data);
          })
            .fail(function(jqXHR){
              customerForm = jqXHR.responseText;
              this.customer.setHasError(true);
          })
            .always(function() {
              $recipientForm.replaceWith(recipientForm);
              $customerForm.replaceWith(customerForm);
              document.dispatchEvent(new Event('initFloatingInput'));
              document.dispatchEvent(new Event('initFormValidation'));
              document.dispatchEvent(new Event('initTooltip'));
          }.bind(this))
      }.bind(this));

      $.when(a1, a2).fail(function () {
        $wrapper.find(Wrapper.ALERT) && $wrapper.find(Wrapper.ALERT)[0].scrollIntoView(scrollUp);
        this.hideOverlay($el);
      }.bind(this));

      $.when(a1, a2).done(function () {
        window.location.href = url;
      }.bind(this));
    }
  }
  submitShippingMethod(e){
    if (this.hasNoErrors()) {
      e.preventDefault();
      let $el = $(e.currentTarget);
      if (this._proceed) { this._proceed = false; return; }
      this.showOverlay($el, Event_.CLICK);
      let url = $el.data('url');
      let $wrapper = $el.closest(Wrapper.GLOBAL);
      let $shippingForm = $wrapper.find(Wrapper.SHIPPING_FORM);
      let $deliveryDateForm = $wrapper.find(Wrapper.DELIVERY_DATE_FORM);

      let shippingForm = null;
      let deliveryDateForm = null;

      let a1 = $.ajax({
        url: $shippingForm.attr('action'),
        method: 'POST',
        data: $shippingForm.serialize(),
        context: this
      });
      let a2 = $.ajax({
        url: $deliveryDateForm.attr('action'),
        method: 'POST',
        data: $deliveryDateForm.serialize(),
        context: this
      });

      a1.done(function(data) {
        $shippingForm.replaceWith(data);
      })
        .fail(function (jqXHR) {
          shippingForm = jqXHR.responseText;
          // this.shipping.setHasError(true);
          this.shipping.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_UNKNOWN, 'danger');
      })
        .always(function() {
          a2.done(function(data){
            $deliveryDateForm.replaceWith(data);
          })
            .fail(function(jqXHR){
              deliveryDateForm = jqXHR.responseText;
              this.deliveryDate.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_UNKNOWN, 'danger');
              // this.deliveryDate.setHasError(true);
              // this.hasNoErrors();
          })
            .always(function() {
              $shippingForm.replaceWith(shippingForm);
              $deliveryDateForm.replaceWith(deliveryDateForm);
              document.dispatchEvent(new Event('initFloatingInput'));
          }.bind(this))
      }.bind(this));

      $.when(a1, a2).fail(function () {
        this.hideOverlay($el);
        if ($wrapper.find(Wrapper.ALERT)) {
          $wrapper.find(Wrapper.ALERT)[0].scrollIntoView(scrollUp);
        }
      }.bind(this));

      $.when(a1, a2).done(function () {
        window.location.href = url;
      }.bind(this));
    }
  }

  // Submits Payment method and Billing address (Sender)
  submitPaymentMethod(e){
    e.preventDefault();
    if (this.hasNoErrors()) {
      let $el = $(e.currentTarget);
      if (this._proceed) { this._proceed = false; return; }
      this.showOverlay($el, Event_.CLICK);
      let url = $el.data('url');
      let $wrapper = $el.closest(Wrapper.GLOBAL);
      let $paymentForm = $wrapper.find(Wrapper.PAYMENT_FORM);
      let $senderForm = $wrapper.find(Wrapper.SENDER_FORM);

      let paymentForm = null;
      let senderForm = null;

      let a1 = $.ajax({
        url: $paymentForm.attr('action'),
        method: 'POST',
        data: $paymentForm.serialize(),
        context: this
      });
      let a2 = $.ajax({
        url: $senderForm.attr('action'),
        method: 'POST',
        data: $senderForm.serialize(),
        context: this
      });

      a1.done(function(data) {
        $paymentForm.replaceWith(data);
      })
        .fail(function (jqXHR) {
          paymentForm = jqXHR.responseText;
          this.payment.setHasError(true);
      })
        .always(function() {
          a2.done(function(data){
            $senderForm.replaceWith(data);
          })
            .fail(function(jqXHR){
              senderForm = jqXHR.responseText;
              this.sender.setHasError(true);
          })
            .always(function() {
              $paymentForm.replaceWith(paymentForm);
              $senderForm.replaceWith(senderForm);
              document.dispatchEvent(new Event('initFloatingInput'));
              document.dispatchEvent(new Event('initFormValidation'));
          }.bind(this))
      }.bind(this));

      $.when(a1, a2).fail(function () {
        $wrapper.find(Wrapper.ALERT) && $wrapper.find(Wrapper.ALERT)[0].scrollIntoView(scrollUp);
        this.hideOverlay($el);
      }.bind(this));

      $.when(a1, a2).done(function () {
        window.location.href = url;
      }.bind(this));
    }
  }
  submitDeliveryDateForm(e) {
    if (this.hasNoErrors()) {
    // if (this.recipient.hasNoErrors()) {
      e.preventDefault();
      let $el = $(e.currentTarget);
      if (this._proceed) { this._proceed = false; return; }
      this.showOverlay($el, Event_.CLICK);
      let url = $el.data('url');
      let $wrapper = $el.closest(Wrapper.GLOBAL);
      let $form = $wrapper.find('.JS--hiddenDeliveryDateForm');
      let $recipientForm = $wrapper.find(Wrapper.RECIPIENT_FORM);
      let $recipientBody = $recipientForm.closest(Wrapper.RECIPIENT_BODY);

      let form = null;
      let recipientForm = null;

      let aDelivery = $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        context: this,
      });
      let aRecipient = $.ajax({
        url: $recipientForm.attr('action'),
        method: 'POST',
        data: $recipientForm.serialize(),
        context: this,
      });

      aRecipient
          .done(function(data) {
            $recipientForm.replaceWith(data);
            console.log('done')
          })
          .fail(function (jqXHR) {
            recipientForm = jqXHR.responseText;
          })
          .always(function() {
            console.log('done2')
            aDelivery
              .done(function(data){
                $form.replaceWith(data);
              })
              .fail(function(jqXHR){
                form = jqXHR.responseText;
              })
              .always(function() {
                console.log('done3')
                $recipientForm.replaceWith(recipientForm);
                $form.replaceWith(form);
                document.dispatchEvent(new Event('initFloatingInput'));
                // this.hideOverlay($el);
              }.bind(this))
          }.bind(this));

      $.when(aRecipient, aDelivery).fail(function () {
          this.hideOverlay($el);
      }.bind(this));

      $.when(aRecipient, aDelivery).done(function () {
        window.location.href = url;
      }.bind(this));
    }
  }

  removeItemFromCart(e) { //, selector
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }
    this.showOverlay($el, Event_.CLICK);

    let url = $el.data('url');
    let $resultWrapper = $el.closest(Wrapper.CART).find(Wrapper.CART_BODY);

    $.ajax({
      url: url,
      method: 'DELETE',
      context: this,
      success: function(data) {
        $resultWrapper.html(data);
        this._proceed = false;
        this.cart.hideAlert();
      }
    });
  }
  addGiftToCart(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }
    this.showOverlay($el, Event_.CLICK);

    let url = $el.data('url');
    let $wrapper = $el.closest(Wrapper.GLOBAL).find(Wrapper.CART);
    let $resultWrapper = $wrapper.find(Wrapper.CART_BODY);
    $.ajax({
      url: url,
      method: 'POST',
      context: this,
    }).done(function(data) {
      $resultWrapper.html(data);
      this.hideOverlay($el);
      this._proceed = false;
      this.cart.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.SUCCESS_PRODUCT_WAS_ADDED, 'success');
    }).fail(function(jqXHR) {
      this.hideOverlay($el);
      this.cart.showAlertAt($wrapper.find(Wrapper.ALERT), jqXHR.responseJSON, 'warning');
    });
  }
  setItemQuantity(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }

    let $wrapperQuantity = $el.closest('[data-wrapper-item-quantity]');
    $wrapperQuantity.addClass('loading-spinner-show');
    this._proceed = true;

    let $wrapper = $el.closest(Wrapper.CART);
    let $form = $el.closest('.JS--formWrapper').find('form');
    let $formWrapper = $el.closest('.JS--formWrapper');
    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: $form.serialize(),
      context: this,
    }).done(function (data) {
      $formWrapper.html(data);
      this.hideOverlay($el);
      this.cart.hideAlert();
      $wrapperQuantity.removeClass('loading-spinner-show');

    }).fail(function (jqXHR) {
      $form.replaceWith(jqXHR.responseText);
      this.hideOverlay($el);
      this.cart.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.WARNING_NOT_ENOUGH_STOCK, 'warning');
      $wrapperQuantity.removeClass('loading-spinner-show');
    });
  }
  pickGreetingCardMessage (e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    let $message = $el.closest('.JS--messageWrapper').find('.JS--messageForm-message');

    $message.val($el.text());
    $el.closest('.JS--globalWrapper').closest('.JS--messageWrapper').find('.JS--greetingCardDropdown').removeClass('show');
  }

  // Show an empty Recipient form, triggered by 'Új címzett hozáadása' button
  showRecipientForm(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }
    this.showOverlay($el, Event_.CLICK);

    let url = $el.data('url');
    let $wrapper = $el.closest(Wrapper.RECIPIENT);
    let $wrapperBody = $wrapper.find(Wrapper.RECIPIENT_BODY);

    $.post({
      url: url,
      context: this,
    }).done(function (data) {
      $wrapperBody.html(data);
      this.hideOverlay($el);
      this.recipient.hideAlert();
      this._proceed = false;
    }).fail(function () {
      this.recipient.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
      this.hideOverlay($el);
    });
  }

  // Picks a Recipient from the recipient list (modal) and updates the Recipient form with it.
  // Triggered from the modal window by choosing one recipient from the list.
  pickRecipient(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }
    this.showOverlay($el, Event_.CLICK);

    let $wrapper = $el.closest(Wrapper.RECIPIENT);
    let $wrapperBody = $wrapper.find(Wrapper.RECIPIENT_BODY);
    let url = $el.attr('href');

    $.post({
      url: url,
      context: this,
    }).done(function (data) {
      $('[data-eval="refreshRecipientList"]').trigger('click');
      $wrapperBody.html(data);
      this.recipient.hideAlert();
    }).fail(function () {
      this.recipient.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
    }).always(function() {
      $wrapper.find(Wrapper.RECIPIENT_MODAL).modal('hide');
      this.hideOverlay($el);
    });
  }

  // Triggered from JS code (deleteRecipient)
  refreshRecipientList(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    // Ez azert nem kell, mert a refreshRecipientList-et kodbol triggereljuk !!
    // if (this._proceed) { this._proceed = false; return; }
    // this.showOverlay($el, Event_.CLICK);

    let url = $el.data('url');
    let $wrapper = $el.closest(Wrapper.RECIPIENT);
    let $wrapperBody = $wrapper.find(Wrapper.RECIPIENT_LIST);
    $.ajax({
      url: url,
      method: 'GET',
      context: this,
    }).done(function(data) {
      $wrapperBody.html(data);
      this.hideOverlay($el);
      this.recipient.hideAlert();
    }).fail(function() {
      this.hideOverlay($el);
      this.recipient.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_UNKNOWN, 'danger');
    });
  }

  deleteRecipient (e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }
    this.showOverlay($el, Event_.CLICK);

    let url = $el.data('url');
    let $wrapper = $el.closest(Wrapper.RECIPIENT);
    let $wrapperBody = $wrapper.find(Wrapper.RECIPIENT_BODY);

    let confirm = window.confirm('Biztosan szeretnéd törölni?');
    if (confirm) {

      $.ajax({
        url: url,
        method: 'GET',
        context: this,
      }).done(function(data) {
        $('[data-eval="refreshRecipientList"]').trigger('click');

        $wrapperBody.html(data);
        $wrapper.find(Wrapper.RECIPIENT_MODAL).modal('hide');
        this._proceed = false;
      }).fail(function() {
        this.hideOverlay($el);
        this.recipient.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_UNKNOWN, 'danger');
      });

    } else {
      this.hideOverlay($el);
    }
  }

  // Show an empty Sender form, triggered by 'Új számlázási cím hozáadása' button
  showSenderForm(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }
    this.showOverlay($el, Event_.CLICK);

    let url = $el.data('url');
    let $wrapper = $el.closest('.JS--senderWrapper');
    let $wrapperBody = $wrapper.find('.JS--senderContentBlock');

    $.ajax({
      url: url,
      method: 'POST',
      context: this
    }).done(function(data) {
      $wrapperBody.html(data);
      this.hideOverlay($el);
      this.sender.hideAlert();
      this._proceed = false;
    }).fail(function() {
      this.sender.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
      this.hideOverlay($el);
    });
  }

  // Picks a Sender from the recipient list (modal) and updates the Sender form with it.
  // Triggered from the modal window by choosing one sender from the list.
  pickSender(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }
    this.showOverlay($el, Event_.CLICK);

    let $wrapper = $el.closest(Wrapper.SENDER);
    let $wrapperBody = $wrapper.find(Wrapper.SENDER_BODY);
    let url = $el.attr('href');

    $.ajax({
      url: url,
      method: 'POST',
      context: this
    }).done(function(data) {
      $('[data-eval="refreshSenderList"]').trigger('click');
      $wrapperBody.html(data);
      $wrapper.find(Wrapper.SENDER_MODAL).modal('hide');
      // $el.closest('.modal-body').find('.selected').removeClass('selected');
      // $el.closest('.JS--item').addClass('selected');
      this.hideOverlay($el);
      this.sender.hideAlert();
    }).fail(function() {
      this.hideOverlay($el);
      this.sender.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
    });
  }

  // Triggered from JS code (deleteSender)
  refreshSenderList(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    // if (this._proceed) { this._proceed = false; return; }
    // this.showOverlay($el, Event_.CLICK);

    let url = $el.data('url');
    let $wrapper = $el.closest(Wrapper.SENDER);
    let $wrapperBody = $wrapper.find(Wrapper.SENDER_LIST);
    $.ajax({
      url: url,
      method: 'GET',
      context: this
    }).done(function(data) {
      $wrapperBody.html(data);
      this.hideOverlay($el);
      // this.sender.hideError();
      this.sender.hideAlert();
    }).fail(function() {
      this.hideOverlay($el);
      this.sender.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
    });
  }

  deleteSender (e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }
    this.showOverlay($el, Event_.CLICK);

    let url = $el.data('url');
    let $wrapper = $el.closest(Wrapper.SENDER);
    let $wrapperBody = $wrapper.find(Wrapper.SENDER_BODY);

    let confirm = window.confirm('Biztosan szeretnéd törölni?');
    if (confirm) {
      $.ajax({
        url: url,
        method: 'GET',
        context: this
      }).done(function(data) {
        $('[data-eval="refreshSenderList"]').trigger('click');
        $wrapperBody.html(data);
        $wrapper.find(Wrapper.SENDER_MODAL).modal('hide');
        this._proceed = false;
      }).fail(function() {
        this.hideOverlay($el);
        this.sender.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
      });
    } else {
      this.hideOverlay($el);
    }
  }


  // Marks the selected Shipping method with a coloured background
  markShippingAsSelected(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    let $wrapper = $el.closest(Wrapper.SHIPPING);
    let $choiceWrapper = $el.closest('.JS--choiceContainer');
    $choiceWrapper.find('input').prop('checked',true);  // Beleteszi a pipát
    $wrapper.find('.selected').removeClass('selected');
    $choiceWrapper.addClass('selected');

    if ($wrapper.find(Wrapper.ALERT)) {
      // this.shipping.hideError();
      this.shipping.hideAlert();
    }
  }

  // Marks the selected Payment method with a coloured background
  markPaymentAsSelected(e) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    let $wrapper = $el.closest(Wrapper.PAYMENT);
    let $choiceWrapper = $el.closest('.JS--choiceContainer');
    $choiceWrapper.find('input').prop('checked',true);  // Beleteszi a pipát
    $wrapper.find('.selected').removeClass('selected');
    $choiceWrapper.addClass('selected');

    if ($wrapper.find(Wrapper.ALERT)) {
      // this.payment.hideError();
      this.payment.hideAlert();
    }
  }

  // Helpers
  showOverlay(el) {
    el.addClass('loading-spinner-show');
    this._proceed = true;
    el.trigger('click');
    document.dispatchEvent(new Event('disposeTooltip'));
  }
  hideOverlay(el) {
    el.removeClass('loading-spinner-show');
    this._proceed = false;
    document.dispatchEvent(new Event('initFloatingInput')); // az input-float miatt szukseges
    document.dispatchEvent(new Event('initFormValidation'));
    document.dispatchEvent(new Event('initTooltip'));
  }
}

// jQuery.fn.checkout = function() {
//     const instance = new Checkout();
//   }
// ;

jQuery.fn.extend({
  checkout: function() {
    new Checkout();
  }
});

$(document).ready(function() {
  $.fn.checkout();

  document.addEventListener('initTooltip', function() { $('[data-toggle="tooltip"]').tooltip(); }, false);
  document.addEventListener('disposeTooltip', function() { $('[data-toggle="tooltip"]').tooltip('dispose'); }, false);
  document.dispatchEvent(new Event('initTooltip'));
});