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

    var instance = _.assignIn(new constructor(container), {
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

  register: function(type, constructor) {
    this.constructors[type] = constructor;

    $('[data-section-type=' + type + ']').each(
        function(index, container) {
          this._createInstance(container, constructor);
        }.bind(this)
    );
  }
});

theme.proceed = false;

theme.LoadingOverlay = (function() {
  function showOverlay(el, eventText) {
    el.addClass('btn-loading');
    theme.proceed = true;
    el.trigger(eventText);
  }

  function hideOverlay(el) {
    el.removeClass('btn-loading');
    theme.proceed = false;
  }

  return {
    showOverlay: showOverlay,
    hideOverlay: hideOverlay
  };
})();

theme.AddToCart = (function () {
  var selectors = {
    addToCartButton: '.JS--Button-addToCart',
    body: 'body',
    itemCountBubble: '[data-cart-item-count]',

  };

  function AddToCart(container) {
    this.$container = $(container);
    // console.log(container);

    this.$container.on(
        'click',
        selectors.addToCartButton,
        this._addToCart.bind(this)
    );
  }

  // AddToCart.prototype = _.assignIn({}, AddToCart.prototype, {
  $.extend(AddToCart.prototype, {

    // proceed: false,

    _addToCart: function(evt) {
      evt.preventDefault();

      let $el = $(evt.currentTarget);
      if (theme.proceed) { theme.proceed = false; return; }
      // theme.LoadingOverlay.showOverlay($el, 'click');
      $el.addClass('btn-loading');
      theme.proceed = true;

      let $form = this.$container.find('form');
      // let $formWrapper = $el.closest('.JS--formWrapper');
      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        context: this,
      }).done(function (data) {
        $form.replaceWith(data);
        $(selectors.body).find(selectors.itemCountBubble).html(3);
        theme.LoadingOverlay.hideOverlay($el);
        // this.cart.hideAlert();
        // $wrapperQuantity.removeClass('btn-loading');

      }).fail(function (jqXHR) {
        $form.replaceWith(jqXHR.responseText);
        theme.LoadingOverlay.hideOverlay($el);
        // this.cart.showAlertAt($wrapper.find(Wrapper.ALERT), AlertMessages.WARNING_NOT_ENOUGH_STOCK, 'warning');
        // $wrapperQuantity.removeClass('btn-loading');
      });

      // $(evt.target).closest('form').submit();
    },

  });

  return AddToCart;

})();


$(document).ready(function() {
  var sections = new theme.Sections();
  sections.register('addToCart-template', theme.AddToCart);

});

theme.init = function() {
  // console.log('init');
  // new theme.AddToCart;
};

$(theme.init);