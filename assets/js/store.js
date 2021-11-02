/**
 * --------------------------------------------------------------------------
 * store.js - similar to Shopify theme.js
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

theme.DatePicker = (function () {
  const selectors = {
    DELIVERY_DATE_WRAPPER: '.JS--Wrapper-deliveryDate',
    DELIVERY_DATE_FORM: '.JS--Wrapper-deliveryDateForm',
    DELIVERY_DATE_BODY: '.JS--dateWrapper',
    DELIVERY_DATE_INTERVAL_BODY: '.JS--deliveryDateContainer',

    DELIVERY_DATE_DRP_BUTTON: '.JS--Button-clickDate',
    DELIVERY_DATE_GENERATED_DATE: "[class*='JS--generatedDate-']",
  };

  function DatePicker(container) {
    this.$container = $(container);

    this.$container
        .on('ready', this.handleDatePicker.bind(this))
    ;
    this.handleDatePicker(selectors);
  }

  $.extend(DatePicker.prototype, {

    handleDatePicker: function(selectors) {
      // Initiliaze DateRangePicker
      const drpConfig = Object.assign({}, drpBaseConfig, {
        // parentEl: ".JS--dateWrapper",
        // displayInline: true,
        opens: 'left',
        drops: 'auto',
        singleDatePicker: true,
        autoApply: true,
        autoUpdateInput: false,
        minDate: moment().add(4, 'hours'),
        maxDate: moment().add(2, 'months'),
      });
      $(selectors.DELIVERY_DATE_DRP_BUTTON).daterangepicker(drpConfig);

      let $wrapper = $(selectors.DELIVERY_DATE_WRAPPER);
      let $vp = $wrapper.find('.vp-checked');
      let hiddenForm = {
        date: {
          element: $wrapper.find('#hidden_deliveryDate'),
          value: $wrapper.find('#hidden_deliveryDate').val(),
          setDateValue: function (value) {
            this.element.val(value);
          },
        },
        interval: {
          element: $wrapper.find('#hidden_deliveryInterval'),
          value: $wrapper.find('#hidden_deliveryInterval').val(),
          setIntervalValue: function (value) {
            this.element.val(value);
          },
        },
        fee: {
          element: $wrapper.find('#hidden_deliveryFee'),
          value: $wrapper.find('#hidden_deliveryFee').val(),
          setFeeValue: function (value) {
            this.element.val(value);
          },
        },
      }

      // Set initial value in DRP
      if (hiddenForm.date.value) {
        $('.JS--Button-clickDate').data('daterangepicker').setStartDate(hiddenForm.date.value);
        $('.JS--Button-clickDate').data('daterangepicker').setEndDate(hiddenForm.date.value);
      }

      let $selectedDate = $wrapper.find('.JS--generatedDate-' + hiddenForm.date.value);
      let $selectedInterval = $wrapper.find('.JS--generatedInterval-' + hiddenForm.date.value);
      let $intervalDropdown = $selectedInterval.find('.JS--intervalDropdown');

      if ($selectedDate.hasClass('d-none')) {
        $selectedDate.removeClass('d-none').addClass('d-temporary');
        $wrapper.find('.JS--showCalendarIcon').hide();
      }
      if ($selectedInterval.hasClass('d-none')) {
        $selectedInterval.removeClass('d-none').addClass('d-temporary');
      }

      let cart = $('.JS--Wrapper-summary');
      let $schedulingPriceBody = cart.find('.JS--Wrapper-schedulingPrice');
      let $amountToPayBody = cart.find('.JS--Wrapper-amountToPayBody');
      let $amountToPayWrapper = cart.find('.JS--Wrapper-amountToPay');

      $wrapper
          .on('keydown click', selectors.DELIVERY_DATE_GENERATED_DATE, pickDeliveryDate.bind(this))
          .on('apply.daterangepicker', selectors.DELIVERY_DATE_DRP_BUTTON, function(e, picker) {
            applySelectedDate(e, picker);
          }.bind(this))
          .on('cancel.daterangepicker', selectors.DELIVERY_DATE_DRP_BUTTON, function(e, picker) {
            cancelSelectedDate(e, picker);
          }.bind(this))
      ;

      // Az idősáv kiválasztásakor elmenti a hidden mezőbe az értéket.
      // Ez akkor triggerelődik, amikor nem kattintunk sem dátumboxra, sem kalendáriumra.
      onSelectInterval($intervalDropdown);

      // $.extend(Checkout.prototype, {
      /**
       * Kezeli melyik dátum boxra lett kattintva, és azt jelöli kiválasztotnak.
       * Továbbá mutatja a dátumhoz tartozó idősávot.
       */
      function pickDeliveryDate(e) {
        if (e.keyCode === 13 || e.type === 'click') {
          let $el = $(e.currentTarget);
          $wrapper = $el.closest('.JS--deliveryDateContainer');
          let $dateWrapper = $el.closest('.JS--dateWrapper');
          let $intervalsWrapper = $wrapper.find('.JS--intervalsWrapper');

          $dateWrapper.find('.vp-checked').removeClass('vp-checked');
          $el.find('.vp').addClass('vp-checked');
          let $intervalModule = $intervalsWrapper.find('.JS--generatedInterval-' + $el.data('date-value'));

          hiddenForm.date.setDateValue($el.data('date-value'));

          // ha a 4-ikre klikkelek amikor az ő temporary, akkor nem csinal semmit
          // amugy visszallitja (ujra mutatja) a CalendarIcont
          if (!($el.hasClass('d-temporary'))) {
            $dateWrapper.find('.d-temporary').removeClass('d-temporary').addClass('d-none');
            $intervalsWrapper.find('.d-temporary').removeClass('d-temporary').addClass('d-none');
            $intervalModule.removeClass('d-none').addClass('d-temporary');
            $dateWrapper.find('.JS--showCalendarIcon').show();
          }

          //minden elozoleg becsekkolt input/select mezot uresre allitok, es torlom a hidden input mezobol is!
          $intervalsWrapper.find('.JS--intervalDropdown').prop('checked', false);
          hiddenForm.interval.setIntervalValue('');

          // Az idősáv kiválasztásakor elmenti a hidden mezőbe az értéket.
          // Ez akkor triggerelődik, amikor kattintunk a dátumboxra (ami nem a kallendárium).
          onSelectInterval($intervalModule.find('.JS--intervalDropdown'));

          // Set daterangepicker to current value
          $('.JS--Button-clickDate').data('daterangepicker').setStartDate($el.data('date-value'));
          $('.JS--Button-clickDate').data('daterangepicker').setEndDate($el.data('date-value'));

        }
      }

      /**
       * Kezeli a kalendáriumra kattintást.
       */
      function applySelectedDate(e, picker) {
        e.preventDefault();
        let $pickerDate = picker.startDate.format('YYYY-MM-DD');
        let $el = $(e.currentTarget);
        $wrapper = $el.closest('.JS--deliveryDateContainer');
        let $dateWrapper = $el.closest('.JS--dateWrapper');
        let $intervalsWrapper = $wrapper.find('.JS--intervalsWrapper');

        hiddenForm.date.setDateValue($pickerDate);

        $dateWrapper.find('.d-temporary').removeClass('d-temporary').addClass('d-none');
        $dateWrapper.find('.vp-checked').removeClass('vp-checked');
        $intervalsWrapper.find('.d-temporary').removeClass('d-temporary').addClass('d-none');

        let $dateModule = $wrapper.find('.JS--generatedDate-' + $pickerDate);
        let $intervalModule = $wrapper.find('.JS--generatedInterval-' + $pickerDate);

        if ($dateModule.is(':hidden')) {  // if it's hidden, de-hide it and make it temporary + hide the CalendarIcon
          $wrapper.find('.JS--showCalendarIcon').hide();
          $dateModule.removeClass('d-none').addClass('d-temporary');
        }
        $dateModule.find('.vp').addClass('vp-checked');
        $intervalModule.removeClass('d-none').addClass('d-temporary');

        if ($dateWrapper.find("[class*='JS--generatedDate-']:not(.d-none)").length <= 3) {
          $wrapper.find('.JS--showCalendarIcon').show();
        }

        //minden elozoleg becsekkolt input/select mezot uresre allitok, es torlom a hidden input mezobol is!
        $intervalsWrapper.find('.JS--intervalDropdown').prop('checked', false);
        hiddenForm.interval.setIntervalValue('');

        // Az idősáv kiválasztásakor elmenti a hidden mezőbe az értéket.
        // Ez akkor triggerelődik, amikor kattintunk a kalendáriumra.
        onSelectInterval($intervalModule.find('.JS--intervalDropdown'));
        // $el.find('.vp-checked')[0].focus();
        $el.closest('.JS--dateWrapper').find('*[tabindex=0]').focus();
      }

      function cancelSelectedDate(e, picker) {
        let $el = $(e.currentTarget);
        $el.val('');
        $el.closest('.JS--dateWrapper').find('*[tabindex=0]').focus();
      }

      function onSelectInterval(dropdown) {
        dropdown.on('change', function () {
          hiddenForm.interval.setIntervalValue($(this).val());
          // retrieve delivery fee from 'data-' attribute
          let dropdownValue = $(this).children('option:selected').data('fee'); //.toString()
          hiddenForm.fee.setFeeValue(dropdownValue);

          $('body').trigger('checkout.summary.updated', [{
            schedulingPrice: dropdownValue
          }]);
        });
      }

    },
  });

    return DatePicker;

})();

