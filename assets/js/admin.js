/**
 * --------------------------------------------------------------------------
 * admin.js - similar to Shopify theme.js
 *
 * Dependencies: lodash.js (Lodash Core)
 * --------------------------------------------------------------------------
 */
;
import Notify from './alerts/notify'

window.theme = window.theme || {};

theme.Sections = function Sections() {
  this.constructors = {};
  this.instances = [];

  // $(document)
  //     .on('stulipan:section:load', this._onSectionLoad.bind(this))
  //     .on('stulipan:section:unload', this._onSectionUnload.bind(this))
  //     .on('stulipan:section:select', this._onSelect.bind(this))
  //     .on('stulipan:section:deselect', this._onDeselect.bind(this))
  //     .on('stulipan:block:select', this._onBlockSelect.bind(this))
  //     .on('stulipan:block:deselect', this._onBlockDeselect.bind(this));
};

theme.Sections.prototype = _.assignIn({}, theme.Sections.prototype, {
  _createInstance: function(container, constructor) {
    var $container = $(container);
    var id = $container.attr('data-section-id');
    var type = $container.attr('data-section-type');

    constructor = constructor || this.constructors[type];

    if (_.isUndefined(constructor)) {
      return;
    }

    var instance = _.assignIn(new constructor(container), {   // E.g.: new AddToCart(container), because constructor here received this value: theme.CartSection
      id: id,
      type: type,
      container: container
    });

    this.instances.push(instance);
  },

  // _onSectionLoad: function(evt) {
  //   var container = $('[data-section-id]', evt.target)[0];
  //   if (container) {
  //     this._createInstance(container);
  //   }
  // },
  //
  // _onSectionUnload: function(evt) {
  //   this.instances = _.filter(this.instances, function(instance) {
  //     var isEventInstance = instance.id === evt.detail.sectionId;
  //
  //     if (isEventInstance) {
  //       if (_.isFunction(instance.onUnload)) {
  //         instance.onUnload(evt);
  //       }
  //     }
  //
  //     return !isEventInstance;
  //   });
  // },
  //
  // _onSelect: function(evt) {
  //   // eslint-disable-next-line no-shadow
  //   var instance = _.find(this.instances, function(instance) {
  //     return instance.id === evt.detail.sectionId;
  //   });
  //
  //   if (!_.isUndefined(instance) && _.isFunction(instance.onSelect)) {
  //     instance.onSelect(evt);
  //   }
  // },
  //
  // _onDeselect: function(evt) {
  //   // eslint-disable-next-line no-shadow
  //   var instance = _.find(this.instances, function(instance) {
  //     return instance.id === evt.detail.sectionId;
  //   });
  //
  //   if (!_.isUndefined(instance) && _.isFunction(instance.onDeselect)) {
  //     instance.onDeselect(evt);
  //   }
  // },
  //
  // _onBlockSelect: function(evt) {
  //   // eslint-disable-next-line no-shadow
  //   var instance = _.find(this.instances, function(instance) {
  //     return instance.id === evt.detail.sectionId;
  //   });
  //
  //   if (!_.isUndefined(instance) && _.isFunction(instance.onBlockSelect)) {
  //     instance.onBlockSelect(evt);
  //   }
  // },
  //
  // _onBlockDeselect: function(evt) {
  //   // eslint-disable-next-line no-shadow
  //   var instance = _.find(this.instances, function(instance) {
  //     return instance.id === evt.detail.sectionId;
  //   });
  //
  //   if (!_.isUndefined(instance) && _.isFunction(instance.onBlockDeselect)) {
  //     instance.onBlockDeselect(evt);
  //   }
  // },

  register: function(type, constructor) {   // 'constructor' is a function
    this.constructors[type] = constructor;

    $('[data-section-type=' + type + ']').each(
        function(index, element) {
          this._createInstance(element, constructor);
        }.bind(this)
    );
  }
});

theme.proceed = false;

