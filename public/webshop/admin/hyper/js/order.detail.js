/**
 * @summary     Order detail scriptek
 * @description Edit and handle Shipping, Billing, DeliveryDate info
 * @author      Difiori
 *
 */

'use strict';
let formIsDisplayed = false;

/**
 * This is used to generate the loading animation shown when a button is clicked.
 * Shows or hides the overlay containing the animation.
 */
let overlay = {
    button: {
        showOverlay: function (event) {
            if (proceedX) {
                proceedX = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-success-faded');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceedX = true;
            $element.trigger('click');
            $element.prop('disabled', true);
        },
        hideOverlay: function (element) {
            element.removeClass('text-success-faded');
            element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
            element.prop('disabled', false);
            proceedX = false;
        }
    },
    link: {
        showOverlay: function (event) {
            if (proceedX) {
                proceedX = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceedX = true;
            $element.trigger('click');
            $element.addClass('disabled');
        },
        hideOverlay: function (element) {
            element.removeClass('text-faded');
            element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
            element.removeClass('disabled');
            proceedX = false;
            console.log(element);

        }
    },
};

var Order = {
    submitShippingInfoForm: function (options) {
        return new OrderDetail(options, 'submitShippingInfoForm');
    },
    submitBillingInfoForm: function (options) {
        return new OrderDetail(options, 'submitBillingInfoForm');
    },
    submitDeliveryDate: function (options) {
        return new OrderDetail(options,'submitDeliveryDate');
    },
    submitStatusForm: function (options) {
        return new OrderDetail(options,'submitStatusForm');
    },
    submitPaymentStatusForm: function (options) {
        return new OrderDetail(options,'submitPaymentStatusForm');
    },
};

(function(window, $) {
    var proceed = false;

    // window.OrderDetail = function (op, fname) {
    //     this.$wrapper = $(op.wrapper);
    //     this.$wrapper.on(op.event, op.selector, this[fname].bind(this));
    // };
    window.OrderDetail = function (wrapper) {
        this.$wrapper = wrapper;

        // this.$wrapper.on('submit','.JS--btn-recipient', this.submitShippingInfoForm.bind(this));
        this.$wrapper.on('click','.JS--btn-submitRecipient', this.submitShippingInfoForm.bind(this));
        this.$wrapper.on('click','.JS--btn-submitSender', this.submitBillingInfoForm.bind(this));
        this.$wrapper.on('click','.JS--btn-submitDeliveryDate', this.submitDeliveryDate.bind(this));
        this.$wrapper.on('click','.JS--btn-submitStatus', this.submitStatusForm.bind(this));
        this.$wrapper.on('click','.JS--btn-submitPaymentStatus', this.submitPaymentStatusForm.bind(this));
        this.$wrapper.on('click','.JS--btn-submitComment', this.submitCommentForm.bind(this));

    };

    $.extend(window.OrderDetail.prototype, {

        submitShippingInfoForm: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            let $form = $element.closest('.JS--recipientWrapper').find('.JS--form');
            console.log($element);
            $element.addClass('text-success-faded');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            // $form.trigger('submit');
            $element.prop('disabled', true);

            // let $wrapper = $element.closest('.JS--recipientWrapper');
            let $contentBlock = $form.closest('.JS--recipientContentBlock');
            let $url = $form.attr('action');
            console.log('form action url:' + $url);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    // $contentBlock.html(data);

                    $contentBlock.closest('.modal').modal('hide');
                    window.location.reload(true);

                    formIsDisplayed = false;
                    proceed = false;

                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                    $element.removeClass('text-success-faded');  // $element.html('');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                    proceed = false;
                }
            });
        },
        submitBillingInfoForm: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            let $form = $element.closest('.JS--senderWrapper').find('.JS--form');
            console.log($element);
            $element.addClass('text-success-faded');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            // $form.trigger('submit');
            $element.prop('disabled', true);

            // let $wrapper = $element.closest('.JS--senderWrapper');
            let $contentBlock = $form.closest('.JS--senderContentBlock');
            let $url = $form.attr('action');
            console.log('form action url:' + $url);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    // $contentBlock.html(data);

                    $contentBlock.closest('.modal').modal('hide');
                    window.location.reload(true);

                    formIsDisplayed = false;
                    proceed = false;

                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                    $element.removeClass('text-success-faded');  // $element.html('');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                    proceed = false;
                }
            });
        },
        submitDeliveryDate: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            let url = $element.data('url');

            $element.addClass('text-success-faded');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.prop('disabled', true);

            let $wrapper = $element.closest('.JS--deliveryDateContainer');
            let $form = $wrapper.find('[data-wrapper-delivery-date-form]');

            $.ajax({
                url: url,
                method: 'POST',
                data: $form.serialize(),
                success: function (data, jqXHR) {
                    // To display the Symfony Profiler page:
                    $form.after(data);

                    // console.log($form.serialize());
                    $form.after(jqXHR.responseText);

                    $wrapper.closest('.modal').modal('hide');
                    window.location.reload(true);

                    proceed = false;
                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                },
                error: function (jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                    $element.removeClass('text-success-faded');  // $element.html('');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                    proceed = false;
                }


                // success: function(data) {
                //     // $contentBlock.html(data);
                //
                //     $contentBlock.closest('.modal').modal('hide');
                //     window.location.reload(true);
                //
                //     formIsDisplayed = false;
                //     proceed = false;
                //
                //     $element.removeClass('text-success-faded');
                //     $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                //     $element.prop('disabled', false);
                // },
                // error: function(jqXHR) {
                //     $form.replaceWith(jqXHR.responseText);
                //     $element.removeClass('text-success-faded');  // $element.html('');
                //     $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                //     $element.prop('disabled', false);
                //     proceed = false;
                // }
            });
        },
        submitStatusForm: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-success-faded');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.prop('disabled', true);

            let $wrapper = $element.closest('.JS--statusWrapper');
            let $form = $wrapper.find('.JS--statusForm');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {

                    // $form.after(data);

                    $wrapper.closest('.modal').modal('hide');
                    window.location.reload(true);

                    proceed = false;

                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                    proceed = false;
                }
            });
        },
        submitPaymentStatusForm: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-success-faded');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.prop('disabled', true);

            let $wrapper = $element.closest('.JS--paymentStatusWrapper');
            let $form = $wrapper.find('.JS--paymentStatusForm');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {

                    // $form.after(data);
                    $wrapper.closest('.modal').modal('hide');
                    window.location.reload(true);

                    proceed = false;

                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                    proceed = false;
                }
            });
        },
        submitCommentForm: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            let $form = $element.closest('.JS--commentWrapper').find('.JS--form');
            $element.addClass('text-success-faded');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            // $element.trigger('click');
            // $form.trigger('submit');
            $element.prop('disabled', true);

            // let $wrapper = $element.closest('.JS--recipientWrapper');
            let $contentBlock = $form.closest('.JS--commentContentBlock');
            let $url = $form.attr('action');
            console.log('form action url:' + $url);
            // alert('form action url:' + $url);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    // $contentBlock.html(data);

                    window.location.reload(true);
                    proceed = false;

                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                    proceed = false;
                }
            });
        },
        
        /**
         * It is used both by Recipient and Sender forms
         */
        handleSubmitZipAndGetCity: function(event) {
            let $element = $(event.currentTarget);
            let $form = $element.closest('form');

            $.ajax({
                url: $element.data('url'),
                type: 'GET',
                data: { zip: $element.val() },
                dataType: 'json',
                success: function (data) {
                    console.log(data.success);
                    if (data.success == true) {
                        $form.find('.JS--recipientForm-province').val(data.province);
                        $form.find('.JS--recipientForm-city').val(data.city);

                        $form.find('.JS--senderForm-province').val(data.province);
                        $form.find('.JS--senderForm-city').val(data.city);
                    }
                },
                error: function(jqXHR) {
                    // alert(jqXHR.responseText);
                }
            });

        },
    });
})(window, jQuery);