/**
 *
 */
theme.AddToCart = (function () {
  var selectors = {
    BODY                      : 'body',
    ADD_TO_CART_BUTTON        : '.JS--Button-addToCart',
    ADD_GIFT_TO_CART_BUTTON   : '.JS--Button-addGiftToCart',
    CART_SIDEBAR              : '#JS--Sidebar-Cart',
    PRODUCT_QUANTITY          : '#cart_add_item_quantity',
  };

  function AddToCart(container) {
    this.$container = $(container);

    this.$container
        .on('click', selectors.ADD_TO_CART_BUTTON, this.addToCart.bind(this))
    ;
  }

  // AddToCart.prototype = _.assignIn({}, AddToCart.prototype, {
  $.extend(AddToCart.prototype, {

    addToCart: function(e) {
      e.preventDefault();

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');
      // $el.addClass('loading-spinner-show');
      // theme.proceed = true;

      let $form = this.$container.find('form');
      let $sidebar = $(selectors.BODY).find(selectors.CART_SIDEBAR);
      let c0 = parseInt($form.find(selectors.PRODUCT_QUANTITY).val());

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        context: this,
      }).done(function (data) {
        $form.replaceWith(data);
        $(selectors.BODY).trigger('cart.updated', [c0]);

        setTimeout(function() {
          Notify.success(AlertMessages.PRODUCT_ADDED);
          theme.LoadingOverlay.hide($el);
          $sidebar.sidebar('show');
        }, 500);

      }).fail(function (jqXHR) {
        $form.replaceWith(jqXHR.responseText);
        theme.LoadingOverlay.hide($el);
        Notify.error(AlertMessages.PRODUCT_NOT_ENOUGH_STOCK);
      });

    },
  });

  return AddToCart;

})();