theme.LoadingOverlay = (function() {
  function show(el, eventText) {
    el.addClass('loading-spinner-show');
    theme.proceed = true;
    el.trigger(eventText);
  }

  function hide(el) {
    el.removeClass('loading-spinner-show');
    theme.proceed = false;
  }

  return {
    show: show,
    hide: hide
  };
})();

class FormValidation {
  constructor(formWrapper, constraints) {
    this.formWrapper = formWrapper;
    this.constraints = constraints;
    this.isWithError = false;
    this.formElements = 'input:not([type=hidden]), textarea, select';

    // Listen for the event.
    document.addEventListener('initFormValidation', function (e) {
      this.initFormValidation();
    }.bind(this), false);

    this.initFormValidation();
  }

  initFormValidation() {
    let form = document.querySelector(this.formWrapper);
    let inputs = form.querySelectorAll(this.formElements)
    for (var i = 0; i < inputs.length; ++i) {
      inputs.item(i).addEventListener('change', function(e) {
        var errors = validate(form, this.constraints, {fullMessages: false}) || {};
        this.showErrorsForInput(e.currentTarget, errors[e.currentTarget.name])
        !Object.keys(errors).length && (this.isWithError = false);
      }.bind(this));
    }
  }

  validateForm() {
    let form = document.querySelector(this.formWrapper);
    let inputs = form.querySelectorAll(this.formElements)
    for (var i = 0; i < inputs.length; ++i) {
      var errors = validate(form, this.constraints, {fullMessages: false}) || {};
      this.showErrorsForInput(inputs.item(i), errors[inputs.item(i).name])
      !Object.keys(errors).length && (this.isWithError = false);
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
    this.isWithError || (this.isWithError = true); /////////////////////
  }

  hasError() {
    return this.isWithError;
  }
}

theme.Order = (function () {
  var selectors = {
    BODY: 'body',
    ALERT: '.JS--Wrapper-alert',
    ORDER_WRAPPER: '.JS--Wrapper-orderDetail',

    RECIPIENT_WRAPPER: '.JS--Wrapper-recipient',
    RECIPIENT_BODY: '.JS--Wrapper-recipientBody',
    RECIPIENT_FORM: '.JS--Wrapper-recipientForm',
    RECIPIENT_SUBMIT_BUTTON: '.JS--Button-submitRecipient',

    SENDER_WRAPPER: '.JS--Wrapper-sender',
    SENDER_BODY: '.JS--Wrapper-senderBody',
    SENDER_FORM: '.JS--Wrapper-senderForm',
    SENDER_SUBMIT_BUTTON: '.JS--Button-submitSender',

    ORDER_CONFIRMATION_SEND_BUTTON: '.JS--Button-sendOrderConfirmation',
    ORDER_CONFIRMATION_BODY: '.JS--Wrapper-orderConfirmationBody',

    SHIPPING_CONFIRMATION_SEND_BUTTON: '.JS--Button-sendShippingConfirmation',
    SHIPPING_CONFIRMATION_BODY: '.JS--Wrapper-shippingConfirmationBody',

    MARK_AS_FULFILLED_BUTTON: '.JS--Button-markAsFulfilled',
    ORDER_STATUS_WRAPPER: '.JS--Wrapper-orderStatus',
    ORDER_STATUS_BODY: '.JS--Wrapper-orderStatusBody',

    MARK_AS_PAID_BUTTON: '.JS--Button-markAsPaid',
    PAYMENT_STATUS_WRAPPER: '.JS--Wrapper-paymentStatus',
    PAYMENT_STATUS_BODY: '.JS--Wrapper-paymentStatusBody',
    // PAYMENT_STATUS_FORM: '.JS--Wrapper-paymentStatusForm',
    // PAYMENT_STATUS_SUBMIT_BUTTON: '.JS--Button-submitPaymentStatus',

    CANCEL_ORDER_BUTTON: '.JS--Button-cancelOrder',
    CANCEL_ORDER_WRAPPER: '.JS--Wrapper-cancelOrder',
    CANCEL_ORDER_BODY: '.JS--Wrapper-cancelOrderBody',

    COMMENT_WRAPPER: '.JS--Wrapper-comment',
    COMMENT_BODY: '.JS--Wrapper-commentBody',
    COMMENT_FORM: '.JS--Wrapper-commentForm',
    COMMENT_SUBMIT_BUTTON: '.JS--Button-submitComment',
  };
  const scrollUp = { block: 'start', behavior: 'smooth'};
  var errors = {
    recipient: false,
    sender: false,
    comment: false,
    paymentStatus: false,
  };
  var ajaxLoading = false;

  function Order(container) {
    this.$container = $(container);

    this.$container
        .on('click', selectors.RECIPIENT_SUBMIT_BUTTON, this.submitRecipientForm.bind(this))
        .on('click', selectors.SENDER_SUBMIT_BUTTON, this.submitSenderForm.bind(this))
        .on('click', selectors.COMMENT_SUBMIT_BUTTON, this.submitCommentForm.bind(this))

        .on('click', selectors.ORDER_CONFIRMATION_SEND_BUTTON, this.sendOrderConfirmation.bind(this))
        .on('click', selectors.SHIPPING_CONFIRMATION_SEND_BUTTON, this.sendShippingConfirmation.bind(this))
        .on('click', selectors.MARK_AS_FULFILLED_BUTTON, this.markAsFulfilled.bind(this))
        .on('click', selectors.MARK_AS_PAID_BUTTON, this.markAsPaid.bind(this))
        .on('click', selectors.CANCEL_ORDER_BUTTON, this.cancelOrder.bind(this))

        .on('click change', selectors.ORDER_WRAPPER, this.preventInteraction.bind(this))
    ;

    $(document).ajaxStart(function() {
      ajaxLoading = true;
      document.dispatchEvent(new Event('disposeTooltip'));
    });
    $(document).ajaxStop(function() {
      ajaxLoading = false;
      document.dispatchEvent(new Event('initFloatingInput'));
      document.dispatchEvent(new Event('initFormValidation'));
      document.dispatchEvent(new Event('initTooltip'));
    });

    if ($(selectors.RECIPIENT_FORM).length) {
      this.recipientValidator = new FormValidation(selectors.RECIPIENT_FORM, recipientConstraints);
    }
    if ($(selectors.SENDER_FORM).length) {
      this.senderValidator = new FormValidation(selectors.SENDER_FORM, senderConstraints);
    }
    if ($(selectors.COMMENT_FORM).length) {
      this.commentValidator = new FormValidation(selectors.COMMENT_FORM, commentConstraints);
    }
    // if ($(selectors.SHIPPING_FORM).length) {
    //   this.shippingValidator = new FormValidation(selectors.SHIPPING_FORM, shippingConstraints);
    // }
    // if ($(selectors.DELIVERY_DATE_FORM).length) {
    //   this.deliveryDateValidator = new FormValidation(selectors.DELIVERY_DATE_FORM, deliveryDateConstraints);
    // }
    // if ($(selectors.PAYMENT_FORM).length) {
    //   this.paymentValidator = new FormValidation(selectors.PAYMENT_FORM, paymentConstraints);
    // }
    // if ($(selectors.ACCEPT_TERMS_FORM).length) {
    //   this.acceptTermsValidator = new FormValidation(selectors.ACCEPT_TERMS_FORM, acceptTermsConstraints);
    // }
  }

  // Order.prototype = _.assignIn({}, Order.prototype, {
  $.extend(Order.prototype, {

    _disableFormElements: function () {
      // console.log('disable');
      $(selectors.BODY).find("input, select, form").prop('readOnly', true);
    },

    preventInteraction(e) {
      if (ajaxLoading) {
        e.preventDefault();
      }
    },

    submitRecipientForm: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      if (typeof this.recipientValidator !== 'undefined') {
        this.recipientValidator.validateForm();
        errors.recipient = this.recipientValidator.hasError();
      }

      if (errors.recipient) {
        // $(selectors.ORDER_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
        return;
      }

      if (!errors.recipient) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let $wrapper = $(selectors.ORDER_WRAPPER);
        let $recipientForm = $(selectors.RECIPIENT_FORM);
        let $recipientBody = $(selectors.RECIPIENT_BODY);

        let recipientForm = null;

        let a1 = $.ajax({
          url: $recipientForm.attr('action'),
          method: 'POST',
          data: $recipientForm.serialize(),
          context: this
        });

        a1.done(function(data) {
          // $recipientForm.replaceWith(data);
        })
            .fail(function (jqXHR) {
              recipientForm = jqXHR.responseText;
              errors.recipient = true;
              $recipientForm.replaceWith(recipientForm);
            })
            .always(function() {
              document.dispatchEvent(new Event('initFloatingInput'));
              document.dispatchEvent(new Event('initFormValidation'));
              document.dispatchEvent(new Event('initTooltip'));
            }.bind(this));

        $.when(a1).fail(function () {
          theme.LoadingOverlay.hide($el);

          setTimeout(function (){
            if (errors.recipient) {
              // $(selectors.ORDER_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
            }
          }, 500);

        }.bind(this));

        $.when(a1).done(function () {
          $recipientBody.closest('.modal').modal('hide');
          window.location.reload(true);
        }.bind(this));
      }
    },
    submitSenderForm: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      if (typeof this.senderValidator !== 'undefined') {
        this.senderValidator.validateForm();
        errors.sender = this.senderValidator.hasError();
      }

      if (errors.sender) {
        // $(selectors.ORDER_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
        return;
      }

      if (!errors.sender) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let $wrapper = $(selectors.ORDER_WRAPPER);
        let $senderForm = $(selectors.SENDER_FORM);
        let $senderBody = $(selectors.SENDER_BODY);

        let senderForm = null;

        let a1 = $.ajax({
          url: $senderForm.attr('action'),
          method: 'POST',
          data: $senderForm.serialize(),
          context: this
        });

        a1.done(function(data) {
          // $recipientForm.replaceWith(data);
        })
            .fail(function (jqXHR) {
              senderForm = jqXHR.responseText;
              errors.sender = true;
              $senderForm.replaceWith(senderForm);
            })
            .always(function() {
              document.dispatchEvent(new Event('initFloatingInput'));
              document.dispatchEvent(new Event('initFormValidation'));
              document.dispatchEvent(new Event('initTooltip'));
            }.bind(this));

        $.when(a1).fail(function () {
          theme.LoadingOverlay.hide($el);

          setTimeout(function (){
            if (errors.sender) {
              // $(selectors.ORDER_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
            }
          }, 500);

        }.bind(this));

        $.when(a1).done(function () {
          $senderBody.closest('.modal').modal('hide');
          window.location.reload(true);
        }.bind(this));
      }
    },
    sendOrderConfirmation: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      if (true) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let url = $el.data('url');
        let $wrapper = $(selectors.ORDER_WRAPPER);
        let $orderConfirmationBody = $(selectors.ORDER_CONFIRMATION_BODY);

