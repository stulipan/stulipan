/**
 * --------------------------------------------------------------------------
 * store.js - similar to Shopify theme.js
 *
 * Dependencies: lodash.js (Lodash Core)
 * --------------------------------------------------------------------------
 */
;
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

  _onSectionLoad: function(evt) {
    var container = $('[data-section-id]', evt.target)[0];
    if (container) {
      this._createInstance(container);
    }
  },

  _onSectionUnload: function(evt) {
    this.instances = _.filter(this.instances, function(instance) {
      var isEventInstance = instance.id === evt.detail.sectionId;

      if (isEventInstance) {
        if (_.isFunction(instance.onUnload)) {
          instance.onUnload(evt);
        }
      }

      return !isEventInstance;
    });
  },

  _onSelect: function(evt) {
    // eslint-disable-next-line no-shadow
    var instance = _.find(this.instances, function(instance) {
      return instance.id === evt.detail.sectionId;
    });

    if (!_.isUndefined(instance) && _.isFunction(instance.onSelect)) {
      instance.onSelect(evt);
    }
  },

  _onDeselect: function(evt) {
    // eslint-disable-next-line no-shadow
    var instance = _.find(this.instances, function(instance) {
      return instance.id === evt.detail.sectionId;
    });

    if (!_.isUndefined(instance) && _.isFunction(instance.onDeselect)) {
      instance.onDeselect(evt);
    }
  },

  _onBlockSelect: function(evt) {
    // eslint-disable-next-line no-shadow
    var instance = _.find(this.instances, function(instance) {
      return instance.id === evt.detail.sectionId;
    });

    if (!_.isUndefined(instance) && _.isFunction(instance.onBlockSelect)) {
      instance.onBlockSelect(evt);
    }
  },

  _onBlockDeselect: function(evt) {
    // eslint-disable-next-line no-shadow
    var instance = _.find(this.instances, function(instance) {
      return instance.id === evt.detail.sectionId;
    });

    if (!_.isUndefined(instance) && _.isFunction(instance.onBlockDeselect)) {
      instance.onBlockDeselect(evt);
    }
  },

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
    notyf.dismissAll();
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
          notyf.success(AlertMessages.PRODUCT_ADDED);
          theme.LoadingOverlay.hide($el);
          $sidebar.sidebar('show');
        }, 500);

      }).fail(function (jqXHR) {
        $form.replaceWith(jqXHR.responseText);
        theme.LoadingOverlay.hide($el);
        notyf.error(AlertMessages.WARNING_NOT_ENOUGH_STOCK);
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
    CART_ITEM                 : '.JS--Cart-item',
    REMOVE_ITEM_BUTTON        : '.JS--Button-removeItem',
    ITEM_QUANTITY_BODY        : '.JS--Wrapper-itemQuantityBody',
    ITEM_QUANTITY_SELECT      : '.JS--Select-itemQuantitySelect',

  };

  function Cart(container) {
    this.$container = $(container);

    this.$container
        .on('click', selectors.ADD_GIFT_TO_CART_BUTTON, this.addGiftToCart.bind(this))
        .on('click', selectors.REMOVE_ITEM_BUTTON, this.removeItem.bind(this))
        .on('change', selectors.ITEM_QUANTITY_SELECT, this.setItemQuantity.bind(this))
    ;
  }

  // AddToCart.prototype = _.assignIn({}, AddToCart.prototype, {
  $.extend(Cart.prototype, {

    _disableFormElements: function() {
      $(selectors.CART_BODY).find("*", "form").prop('disabled', true);
    },

    addGiftToCart: function(e) {
      e.preventDefault();
      this._disableFormElements();

      let $el = $(e.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      theme.LoadingOverlay.show($el, 'click');
      // $el.addClass('loading-spinner-show');
      // theme.proceed = true;

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
          notyf.success(AlertMessages.PRODUCT_ADDED);
        }, 500);
      }).fail(function (jqXHR) {
        // $cartBody.replaceWith(jqXHR.responseText);
        notyf.error(jqXHR.responseJSON);
      }).always(()=>{
        theme.LoadingOverlay.hide($el);
      });
    },
    /**
     * Removes an Item from the Cart
     * Makes an Ajax call, which if successful replaces the whole Cart body
     */
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
        notyf.error(AlertMessages.ERROR_AJAX_FAILED);
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
        notyf.error(AlertMessages.WARNING_NOT_ENOUGH_STOCK);
      }).always(function() {
        theme.LoadingOverlay.hide($itemQuantityBody);
      });
      // This is executed way before Ajax call is completed,
      // BUT this way the form is yet not disabled when submitted.
      this._disableFormElements();

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
      //   // notyf.error(jqXHR.responseText);
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
        notyf.error(jqXHR.responseText);
        $cartBody.removeClass('loading-spinner-show');
      })
      ;

    },

  });

  return SidebarCart;

})();

$(document).ready(function() {
  var sections = new theme.Sections();
  // Used on Product page
  sections.register('addToCart-template', theme.AddToCart);
  sections.register('cartBadge-template', theme.CartBadgeSection);
  sections.register('sidebarCart-template', theme.SidebarCartSection);
  // Used on the Cart page
  sections.register('cart-template', theme.CartSection);

});

theme.init = function() {
  // console.log('init');
  // new theme.AddToCart;
};

$(theme.init);