theme.CartSection = (function () {
  var selectors = {
    BODY                      : 'body',
    ADD_GIFT_TO_CART_BUTTON   : '.JS--Button-addGiftToCart',
    CART_SIDEBAR              : '#JS--Sidebar-Cart',
    CART_WRAPPER              : '.JS--Wrapper-cart',
    CART_BODY                 : '.JS--Wrapper-cartBody',
    CART_ITEM                 : '.JS--Wrapper-cartItem',
    REMOVE_ITEM_BUTTON        : '.JS--Button-removeItem',
    ITEM_QUANTITY_BODY        : '.JS--Wrapper-itemQuantityBody',
    ITEM_QUANTITY_SELECT      : '.JS--Select-itemQuantitySelect',

    GREETING_CARD_WRAPPER     : '.JS--Wrapper-greetingCard',
    GREETING_CARD_BODY        : '.JS--Wrapper-greetingCardBody',
    GREETING_CARD_FORM        : '.JS--Wrapper-greetingCardForm',

    GOTO_STEP1_BUTTON         : '.JS--Button-gotoStep1',

  };
  var ajaxLoading = false;

  function Cart(container) {
    this.$container = $(container);

    this.$container
        .on('click', selectors.ADD_GIFT_TO_CART_BUTTON, this.addGiftToCart.bind(this))
        .on('click', selectors.REMOVE_ITEM_BUTTON, this.removeItem.bind(this))
        .on('change', selectors.ITEM_QUANTITY_SELECT, this.setItemQuantity.bind(this))
        .on('click', selectors.GOTO_STEP1_BUTTON, this.submitGreetingCardForm.bind(this))

        .on('click change', selectors.CART_WRAPPER, this.preventInteraction.bind(this))
    ;

    $(document).ajaxStart(function() {
      ajaxLoading = true;
    });
    $(document).ajaxStop(function() {
      ajaxLoading = false;
    });

  }

  // AddToCart.prototype = _.assignIn({}, AddToCart.prototype, {
  $.extend(Cart.prototype, {

    _disableFormElements: function() {
      $(selectors.BODY).find("input, select, form").prop('readOnly', true);
    },

    preventInteraction(e) {
      if (ajaxLoading) {
        e.preventDefault();
      }
    },

    addGiftToCart: function(e) {
      e.preventDefault();
      if (ajaxLoading) return;

      // this._disableFormElements();

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');

      let $cartBody = $(selectors.CART_WRAPPER).find(selectors.CART_BODY);
      let url = $el.data('url');

      $.ajax({
        url: url,
        method: 'POST',
        context: this,
      }).done(function(data) {
        $cartBody.html(data);
        $(selectors.BODY).trigger('cart.updated', [1]);

        setTimeout(function() {
          // notyf.success(AlertMessages.PRODUCT_ADDED);
          Notify.success(AlertMessages.PRODUCT_ADDED);
        }, 500);
      }).fail(function (jqXHR) {
        // $cartBody.replaceWith(jqXHR.responseText);
        Notify.error(jqXHR.responseJSON);
      }).always(()=>{
        theme.LoadingOverlay.hide($el);
      });
    },

    // Removes an Item from the Cart
    // Makes an Ajax call, which if successful replaces the whole Cart body
    removeItem: function(e) {
      e.preventDefault();
      this._disableFormElements();

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');

      let url = $el.data('url');
      // Gets the Item's quantity from 'data-item-quantity'
      let count = -1 * parseInt($el.closest(selectors.CART_ITEM).data('itemQuantity'));

      $.ajax({
        url: url,
        method: 'DELETE',
        context: this,
      }).done(function(data) {
        $(selectors.CART_BODY).html(data);
        $(selectors.BODY).trigger('cart.updated', [count]);
      }).fail(function(data) {
        Notify.error(AlertMessages.ERROR_AJAX_FAILED);
      }).always(function() {
        theme.LoadingOverlay.hide($el);
      });
    },

    setItemQuantity: function (e) {
      e.preventDefault();

      let $el = $(e.currentTarget),
          $itemQuantityBody = $el.closest(selectors.ITEM_QUANTITY_BODY),
          $form = $itemQuantityBody.find('form')
      ;

      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($itemQuantityBody, 'click');

      let c0 = parseInt($el.closest(selectors.CART_ITEM).data('itemQuantity'));

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        context: this,
      }).done(function (data) {
        $(selectors.CART_BODY).html(data);

        let c1 = parseInt($el.val());
        let count = c1-c0; // calculate quantity difference from c1 (after) to c0 (initial, before)
        $(selectors.BODY).trigger('cart.updated', [count]);

      }).fail(function (jqXHR) {
        $(selectors.CART_BODY).html(jqXHR.responseText);
        Notify.error(AlertMessages.PRODUCT_NOT_ENOUGH_STOCK);
      }).always(function() {
        theme.LoadingOverlay.hide($itemQuantityBody);
      });
      // This is executed way before Ajax call is completed,
      // BUT this way the form is yet not disabled when submitted.
      this._disableFormElements();

    },

    submitGreetingCardForm(e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');

      let url = $el.data('url');
      let $greetingCardForm = $(selectors.GREETING_CARD_FORM);

      $.ajax({
        url: $greetingCardForm.attr('action'),
        method: 'POST',
        data: $greetingCardForm.serialize(),
        context: this
      }).done(function() {
        window.location.href = url;
      }).fail(function(jqXHR) {
        $greetingCardForm.replaceWith(jqXHR.responseText);
        theme.LoadingOverlay.hide($el);
      });
    },

  });

  return Cart;

})();

theme.CartBadgeSection = (function () {
  var selectors = {
    BODY                      : 'body',
    ITEMS_COUNT_BUBBLE        : '.JS--Badge-itemsCount',
  };
  var classNames = {
    show: 'show',
  };

  function CartBadge(container) {
    this.$container = $(container);

    $(selectors.BODY).on(
        'cart.updated', this.refreshItemsCount.bind(this)
    );
  }

  $.extend(CartBadge.prototype, {

    refreshItemsCount: function (e, count) {
      e.preventDefault();
      let $el = $(selectors.ITEMS_COUNT_BUBBLE);

      let newCount = $el.data('itemsCount') + count;
      $el.html(newCount);                 // update html content
      $el.data('itemsCount', newCount);   // update data-items-count content
      newCount ? $el.addClass(classNames.show) : $el.removeClass(classNames.show);

      // let url = '/hu/cart/getItemsCount';
      //
      // $.ajax({
      //   url: url,
      //   method: 'GET',
      //   context: this,
      // }).done(function (count) {
      //   $el.html(count);
      //   count ? $el.addClass(classNames.show) : $el.removeClass(classNames.show);
      // }).fail(function (jqXHR) {
      //   // console.log(jqXHR);
      //   // $el.append(jqXHR.responseText);
      //   // theme.LoadingOverlay.hide($el);
      //   // Notify.error(jqXHR.responseText);
      // })
      // ;
    },

  });

  return CartBadge;

})();