        $.ajax({
          url: url,
          method: 'POST',
          context: this
        }).done(function(data) {
          $orderConfirmationBody.closest('.modal').modal('hide');
          window.location.reload();
          // Notify.success('Email sikeresen elküldve!');
        }).fail(function(jqXHR) {
          let data = JSON.parse(jqXHR.responseText);
          Notify.error(data.error);
        }).always(function() {
          if (performance.navigation.type == performance.navigation.TYPE_RELOAD) {
            theme.LoadingOverlay.hide($el);
          }
        });
      }
    },
    sendShippingConfirmation: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      if (true) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let url = $el.data('url');
        let $wrapper = $(selectors.ORDER_WRAPPER);
        let $shippingConfirmationBody = $(selectors.SHIPPING_CONFIRMATION_BODY);

        $.ajax({
          url: url,
          method: 'POST',
          context: this
        }).done(function(data) {
          $shippingConfirmationBody.closest('.modal').modal('hide');
          window.location.reload();
        }).fail(function(jqXHR) {
          let data = JSON.parse(jqXHR.responseText);
          Notify.error(data.error);
        }).always(function() {
          if (performance.navigation.type == performance.navigation.TYPE_RELOAD) {
            theme.LoadingOverlay.hide($el);
          }
        });
      }
    },
    markAsFulfilled: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      if (true) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let url = $el.data('url');
        let $wrapper = $(selectors.ORDER_WRAPPER);
        let $orderStatusBody = $(selectors.ORDER_STATUS_BODY);
        let isSendEmail = $orderStatusBody.find('#sendShippingConfirmation').prop('checked');
        if (!isSendEmail) {
          url += '?'+$.param({ noShippingConfirmation: 1 });
        }

        $.ajax({
          url: url,
          method: 'POST',
          context: this,
        }).done(function(data) {
          $orderStatusBody.closest('.modal').modal('hide');
          window.location.reload();
        }).fail(function(jqXHR) {
          let data = JSON.parse(jqXHR.responseText);
          Notify.error(data.error);
        }).always(function() {
          if (performance.navigation.type == performance.navigation.TYPE_RELOAD) {
            theme.LoadingOverlay.hide($el);
          }
          // theme.LoadingOverlay.hide($el);
        });
      }
    },
    markAsPaid: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      if (true) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let url = $el.data('url');
        let $wrapper = $(selectors.ORDER_WRAPPER);
        let $paymentStatusBody = $(selectors.PAYMENT_STATUS_BODY);

        $.ajax({
          url: url,
          method: 'POST',
          context: this
        }).done(function(data) {
          $paymentStatusBody.closest('.modal').modal('hide');
          window.location.reload();
        }).fail(function(jqXHR) {
          let data = JSON.parse(jqXHR.responseText);
          Notify.error(data.error);
        }).always(function() {
          if (performance.navigation.type == performance.navigation.TYPE_RELOAD) {
            theme.LoadingOverlay.hide($el);
          }
          // theme.LoadingOverlay.hide($el);
        });
      }
    },
    cancelOrder: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      if (true) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let url = $el.data('url');
        let $wrapper = $(selectors.ORDER_WRAPPER);
        let $cancelOrderBody = $(selectors.CANCEL_ORDER_BODY);

        $.ajax({
          url: url,
          method: 'POST',
          context: this
        }).done(function(data) {
          $cancelOrderBody.closest('.modal').modal('hide');
          window.location.reload();
        }).fail(function(jqXHR) {
          let data = JSON.parse(jqXHR.responseText);
          Notify.error(data.error);
        }).always(function() {
          if (performance.navigation.type == performance.navigation.TYPE_RELOAD) {
            theme.LoadingOverlay.hide($el);
            $cancelOrderBody.closest('.modal').modal('hide');
          }
        });
      }
    },
    submitCommentForm: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      if (typeof this.commentValidator !== 'undefined') {
        this.commentValidator.validateForm();
        errors.comment = this.commentValidator.hasError();
      }

      if (errors.comment) {
        // $(selectors.ORDER_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
        return;
      }

      if (!errors.comment) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let $wrapper = $(selectors.ORDER_WRAPPER);
        let $commentForm = $(selectors.COMMENT_FORM);
        let $commentBody = $(selectors.COMMENT_BODY);

        let commentForm = null;

        let a1 = $.ajax({
          url: $commentForm.attr('action'),
          method: 'POST',
          data: $commentForm.serialize(),
          context: this
        });

        a1.done(function(data) {
          // $recipientForm.replaceWith(data);
        })
            .fail(function (jqXHR) {
              commentForm = jqXHR.responseText;
              errors.comment = true;
              $commentForm.replaceWith(commentForm);
            })
            .always(function() {
              document.dispatchEvent(new Event('initFloatingInput'));
              document.dispatchEvent(new Event('initFormValidation'));
              document.dispatchEvent(new Event('initTooltip'));
            }.bind(this));

        $.when(a1).fail(function () {
          theme.LoadingOverlay.hide($el);

          setTimeout(function (){
            if (errors.comment) {
              // $(selectors.ORDER_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
            }
          }, 500);

        }.bind(this));

        $.when(a1).done(function () {
          window.location.reload(true);
        }.bind(this));
      }
    },

    submitPaymentStatusForm: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      // if (typeof this.senderValidator !== 'undefined') {
      //   this.senderValidator.validateForm();
      //   errors.sender = this.senderValidator.hasError();
      // }

      if (errors.paymentStatus) {
        // $(selectors.ORDER_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
        return;
      }

      if (!errors.paymentStatus) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let url = $el.data('url');
        let $wrapper = $(selectors.ORDER_WRAPPER);
        let $paymentStatusForm = $(selectors.PAYMENT_STATUS_FORM);
        let $paymentStatusBody = $(selectors.PAYMENT_STATUS_BODY);

        let paymentStatusForm = null;

        let a1 = $.ajax({
          url: $paymentStatusForm.attr('action'),
          method: 'POST',
          data: $paymentStatusForm.serialize(),
          context: this
        });

        a1.done(function(data) {
          // $recipientForm.replaceWith(data);
        })
            .fail(function (jqXHR) {
              paymentStatusForm = jqXHR.responseText;
              errors.paymentStatus = true;
              $paymentStatusForm.replaceWith(paymentStatusForm);
            })
            .always(function() {
              document.dispatchEvent(new Event('initFloatingInput'));
              document.dispatchEvent(new Event('initFormValidation'));
              document.dispatchEvent(new Event('initTooltip'));
            }.bind(this));

        $.when(a1).fail(function () {
          theme.LoadingOverlay.hide($el);

          setTimeout(function (){
            if (errors.paymentStatus) {
              // $(selectors.ORDER_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
            }
          }, 500);

        }.bind(this));

        $.when(a1).done(function () {
          $paymentStatusBody.closest('.modal').modal('hide');
          window.location.reload(true);
        }.bind(this));
      }
    },
  });

  return Order;

})();
theme.Analytics = (function () {
  var selectors = {
    BODY: 'body',
    ALERT: '.JS--Wrapper-alert',
    ANALYTICS_WRAPPER: '.JS--Wrapper-analytics',

    EXPORT_REPORT_DOWNLOAD_BUTTON: '.JS--Button-downloadExportReport',
    EXPORT_REPORT_BODY: '.JS--Wrapper-exportReportBody',
  };
  const scrollUp = { block: 'start', behavior: 'smooth'};
  var ajaxLoading = false;

  function Analytics(container) {
    this.$container = $(container);

    this.$container
        .on('click', selectors.EXPORT_REPORT_DOWNLOAD_BUTTON, this.downloadReport.bind(this))

        .on('click change', selectors.ANALYTICS_WRAPPER, this.preventInteraction.bind(this))
    ;

    $(document).ajaxStart(function() {
      ajaxLoading = true;
      document.dispatchEvent(new Event('disposeTooltip'));
    });
    $(document).ajaxStop(function() {
      ajaxLoading = false;
      // document.dispatchEvent(new Event('initFloatingInput'));
      // document.dispatchEvent(new Event('initFormValidation'));
      document.dispatchEvent(new Event('initTooltip'));
    });
  }

  // Analytics.prototype = _.assignIn({}, Analytics.prototype, {
  $.extend(Analytics.prototype, {

    _disableFormElements: function () {
      $(selectors.BODY).find("input, select, form").prop('readOnly', true);
    },

    preventInteraction(e) {
      if (ajaxLoading) {
        e.preventDefault();
      }
    },

    downloadReport: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      let url = $el.data('url');
      let $exportReportBody = $(selectors.EXPORT_REPORT_BODY);

      window.location.href = url;
      $exportReportBody.closest('.modal').modal('hide');
    },

  });

  return Analytics;

})();

$(document).ready(function() {
  var sections = new theme.Sections();
  sections.register('orderDetail-page', theme.Order);
  sections.register('sales-over-time', theme.Analytics);
});

theme.init = function() {
  document.addEventListener('initTooltip', function() { $('[data-toggle="tooltip"]').tooltip(); }, false);
  document.addEventListener('disposeTooltip', function() { $('[data-toggle="tooltip"]').tooltip('dispose'); }, false);
  document.dispatchEvent(new Event('initTooltip'));
};

$(theme.init);