theme.SidebarCartSection = (function () {
  var selectors = {
    BODY                      : 'body',
    CART_SIDEBAR              : '#JS--Sidebar-Cart',
    CART_BODY                 : '.JS--Sidebar-cartBody',
  };

  function SidebarCart(container) {
    this.$container = $(container);

    $(selectors.BODY).on(
        'cart.updated', this._refreshSidebarCart.bind(this)
    );
  }

  // SidebarCart.prototype = _.assignIn({}, SidebarCart.prototype, {
  $.extend(SidebarCart.prototype, {

    _refreshSidebarCart: function(e) {
      e.preventDefault();

      // let $el = $(e.currentTarget);
      let $cartBody = $(selectors.CART_BODY);
      let url = '/hu/cart/getCart';

      $cartBody.addClass('loading-spinner-show');

      $.ajax({
        url: url,
        method: 'GET',
        context: this,
      }).done(function (data) {
        $cartBody.html(data);
        $cartBody.removeClass('loading-spinner-show');
      }).fail(function (jqXHR) {
        console.log(jqXHR);
        // $el.append(jqXHR.responseText);
        // theme.LoadingOverlay.hide($el);
        Notify.error(jqXHR.responseText);
        $cartBody.removeClass('loading-spinner-show');
      })
      ;

    },

  });

  return SidebarCart;

})();

theme.CheckoutSection = (function () {
  var selectors = {
    BODY: 'body',
    ALERT: '.JS--Wrapper-alert',
    CHECKOUT_WRAPPER: '.JS--Wrapper-checkout',
    GOTO_STEP2_BUTTON:  '.JS--Button-gotoStep2',
    GOTO_STEP3_BUTTON: '.JS--Button-gotoStep3',
    GOTO_STEP4_BUTTON: '.JS--Button-gotoThankYou',

    RECIPIENT_WRAPPER: '.JS--Wrapper-recipient',
    RECIPIENT_BODY: '.JS--Wrapper-recipientBody',
    RECIPIENT_FORM: '.JS--Wrapper-recipientForm',
    RECIPIENT_MODAL: '#modal--recipientList',
    RECIPIENT_LIST: '.JS--Wrapper-recipientList',
    REFRESH_RECIPIENT_LIST_BUTTON: '.JS--Button-refreshRecipientList',
    SHOW_RECIPIENT_FORM_BUTTON: '.JS--Button-showRecipientForm',
    PICK_RECIPIENT_BUTTON: '.JS--Button-pickRecipient',
    DELETE_RECIPIENT: '.JS--Button-deleteRecipient',

    CUSTOMER_WRAPPER: '.JS--Wrapper-customer',
    CUSTOMER_BODY: '.JS--Wrapper-customerBody',
    CUSTOMER_FORM: '.JS--Wrapper-customerForm',

    SHIPPING_WRAPPER: '.JS--Wrapper-shipping',
    SHIPPING_FORM: '.JS--Wrapper-shippingForm',
    SHIPPING_CHOICE: '.JS--Wrapper-choice',
    SHIPPING_CHOICE_BUTTON: '.JS--Button-pickChoice',

    DELIVERY_DATE_WRAPPER: '.JS--Wrapper-deliveryDate',
    DELIVERY_DATE_FORM: '.JS--Wrapper-deliveryDateForm',
    DELIVERY_DATE_BODY: '.JS--dateWrapper',
    DELIVERY_DATE_INTERVAL_BODY: '.JS--deliveryDateContainer',


    DELIVERY_DATE_DRP_BUTTON: '.JS--Button-clickDate',
    DELIVERY_DATE_GENERATED_DATE: "[class*='JS--generatedDate-']",


    PAYMENT_WRAPPER: '.JS--Wrapper-payment',
    PAYMENT_FORM: '.JS--Wrapper-paymentForm',
    PAYMENT_CHOICE: '.JS--Wrapper-paymentChoice',
    PAYMENT_CHOICE_BUTTON: '.JS--Button-pickPayment',

    SENDER_WRAPPER: '.JS--Wrapper-sender',
    SENDER_BODY: '.JS--Wrapper-senderBody',
    SENDER_FORM: '.JS--Wrapper-senderForm',
    SENDER_MODAL: '#modal--senderList',
    SENDER_LIST: '.JS--Wrapper-senderList',
    REFRESH_SENDER_LIST_BUTTON: '.JS--Button-refreshSenderList',
    SHOW_SENDER_FORM_BUTTON: '.JS--Button-showSenderForm',
    PICK_SENDER_BUTTON: '.JS--Button-pickSender',
    DELETE_SENDER: '.JS--Button-deleteSender',

    SAME_AS_RECIPIENT: '.JS--Wrapper-sameAsRecipientForm',

    ACCEPT_TERMS_WRAPPER: '.JS--Wrapper-acceptTerms',
    ACCEPT_TERMS_FORM: '.JS--Wrapper-acceptTermsForm',

    SUMMARY_WRAPPER: '.JS--Wrapper-orderSummary',
    SUMMARY_SHIPPING_FEE: '.JS--orderSummary-shippingFee',
    SUMMARY_PAYMENT_BODY: '.JS--orderSummary-paymentBody',
    SUMMARY_PAYMENT_NAME: '.JS--orderSummary-paymentName',
    SUMMARY_PAYMENT_FEE: '.JS--orderSummary-paymentFee',
    SUMMARY_SCHEDULING_PRICE: '.JS--orderSummary-schedulingPrice',
    SUMMARY_AMOUNT_TO_PAY_WRAPPER: '.JS--orderSummary-amountToPay',
    SUMMARY_AMOUNT_TO_PAY_BODY: '.JS--orderSummary-amountToPayBody',

  };
  const scrollUp = { block: 'start', behavior: 'smooth'};
  var errors = {
    recipient: false,
    customer: false,
    shipping: false,
    deliveryDate: false,
    payment: false,
    sender: false,
    acceptTerms: false,
  };
  var ajaxLoading = false;

  function Checkout(container) {
    this.$container = $(container);

    this.$container
        .on('click', selectors.GOTO_STEP2_BUTTON, this.submitRecipientAndCustomer.bind(this))
        .on('click', selectors.GOTO_STEP3_BUTTON, this.submitShippingMethod.bind(this))
        .on('click', selectors.GOTO_STEP4_BUTTON, this.submitPaymentMethod.bind(this))

        .on('click', selectors.SHOW_RECIPIENT_FORM_BUTTON, this.showRecipientForm.bind(this))
        .on('click', selectors.REFRESH_RECIPIENT_LIST_BUTTON, this.refreshRecipientList.bind(this))
        .on('click', selectors.PICK_RECIPIENT_BUTTON, this.pickRecipient.bind(this))
        .on('click', selectors.DELETE_RECIPIENT, this.deleteRecipient.bind(this))

        .on('click', selectors.SHOW_SENDER_FORM_BUTTON, this.showSenderForm.bind(this))
        .on('click', selectors.REFRESH_SENDER_LIST_BUTTON, this.refreshSenderList.bind(this))
        .on('click', selectors.PICK_SENDER_BUTTON, this.pickSender.bind(this))
        .on('click', selectors.DELETE_SENDER, this.deleteSender.bind(this))
        .on('change', selectors.SAME_AS_RECIPIENT, this.handleSenderForm.bind(this))

        .on('change', selectors.SHIPPING_CHOICE_BUTTON, this.markShippingAsSelected.bind(this))
        .on('change', selectors.PAYMENT_CHOICE_BUTTON, this.markPaymentAsSelected.bind(this))

        .on('click change', selectors.CHECKOUT_WRAPPER, this.preventInteraction.bind(this))

    ;
    $(selectors.BODY).on(
        'checkout.summary.updated', this.updateSummary.bind(this)
    );

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
    if ($(selectors.CUSTOMER_FORM).length) {
      this.customerValidator = new FormValidation(selectors.CUSTOMER_FORM, customerConstraints);
    }
    if ($(selectors.SHIPPING_FORM).length) {
      this.shippingValidator = new FormValidation(selectors.SHIPPING_FORM, shippingConstraints);
    }
    if ($(selectors.DELIVERY_DATE_FORM).length) {
      this.deliveryDateValidator = new FormValidation(selectors.DELIVERY_DATE_FORM, deliveryDateConstraints);
    }
    if ($(selectors.SENDER_FORM).length) {
      this.senderValidator = new FormValidation(selectors.SENDER_FORM, senderConstraints);
    }
    if ($(selectors.PAYMENT_FORM).length) {
      this.paymentValidator = new FormValidation(selectors.PAYMENT_FORM, paymentConstraints);
    }
    if ($(selectors.ACCEPT_TERMS_FORM).length) {
      this.acceptTermsValidator = new FormValidation(selectors.ACCEPT_TERMS_FORM, acceptTermsConstraints);
    }
  }

  // Checkout.prototype = _.assignIn({}, Checkout.prototype, {
  $.extend(Checkout.prototype, {

    _disableFormElements: function () {
      // console.log('disable');
      $(selectors.BODY).find("input, select, form").prop('readOnly', true);
    },

    preventInteraction(e) {
      if (ajaxLoading) {
        e.preventDefault();
      }
    },

    submitRecipientAndCustomer: function (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      if (typeof this.recipientValidator !== 'undefined') {
        this.recipientValidator.validateForm();
        errors.recipient = this.recipientValidator.hasError();
      }

      if (typeof this.customerValidator !== 'undefined') {
        this.customerValidator.validateForm();
        errors.customer = this.customerValidator.hasError();
      }

      if (errors.recipient || errors.customer) {
        $(selectors.CHECKOUT_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
        return;
      }

      if (!errors.recipient && !errors.customer) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let url = $el.data('url');
        let $wrapper = $(selectors.CHECKOUT_WRAPPER);
        let $recipientForm = $(selectors.RECIPIENT_FORM);
        let $recipientBody = $(selectors.RECIPIENT_BODY);
        let $customerBody = $(selectors.CUSTOMER_BODY);
        let $customerForm = $(selectors.CUSTOMER_FORM);


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
              errors.recipient = true;
            })
            .always(function() {
              a2.done(function(data){
                $customerForm.replaceWith(data);
              })
                  .fail(function(jqXHR){
                    customerForm = jqXHR.responseText;
                    errors.customer = true;
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
          theme.LoadingOverlay.hide($el);

          setTimeout(function (){
            if (errors.recipient || errors.customer) {
              $(selectors.CHECKOUT_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
            }
          }, 500);

        }.bind(this));

        $.when(a1, a2).done(function () {
          window.location.href = url;
        }.bind(this));
      }
    },

    // Show an empty Recipient form, triggered by 'Új címzett hozáadása' button
    showRecipientForm(e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');

      let url = $el.data('url');
      let $recipientBody = $(selectors.RECIPIENT_BODY);

      $.post({
        url: url,
        context: this,
      }).done(function (data) {
        $recipientBody.html(data);
        theme.LoadingOverlay.hide($el);
        errors.recipient = false;
      }).fail(function () {
        Notify.error(AlertMessages.ERROR_AJAX_FAILED);
        theme.LoadingOverlay.hide($el);
      });
    },

    // Picks a Recipient from the recipient list (modal) and updates the Recipient form with it.
    // Triggered from the modal window by choosing one recipient from the list.
    pickRecipient(e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');

      let $recipientBody = $(selectors.RECIPIENT_BODY);
      let url = $el.attr('href');

      $.post({
        url: url,
        context: this,
      }).done(function (data) {
        $(selectors.REFRESH_RECIPIENT_LIST_BUTTON).trigger('click');
        $recipientBody.html(data);
        errors.recipient = false;
      }).fail(function () {
        Notify.error(AlertMessages.ERROR_AJAX_FAILED);
      }).always(function() {
        $(selectors.RECIPIENT_MODAL).modal('hide');
        theme.LoadingOverlay.hide($el);
      });
    },

    // Triggered from JS code (deleteRecipient)
    refreshRecipientList(e) {
      e.preventDefault();
      let $el = $(e.currentTarget);
      // Ez azert nem kell, mert a refreshRecipientList-et kodbol triggereljuk !!
      // if (theme.proceed) { theme.proceed = false; return; }
      // theme.LoadingOverlay.show($el, 'click');

      let url = $el.data('url');
      // let $wrapper = $el.closest(Wrapper.RECIPIENT);
      let $recipientListBody = $(selectors.RECIPIENT_LIST);
      $.ajax({
        url: url,
        method: 'GET',
        context: this,
      }).done(function(data) {
        $recipientListBody.html(data);
        theme.LoadingOverlay.hide($el);
      }).fail(function() {
        theme.LoadingOverlay.hide($el);
        Notify.error(AlertMessages.ERROR_UNKNOWN);
        // this.recipient.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_UNKNOWN, 'danger');
      });
    },

    deleteRecipient (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');

      let url = $el.data('url');
      // let $wrapper = $el.closest(Wrapper.RECIPIENT);
      let $recipientBody = $(selectors.RECIPIENT_BODY);

      let confirm = window.confirm('Biztosan szeretnéd törölni?');
      if (confirm) {

        $.ajax({
          url: url,
          method: 'GET',
          context: this,
        }).done(function(data) {
          $(selectors.REFRESH_RECIPIENT_LIST_BUTTON).trigger('click');

          $recipientBody.html(data);
          $(selectors.RECIPIENT_MODAL).modal('hide');
          theme.LoadingOverlay.hide($el);
        }).fail(function() {
          theme.LoadingOverlay.hide($el);
          Notify.error(AlertMessages.ERROR_UNKNOWN);
          // this.recipient.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_UNKNOWN, 'danger');
        });

      } else {
        theme.LoadingOverlay.hide($el);
      }
    },

    submitShippingMethod(e){
      e.preventDefault();
      if (ajaxLoading) return;

      if (typeof this.shippingValidator !== 'undefined') {
        this.shippingValidator.validateForm();
        errors.shipping = this.shippingValidator.hasError();
      }

      if (typeof this.deliveryDateValidator !== 'undefined') {
        this.deliveryDateValidator.validateForm();
        errors.deliveryDate = this.deliveryDateValidator.hasError();
      }

      if (errors.shipping || errors.deliveryDate) {
        $(selectors.CHECKOUT_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
        return;
      }

      if (!errors.shipping && !errors.deliveryDate) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let url = $el.data('url');
        let $wrapper = $(selectors.SHIPPING_WRAPPER);
        let $shippingForm = $(selectors.SHIPPING_FORM);
        let $deliveryDateForm = $(selectors.DELIVERY_DATE_FORM);

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
              // this.shipping.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_UNKNOWN, 'danger');
              Notify.error(AlertMessages.ERROR_UNKNOWN);
            })
            .always(function() {
              a2.done(function(data){
                $deliveryDateForm.replaceWith(data);
              })
                  .fail(function(jqXHR){
                    deliveryDateForm = jqXHR.responseText;
                    // this.deliveryDate.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_UNKNOWN, 'danger');
                    Notify.error(AlertMessages.ERROR_UNKNOWN);
                  })
                  .always(function() {
                    $shippingForm.replaceWith(shippingForm);
                    $deliveryDateForm.replaceWith(deliveryDateForm);
                    document.dispatchEvent(new Event('initFloatingInput'));
                  }.bind(this))
            }.bind(this));

        $.when(a1, a2).fail(function () {
          theme.LoadingOverlay.hide($el);
          // $wrapper.find(selectors.ALERT) && $wrapper.find(selectors.ALERT)[0].scrollIntoView(scrollUp);

          if ($wrapper.find(selectors.ALERT)) {
            $wrapper.find(selectors.ALERT)[0].scrollIntoView(scrollUp);
            // $wrapper.find(Wrapper.ALERT)[0].scrollIntoView(scrollUp);
          }
        }.bind(this));

        $.when(a1, a2).done(function () {
          window.location.href = url;
        }.bind(this));
      }
    },

    // Marks the selected Payment method with a coloured background
    markShippingAsSelected(e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      let $wrapper = $(selectors.SHIPPING_WRAPPER);
      let $choiceWrapper = $el.closest(selectors.SHIPPING_CHOICE);
      $choiceWrapper.find('input').prop('checked',true);  // Beleteszi a pipát
      $wrapper.find('.selected').removeClass('selected');
      $choiceWrapper.addClass('selected');
      $(selectors.SHIPPING_FORM).addClass('was-validated');

      $(selectors.BODY).trigger('checkout.summary.updated', [{
        shippingFee: $choiceWrapper.data('shippingFee')
      }]);

      errors.shipping = false;
      // if ($wrapper.find(selectors.ALERT)) {
      //   $wrapper.find(selectors.ALERT).hide();
      // }
    },

    // Show an empty Sender form, triggered by 'Új számlázási cím hozáadása' button
    showSenderForm(e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');

      let url = $el.data('url');
      let $senderForm = $(selectors.SENDER_FORM);

      $.ajax({
        url: url,
        method: 'POST',
        context: this
      }).done(function(data) {
        $senderForm.replaceWith(data);
        theme.LoadingOverlay.hide($el);
        errors.sender = true;
      }).fail(function() {
        Notify.error(AlertMessages.ERROR_AJAX_FAILED);
        theme.LoadingOverlay.hide($el);
        // this.sender.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
      });
    },

    handleSenderForm(e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      let $formWrapper = $(selectors.SAME_AS_RECIPIENT);
      let $senderBody = $(selectors.SENDER_BODY);

      let input = $formWrapper.find('input')[0];
      if (input.checked) {
        $senderBody.removeClass('--active');
        errors.sender = false;
      } else {
        $senderBody.addClass('--active');
      }
    },

    // Picks a Sender from the recipient list (modal) and updates the Sender form with it.
    // Triggered from the modal window by choosing one sender from the list.
    pickSender(e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');

      let $senderForm = $(selectors.SENDER_FORM);
      let url = $el.attr('href');

      $.ajax({
        url: url,
        method: 'POST',
        context: this
      }).done(function(data) {
        $(selectors.REFRESH_SENDER_LIST_BUTTON).trigger('click');
        $senderForm.replaceWith(data);
        errors.sender = false;
      }).fail(function () {
        Notify.error(AlertMessages.ERROR_AJAX_FAILED);
        // this.recipient.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
      }).always(function() {
        $(selectors.SENDER_MODAL).modal('hide');
        theme.LoadingOverlay.hide($el);
      });
    },

    // Triggered from JS code (deleteSender)
    refreshSenderList(e) {
      e.preventDefault();
      let $el = $(e.currentTarget);

      let url = $el.data('url');
      let $senderListBody = $(selectors.SENDER_LIST);
      $.ajax({
        url: url,
        method: 'GET',
        context: this
      }).done(function(data) {
        $senderListBody.html(data);
        theme.LoadingOverlay.hide($el);
      }).fail(function() {
        theme.LoadingOverlay.hide($el);
        Notify.error(AlertMessages.ERROR_UNKNOWN);
        // this.sender.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
      });
    },

    deleteSender (e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');

      let url = $el.data('url');
      let $senderForm = $(selectors.SENDER_FORM);

      let confirm = window.confirm('Biztosan szeretnéd törölni?');
      if (confirm) {
        $.ajax({
          url: url,
          method: 'GET',
          context: this
        }).done(function(data) {
          $(selectors.REFRESH_SENDER_LIST_BUTTON).trigger('click');
          $senderForm.replaceWith(data);
          $(selectors.SENDER_MODAL).modal('hide');
          theme.LoadingOverlay.hide($el);
        }).fail(function() {
          theme.LoadingOverlay.hide($el);
          Notify.error(AlertMessages.ERROR_UNKNOWN);
          // this.sender.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.ERROR_AJAX_FAILED, 'danger');
        });
      } else {
        theme.LoadingOverlay.hide($el);
      }
    },

    // Submits Payment method and Billing address (Sender)
    submitPaymentMethod(e){
      e.preventDefault();
      if (ajaxLoading) return;

      let $sameAsRecipient = $(selectors.SAME_AS_RECIPIENT);
      let sameAsRecipientInput = $sameAsRecipient.find('input')[0];

      if (!sameAsRecipientInput.checked) {
        if (typeof this.senderValidator !== 'undefined') {
          this.senderValidator.validateForm();
          errors.sender = this.senderValidator.hasError();
        }
      }

      if (typeof this.acceptTermsValidator !== 'undefined') {
        this.acceptTermsValidator.validateForm();
        errors.acceptTerms = this.acceptTermsValidator.hasError();
      }

      if (typeof this.paymentValidator !== 'undefined') {
        this.paymentValidator.validateForm();
        errors.payment = this.paymentValidator.hasError();
      }

      if (errors.payment || errors.sender || errors.acceptTerms) {
        $(selectors.CHECKOUT_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
        return;
      }

      if (!errors.payment && !errors.sender && !errors.acceptTerms) {
        this._disableFormElements();

        let $el = $(e.currentTarget);
        if (theme.proceed) { theme.proceed = false; return; }
        theme.LoadingOverlay.show($el, 'click');

        let url = $el.data('url');
        let $paymentForm = $(selectors.PAYMENT_FORM);
        let $senderForm = $(selectors.SENDER_FORM);
        let $acceptTermsForm = $(selectors.ACCEPT_TERMS_FORM);

        let paymentForm = null;
        let senderForm = null;
        let acceptTermsForm = null;

        let a1 = $.ajax({
          url: $paymentForm.attr('action'),
          method: 'POST',
          data: $paymentForm.serialize(),
          context: this
        });

        let a2 = null;
        if (!sameAsRecipientInput.checked) {
          console.log('senderForm');
          a2 = $.ajax({
            url: $senderForm.attr('action'),
            method: 'POST',
            data: $senderForm.serialize(),
            context: this
          });
        }
        if (sameAsRecipientInput.checked) {
          console.log('sameAsRecipient');
          a2 = $.ajax({
            url: $sameAsRecipient.attr('action'),
            method: 'POST',
            // data: $senderForm.serialize(),
            context: this
          });
        }


        let a3 = $.ajax({
          url: $acceptTermsForm.attr('action'),
          method: 'POST',
          data: $acceptTermsForm.serialize(),
          context: this
        });


        a1.done(function(data) {
          $paymentForm.replaceWith(data);
        })
          .fail(function (jqXHR) {
            paymentForm = jqXHR.responseText;
            errors.payment = true;
          })
          .always(function() {

            a2.done(function(data){
              if (!sameAsRecipientInput.checked) {
                $senderForm.replaceWith(data);
              }
            })
              .fail(function(jqXHR){
                if (!sameAsRecipientInput.checked) {
                  senderForm = jqXHR.responseText;
                  errors.sender = true;
                } else {
                  console.log(jqXHR.responseText)
                }
              })
              .always(function() {

                a3.done(function(data){
                  $acceptTermsForm.replaceWith(data);
                })
                  .fail(function(jqXHR){
                    acceptTermsForm = jqXHR.responseText;
                    errors.acceptTerms = true;
                  })
                  .always(function() {
                    $paymentForm.replaceWith(paymentForm);
                    if (!sameAsRecipientInput.checked) {
                      $senderForm.replaceWith(senderForm);
                    }
                    $acceptTermsForm.replaceWith(acceptTermsForm);
                    document.dispatchEvent(new Event('initFloatingInput'));
                    document.dispatchEvent(new Event('initFormValidation'));
                  }.bind(this))

              }.bind(this))

          }.bind(this));

        $.when(a1, a2, a3).fail(function () {
          theme.LoadingOverlay.hide($el);

          setTimeout(function (){
            if (errors.payment) {
              $(selectors.CHECKOUT_WRAPPER).find('.invalid-feedback:not(:empty)').closest('form')[0].scrollIntoView(scrollUp);
            }
          }, 500);

        }.bind(this));

        $.when(a1, a2, a3).done(function () {
          window.location.href = url;
        }.bind(this));
      }
    },

    // Marks the selected Payment method with a coloured background
    markPaymentAsSelected(e) {
      e.preventDefault();
      if (ajaxLoading) return;

      let $el = $(e.currentTarget);
      let $wrapper = $(selectors.PAYMENT_WRAPPER);
      let $choiceWrapper = $el.closest(selectors.PAYMENT_CHOICE);
      $choiceWrapper.find('input').prop('checked',true);  // Beleteszi a pipát
      $wrapper.find('.selected').removeClass('selected');
      $choiceWrapper.addClass('selected');
      $(selectors.PAYMENT_FORM).addClass('was-validated');

      $(selectors.BODY).trigger('checkout.summary.updated', [{
        paymentFee: $choiceWrapper.data('paymentFee'),
        paymentName: $choiceWrapper.data('paymentName')
      }]);

      errors.payment = false;
      // if ($wrapper.find(selectors.ALERT)) {
      //   $wrapper.find(selectors.ALERT).hide();
      // }
    },

    updateSummary: function(e, summary) {
      e.preventDefault();

      // let url = '/hu/cart/getSummary';
      let $wrapper = $(selectors.SUMMARY_WRAPPER);
      let $shippingFee = $(selectors.SUMMARY_SHIPPING_FEE);
      let $schedulingPriceBody = $(selectors.SUMMARY_SCHEDULING_PRICE);
      let $paymentName = $(selectors.SUMMARY_PAYMENT_NAME);
      let $paymentBody = $(selectors.SUMMARY_PAYMENT_BODY);
      let $paymentFee = $(selectors.SUMMARY_PAYMENT_FEE);
      let $amountToPayWrapper = $(selectors.SUMMARY_AMOUNT_TO_PAY_WRAPPER);
      let $amountToPayBody = $(selectors.SUMMARY_AMOUNT_TO_PAY_BODY);

      let updatedAmountToPay =  $amountToPayWrapper.data('itemsPrice');
      let shippingFee = $shippingFee.data('shippingFee');
      let paymentFee = $paymentFee.data('paymentFee');

      // If flowerShopMode, there's no $schedulingPriceBody. We set the schedulingPrice to 0.
      let schedulingPrice = $schedulingPriceBody.length ? $schedulingPriceBody.data('schedulingPrice') : 0;

      if ("undefined" !== typeof summary.shippingFee) {
        console.log(summary.shippingFee);
        $shippingFee.html(summary.shippingFee.toLocaleString("de-DE", {style: "decimal", minimumFractionDigits: 0, useGrouping: true}) + ' Ft');
        $shippingFee.data('shippingFee', summary.shippingFee);
        updatedAmountToPay += summary.shippingFee + paymentFee + schedulingPrice;
      }
      if ("undefined" !== typeof summary.schedulingPrice) {
        $schedulingPriceBody.html(summary.schedulingPrice.toLocaleString("de-DE", {style: "decimal", minimumFractionDigits: 0, useGrouping: true}) + ' Ft');
        $schedulingPriceBody.data('schedulingPrice', summary.schedulingPrice);
        updatedAmountToPay += shippingFee + paymentFee + summary.schedulingPrice;
      }
      if ("undefined" !== typeof summary.paymentFee) {
        $paymentName.html(summary.paymentName);
        $paymentFee.html(summary.paymentFee.toLocaleString("de-DE", {style: "decimal", minimumFractionDigits: 0, useGrouping: true}) + ' Ft');
        $paymentFee.data('paymentFee', summary.paymentFee);
        if (summary.paymentFee == 0) {
          $paymentBody.addClass('d-none');
        } else {
          $paymentBody.removeClass('d-none');
        }
        updatedAmountToPay += shippingFee + summary.paymentFee + schedulingPrice;
      }

      $amountToPayBody.html(updatedAmountToPay.toLocaleString("de-DE", {style: "decimal", minimumFractionDigits: 0, useGrouping: true}) + ' Ft');
    },

  });

  return Checkout;

})();

theme.RegistrationSection = (function () {
  var selectors = {
    BODY: 'body',
    REGISTRATION_WRAPPER: '.JS--Wrapper-registration',
    REGISTRATION_FORM: '.JS--Wrapper-registrationForm',
    REGISTRATION_BUTTON:  '.JS--Button-register',

    LOGIN_FORM: '.JS--Wrapper-loginForm',
    LOGIN_BUTTON:  '.JS--Button-login',
  };
  const scrollUp = { block: 'start', behavior: 'smooth'};
  var errors = {
    login: false,
    registration: false,
  };

  function Registration(container) {
    this.$container = $(container);

    this.$container
        .on('click', selectors.LOGIN_BUTTON, this.submitLoginForm.bind(this))
        .on('click', selectors.REGISTRATION_BUTTON, this.submitRegistrationForm.bind(this))
    ;

    if ($(selectors.LOGIN_FORM).length) {
      this.loginValidation = new FormValidation(selectors.LOGIN_FORM, loginConstraints);
    }
    if ($(selectors.REGISTRATION_FORM).length) {
      this.registrationValidation = new FormValidation(selectors.REGISTRATION_FORM, registrationConstraints);
    }
  }

  $.extend(Registration.prototype, {

    submitLoginForm: function (e) {
      e.preventDefault();
      console.log('login');

      if (typeof this.loginValidation !== 'undefined') {
        this.loginValidation.validateForm();
        errors.login = this.loginValidation.hasError();
      }

      if (!errors.login) {
        $(selectors.LOGIN_FORM).submit();
      }
    },

    submitRegistrationForm: function (e) {
      e.preventDefault();

      if (typeof this.registrationValidation !== 'undefined') {
        this.registrationValidation.validateForm();
        errors.registration = this.registrationValidation.hasError();
      }

      if (!errors.registration) {
        $(selectors.REGISTRATION_FORM).submit();
      }
    },

  });

  return Registration;

})();

$(document).ready(function() {
  var sections = new theme.Sections();

  // Used on Product page
  sections.register('addToCart-template', theme.AddToCart);
  sections.register('cartBadge-template', theme.CartBadgeSection);
  sections.register('sidebarCart-template', theme.SidebarCartSection);

  // Used on the Cart page
  sections.register('cart-template', theme.CartSection);

  // Used on the Checkout pages
  sections.register('checkout-template', theme.CheckoutSection);
  sections.register('datePicker-block', theme.DatePicker);

  // Used on the Registration pages
  sections.register('registration-section', theme.RegistrationSection);
});

theme.init = function() {
  // console.log('init');
  // new theme.AddToCart;
  document.addEventListener('initTooltip', function() { $('[data-toggle="tooltip"]').tooltip(); }, false);
  document.addEventListener('disposeTooltip', function() { $('[data-toggle="tooltip"]').tooltip('dispose'); }, false);
  document.dispatchEvent(new Event('initTooltip'));
};

$(theme.init);