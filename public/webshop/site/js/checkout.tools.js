/**
 * @summary     Saját scriptek
 * @description Inline edit, Delete item
 * @version     0.1
 * @author      Difiori
 *
 */

/**
 * Removes an entry from a table with AJAX
 *
 *   - Delete button must have the 'JS--button-edit' class.
 *   - Enclosing table must have the 'JS--wrapper-table' class.
 *   - Delete button must have the 'data-url' attribute which holds the URL where the request is sent.
 *   - URL must be defined on the server side
 */

// customer: {
//     error: false,
//     showErrorAt: function (position) {
//         position.after(`<div class="JS--alertMessage alert alert-danger mt-4 px-3 px-md-4 rounded-0" role="alert">
//                     <i class="fas fa-exclamation-circle mr-1 text-muted"></i> Nem adtad meg az adataidat vagy valamelyik mezőt nem tölötted ki.
//                 </div>`);
//     },
//     hideError: function (position) {
//         position.find('.JS--alertMessage').hide();
//         position.find('.JS--alertMessage').replaceWith('');
//         this.error = false;
//     },
// },

'use strict';
let proceedX = false;
let formIsDisplayed = false;
let alert = {
    scroll: {
        scrollIntoView: function (position) {
            console.log(position);
            // position[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
        }
    },
    cart: {
        hasSuccess: false,
        hasError: false,
        fetchInitialValue: function () {
            this.hasError = $('.JS--cartWrapper').find('.JS--alertMessage').length > 0 ? true : false;
        },
        showSuccessAt: function (position) {
            if (this.hasError) {
                this.hideError();
            }
            if (!this.hasSuccess) {
                position.after(`<div class="JS--alertMessage alert alert-success mt-4 px-3 px-md-4 rounded-0" role="alert">
                        <i class="fas fa-exclamation-circle mr-1 text-muted"></i> Az ajándékot sikeresen hozzáadtad a kosárhoz! 
                    </div>`);
                this.hasSuccess = true;
            }
            position[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
        },
        hideSuccess: function () {
            if (this.hasSuccess) {
                $('.JS--cartWrapper').find('.JS--alertMessage').replaceWith('');
                this.hasSuccess = false;
            }
        },
        hideError: function () {
            if (this.hasError) {
                $('.JS--cartWrapper').find('.JS--alertMessage').replaceWith('');
                this.hasError = false;
            }
        },
    },
    recipient: {
        hasError: false,
        fetchInitialValue: function () {
            this.hasError = $('.JS--recipientWrapper').find('.JS--alertMessage').length > 0 ? true : false;
        },
        showErrorAt: function (position) {
            if (!this.hasError) {
                position.after(`<div class="JS--alertMessage alert alert-danger mt-4 px-3 px-md-4 rounded-0" role="alert">
                        <i class="fas fa-exclamation-circle mr-1 text-muted"></i> Nem mentetted el címzett adatait! 
                    </div>`);
                this.hasError = true;
            }
            position[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
        },
        hideError: function () {
            if (this.hasError) {
                $('.JS--recipientWrapper').find('.JS--alertMessage').replaceWith('');
                this.hasError = false;
            }
        },
    },
    deliveryDate: {
        hasError: false,
        fetchInitialValue: function () {
            this.hasError = $('.JS--deliveryDateContainer').find('.JS--alertMessage').length > 0 ? true : false;
        },
        showErrorAt: function (position) {
            if (!this.hasError) {
                position.after(`<div class="JS--alertMessage alert alert-danger mt-4 px-3 px-md-4 rounded-0" role="alert">
                        <i class="fas fa-exclamation-circle mr-1 text-muted"></i> Add meg a szállítás idejét! Bizonyosodj meg róla, hogy választottál szállítási idősávot is. !dlkjnsf
                    </div>`);
                this.hasError = true;
            }
            position[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
        },
        hideError: function () {
            if (this.hasError) {
                $('.JS--deliveryDateContainer').find('.JS--alertMessage').replaceWith('');
                this.hasError = false;
            }
        },
    },
    sender: {
        error: false,
        fetchInitialValue: function () {
            this.hasError = $('.JS--senderWrapper').find('.JS--alertMessage').length > 0 ? true : false;
        },
        showErrorAt: function (position) {
            if (!this.hasError) {
                position.after(`<div class="JS--alertMessage alert alert-danger mt-4 px-3 px-md-4 rounded-0" role="alert">
                        <i class="fas fa-exclamation-circle mr-1 text-muted"></i> Nem mentetted el címzett adatait! 
                    </div>`);
                this.hasError = true;
            }
            position[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
        },
        hideError: function () {
            if (this.hasError) {
                $('.JS--senderWrapper').find('.JS--alertMessage').replaceWith('');
                this.hasError = false;
            }
        },
    },
    shipping: {
        error: false,
        fetchInitialValue: function () {
            this.hasError = $('.JS--shippingWrapper').find('.JS--alertMessage').length > 0 ? true : false;
        },
        showErrorAt: function (position) {
            if (!this.hasError) {
                position.after(`<div class="JS--alertMessage alert alert-danger mt-4 px-3 px-md-4 rounded-0" role="alert">
                        <i class="fas fa-exclamation-circle mr-1 text-muted"></i> Nem mentetted el címzett adatait! 
                    </div>`);
                this.hasError = true;
            }
            position[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
        },
        hideError: function () {
            if (this.hasError) {
                $('.JS--shippingWrapper').find('.JS--alertMessage').replaceWith('');
                this.hasError = false;
            }
        },
    },
    payment: {
        error: false,
        fetchInitialValue: function () {
            this.hasError = $('.JS--paymentWrapper').find('.JS--alertMessage').length > 0 ? true : false;
        },
        showErrorAt: function (position) {
            if (!this.hasError) {
                position.after(`<div class="JS--alertMessage alert alert-danger mt-4 px-3 px-md-4 rounded-0" role="alert">
                        <i class="fas fa-exclamation-circle mr-1 text-muted"></i> Nem mentetted el címzett adatait! 
                    </div>`);
                this.hasError = true;
            }
            position[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
        },
        hideError: function () {
            if (this.hasError) {
                $('.JS--paymentWrapper').find('.JS--alertMessage').replaceWith('');
                this.hasError = false;
            }
        },
    },
    hasNoErrors: function () {
        if (this.cart.hasError) {
            $('.JS--cartWrapper').find('.JS--alertBlock')[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
            return false;
        }
        if (this.recipient.hasError) {
            $('.JS--recipientWrapper').find('.JS--alertBlock')[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
            return false;
        }
        if (this.deliveryDate.hasError) {
            $('.JS--deliveryDateContainer').find('.JS--alertBlock')[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
            return false;
        }
        if (this.sender.hasError) {
            $('.JS--senderWrapper').find('.JS--alertBlock')[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
            return false;
        }
        if (this.shipping.hasError) {
            $('.JS--shippingWrapper').find('.JS--alertBlock')[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
            return false;
        }
        if (this.payment.hasError) {
            $('.JS--paymentWrapper').find('.JS--alertBlock')[0].scrollIntoView({ block: 'start', behavior: 'smooth'});
            return false;
        }
        if (this.cart.hasSuccess) {
            this.cart.hideSuccess();
        }
        return true;
    },
};

alert.cart.fetchInitialValue();
alert.recipient.fetchInitialValue();
alert.deliveryDate.fetchInitialValue();
alert.sender.fetchInitialValue();
alert.shipping.fetchInitialValue();
alert.payment.fetchInitialValue();

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


(function(window, $) {
    var proceed = false;

    window.handleOrder = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on('click', '.JS--Button-gotoStep2', this.submitMessageAndCustomerForm.bind(this));
        this.$wrapper.on('click', '.JS--Button-gotoStep3', this.submitDeliveryDateForm.bind(this));
        this.$wrapper.on('click', '.JS--Button-gotoThankYou', this.submitShippingAndPaymentForm.bind(this));
    };

    $.extend(window.handleOrder.prototype, {

        submitMessageAndCustomerForm: function(event) {
            if (alert.hasNoErrors()) {
                if (proceed) {
                    proceed = false;
                    return;
                }
                event.preventDefault();
                let $element = $(event.currentTarget);
                $element.addClass('text-success-faded');  // $element.html('');
                $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
                proceed = true;
                $element.trigger('click');
                $element.prop('disabled', true);

                // let url = $element.attr('href');
                let url = $element.data('url');
                let $wrapper = $element.closest('.JS--globalWrapper');
                let $form = $wrapper.find('.JS--messageAndCustomerForm');
                // console.log('URL: ' + $form.attr('action'));

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    success: function (data) {
                        console.log($form.attr('action'));
                        window.location.href = url;
                    },
                    error: function (jqXHR) {
                        $form.replaceWith(jqXHR.responseText);

                        console.log($form.first('.JS--messageForm-message'));
                        alert.scroll.scrollIntoView($form.first('.JS--messageForm-message').find('.invalid-feedback'));
                        overlay.button.hideOverlay($element);
                    }
                });
            }
            // overlay.button.hideOverlay($element);

            // alert.cart.hideSuccess(); // hide any success alert visible in the cart
            // alert.customer.hideError();
        },
        submitDeliveryDateForm: function(event) {
            if (alert.hasNoErrors()) {
                if (proceed) {
                    proceed = false;
                    return;
                }
                event.preventDefault();
                // overlay.button.showOverlay(event);

                let $element = $(event.currentTarget);
                $element.addClass('text-success-faded');  // $element.html('');
                $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
                proceed = true;
                $element.trigger('click');
                $element.prop('disabled', true);


                let url = $element.data('url');
                let $wrapper = $element.closest('.JS--globalWrapper');
                let $form = $wrapper.find('.JS--hiddenDeliveryDateForm');
                // console.log('URL: ' + $form.attr('action'));

                if ($wrapper.find('.JS--recipientContentBlock').find('.JS--item').length < 1) {
                    formIsDisplayed = true;
                }

                console.log('formIsDisplayed: ' + formIsDisplayed);
                if (formIsDisplayed) {
                    if (!alert.recipient.hasError) {
                        alert.recipient.showErrorAt($wrapper.find('.JS--recipientWrapper').find('.JS--alertBlock'));
                        alert.recipient.hasError = true;
                    }
                    // setTimeout(function () {
                    overlay.button.hideOverlay($element);
                    // }, 1000);
                    $wrapper.find('.JS--recipientWrapper')[0].scrollIntoView({block: 'start'});
                } else {
                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: $form.serialize(),
                        success: function (data, jqXHR) {
                            console.log($form.attr('action'));
                            console.log(url);
                            window.location.href = url;

                            // To display the Symfony Profiler page:
                            // $form.after(data);
                        },
                        error: function (jqXHR) {
                            $form.replaceWith(jqXHR.responseText);
                            overlay.button.hideOverlay($element);
                        }
                    });
                }
            }

        },

        /**
         * Az alábbit akkor használtam amikor az első lépésben csak a Message form volt kint.
         */
        handleSubmitMessageForm: function(event) {
            event.preventDefault();
            let $element = $(event.currentTarget);
            let url = $element.attr('href');
            let $wrapper = $element.closest('.JS--globalWrapper');
            let $formWrapper = $wrapper.find('.JS--messageWrapper').find('.JS--formWrapper')
            let $messageForm = $formWrapper.find('form');
            alert('URL: ' + $messageForm.attr('action'));

            console.log($messageForm);

            let $dateForm = $wrapper.find('.JS--dateWrapper').closest('form');
            // console.log($dateForm + ' asfds');
            // alert($messageForm.attr('action'));

            $.ajax({
                url: $messageForm.attr('action'),
                method: 'POST',
                data: $messageForm.serialize(),
                success: function (data) {
                    console.log($messageForm.attr('action'));
                    window.location.href = url;
                    // $formWrapper.html(data);
                },
                error: function (jqXHR) {
                    $messageForm.replaceWith(jqXHR.responseText);
                }

            });
        },
        submitShippingAndPaymentForm: function(event) {
            if (alert.hasNoErrors()) {
                if (proceed) {
                    proceed = false;
                    return;
                }
                event.preventDefault();
                let $element = $(event.currentTarget);
                $element.addClass('text-success-faded');  // $element.html('');
                $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
                proceed = true;
                $element.trigger('click');
                $element.prop('disabled', true);

                let url = $element.data('url');
                let $wrapper = $element.closest('.JS--globalWrapper');
                let $shipAndPayForm = $element.find('.JS--shipAndPayForm');
                let $registrationForm = $wrapper.find('.JS--registrationForm');
                console.log($shipAndPayForm);

                if ($wrapper.find('.JS--senderContentBlock').find('.JS--item').length < 1) {
                    formIsDisplayed = true;
                }

                if (formIsDisplayed) {
                    if (!alert.sender.hasError) {
                        alert.sender.showErrorAt($wrapper.find('.JS--senderWrapper').find('.JS--alertBlock'));
                        alert.sender.hasError = true;
                    }
                    // setTimeout(function () {
                    overlay.button.hideOverlay($element);
                    // }, 1000);
                    $wrapper.find('.JS--senderWrapper')[0].scrollIntoView({block: 'start'});
                } else {
                    // elposztolja a regisztracios formot
                    $.ajax({
                        url: $registrationForm.attr('action'),
                        method: 'POST',
                        data: $registrationForm.serialize(),
                        success: function (data) {
                            // nem kell csinaljak semmit, mert sikeres regisztracioval be is lepteti a usert.
                            // $registrationForm.replaceWith(data);
                            console.log(data);

                            // elposztolja a shipAndPay formot
                            $.ajax({
                                url: $shipAndPayForm.attr('action'),
                                method: 'POST',
                                data: $shipAndPayForm.serialize(),
                                success: function (data) {
                                    console.log($shipAndPayForm.attr('action'));
                                    window.location.href = url;
                                },
                                error: function (jqXHR) {
                                    $shipAndPayForm.replaceWith(jqXHR.responseText);
                                    overlay.button.hideOverlay($element);
                                }
                            });
                        },
                        error: function (jqXHR) {
                            $registrationForm.replaceWith(jqXHR.responseText);
                            overlay.button.hideOverlay($element);
                        }
                    });
                }
            }
        }
    });
})(window, jQuery);

(function(window, $) {
    var proceed = false;

    window.InlineEdit = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on('click','.JS--editButton', this.handleEdit.bind(this));
        this.$wrapper.on('submit','.JS--editForm', this.handleFormSubmit.bind(this));

        // Cart, Message & CustomerBasic: Step1 Page >> Cart items
        this.$wrapper.on('click','.JS--Button-removeItem', this.handleRemoveItemFromCart.bind(this));
        this.$wrapper.on('click','.JS--Button-selectGift', this.handleAddGiftToCart.bind(this));
        this.$wrapper.on('change','.JS--Dropdown-setItemQuantity', this.handleSetItemQuantity.bind(this));
        this.$wrapper.on('click','.JS--cardMessage', this.handlePickCardMessage.bind(this));

        // Recipient, Delivery Date: Step2 Page >> Recipient
        this.$wrapper.on('focusout','.JS--recipientForm-zip', this.handleSubmitZipAndGetCity.bind(this));

        this.$wrapper.on('click','.JS--Button-pickRecipient', this.pickRecipient.bind(this));
        this.$wrapper.on('click','.JS--Button-getRecipients', this.getRecipients.bind(this));
        this.$wrapper.on('click','.JS--Button-editRecipient', this.showRecipientForm.bind(this));
        this.$wrapper.on('click','.JS--Button-deleteRecipient', this.deleteRecipient.bind(this));
        this.$wrapper.on('submit','.JS--editForm-recipient', this.submitRecipientForm.bind(this));

        // Sender, Payment & Shipping: Step3 Page >> Sender
        this.$wrapper.on('focusout','.JS--senderForm-zip', this.handleSubmitZipAndGetCity.bind(this));

        this.$wrapper.on('click','.JS--Button-pickSender', this.pickSender.bind(this));
        this.$wrapper.on('click','.JS--Button-getSenders', this.getSenders.bind(this));
        this.$wrapper.on('click','.JS--Button-editSender', this.showSenderForm.bind(this));
        this.$wrapper.on('click','.JS--Button-deleteSender', this.deleteSender.bind(this));
        this.$wrapper.on('submit','.JS--editForm-sender', this.submitSenderForm.bind(this));

        // Payment & Shipping: Utils >> Shipping and Payment methods
        this.$wrapper.on('click','.JS--Button-pickShipping', this.pickShipping.bind(this));
        this.$wrapper.on('click','.JS--Button-pickPayment', this.pickPayment.bind(this));
    };

    $.extend(window.InlineEdit.prototype, {

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

        handleEdit: function(event) {
            event.preventDefault();
            let $element = $(event.currentTarget);
            let url = $element.data('url');
            let $enclosing = $element.closest('span');
            let $next = $enclosing.next();

            $.ajax({
                url: url,
                method: 'POST',
                success: function() {
                    // $enclosing.addClass('d-none');
                    $enclosing.hide();
                    $next.show();
                    $next.removeClass('d-none');
                    $.get(url, function(data){
                        $next.html(data);
                    });
                }
            });
        },

        handleFormSubmit: function(event) {
            event.preventDefault();
            let $element = $(event.currentTarget);
            let $enclosing = $element.closest('span');
            let $prev = $enclosing.prev();
            let $form = $element;

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    // $form.closest('.js--wrapperForm').html(data);
                    $enclosing.hide();
                    $enclosing.empty();
                    $prev.find('.JS--result').html(data);
                    $prev.show();
                },
                error: function(jqXHR) {
                    console.log($form.closest('.JS--formWrapper'));
                    console.log(jqXHR.responseText);
                    $form.closest('.JS--formWrapper').html(jqXHR.responseText);
                }
            });
        },
        handleSetItemQuantity: function(event) {
            event.preventDefault();
            let $element = $(event.currentTarget);
            let $wrapper = $element.closest('.JS--cartWrapper');
            let $formWrapper = $element.closest('.JS--formWrapper');
            let $form = $formWrapper.find('form');
            console.log($form.attr('action'));

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    $formWrapper.html(data);
                    alert.cart.hideSuccess();
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                }
            });
        },
        handleRemoveItemFromCart: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            // $element.trigger('click');
            $element.addClass('disabled');

            let url = $element.data('url');
            let $wrapper = $element.closest('.JS--cartWrapper');
            let $resultWrapper = $wrapper.find('.JS--resultWrapper');
            console.dir($resultWrapper);
            console.log(url);

            $.ajax({
                url: url,
                method: 'DELETE',
                success: function(data) {
                    $resultWrapper.html(data);
                    proceed = false;
                    alert.cart.hideSuccess();
                }
            });
        },
        handleAddGiftToCart: function (event) {
            console.log(proceed);
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            console.log($element[0]);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.addClass('disabled');

            let url = $element.data('url');
            let $wrapper = $element.closest('.JS--globalWrapper').find('.JS--cartWrapper');
            let $resultWrapper = $wrapper.find('.JS--resultWrapper');
            console.log($resultWrapper[0]);

            // alert.cart.hideSuccess();

            $.ajax({
                url: url,
                method: 'POST',
                success: function(data) {
                    $resultWrapper.html(data);
                    overlay.link.hideOverlay($element);
                    proceed = false;
                    alert.cart.showSuccessAt($wrapper.find('.JS--alertBlock'));
                }
            });

        },
        handlePickCardMessage: function (event) {
            event.preventDefault();
            let $element = $(event.currentTarget);
            console.log($element);
            let $message = $element.closest('.JS--messageWrapper').find('.JS--messageForm-message');
            console.log($message);

            $message.val($element.text());
            $element.closest('.JS--globalWrapper').closest('.JS--messageWrapper').find('.JS--cardMessageDropdown').removeClass('show');
            console.log($element.closest('.JS--globalWrapper').closest('.JS--messageWrapper').find('.JS--cardMessageDropdown'));

        },


        showRecipientForm: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.addClass('disabled');

            let url = $element.data('url');
            let $wrapper = $element.closest('.JS--recipientWrapper');
            let $contentBlock = $wrapper.find('.JS--recipientContentBlock');

            $.post(url, function (data) {
                $contentBlock.html(data);

                formIsDisplayed = true;
                proceed = false;
            });
        },
        pickRecipient: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.addClass('disabled');

            let $wrapper = $element.closest('.JS--recipientWrapper');
            let url = $element.attr('href');

            $.post(url, function (data) {
                $element.closest('.row').find('.selected').removeClass('selected');
                $element.closest('.JS--item').addClass('selected');
                overlay.link.hideOverlay($element);
                alert.recipient.hideError();
                proceed = false;
            }).fail(function (jqXHR) {
                $element.removeClass('text-success-faded');
                $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                $element.prop('disabled', false);
                alert.recipient.showErrorAt($wrapper.find('.JS--alertBlock'));
                alert.recipient.hasError = true;
                proceed = false;
            });

            // $.ajax({
            //     url: url,
            //     method: 'POST',
            //     success: function() {
            //         $.get(url, function (data) {
            //             $element.closest('.row').find('.selected').removeClass('selected');
            //             $element.closest('.JS--item').addClass('selected');
            //             overlay.link.hideOverlay($element);
            //             alert.recipient.hideError();
            //             proceed = false;
            //         });
            //     },
            //     error: function(jqXHR) {
            //         $element.removeClass('text-success-faded');
            //         $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
            //         $element.prop('disabled', false);
            //         alert.recipient.showErrorAt($wrapper.find('.JS--alertBlock'));
            //         alert.recipient.hasError = true;
            //         proceed = false;
            //     }
            // });
        },
        getRecipients: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.addClass('disabled');

            let url = $element.data('url');
            let $wrapper = $element.closest('.JS--recipientWrapper');
            let $contentBlock = $wrapper.find('.JS--recipientContentBlock');
            console.log(url);

            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $contentBlock.html(data);

                    formIsDisplayed = false;
                    alert.recipient.hideError();
                    proceed = false;
                },
                error: function(jqXHR) {
                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                    alert.recipient.showErrorAt($wrapper.find('.JS--alertBlock'));
                    alert.recipient.hasError = true;
                    proceed = false;
                }
            });
        },
        submitRecipientForm: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $form = $(event.currentTarget);
            let $element = $form.find('.JS--Button-submit');
            console.log($element);
            $element.addClass('text-success-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.prop('disabled', true);

            let $wrapper = $element.closest('.JS--recipientWrapper');
            let $contentBlock = $form.closest('.JS--recipientContentBlock');
            let $url = $form.attr('action');
            console.log('form action url:' + $url);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    $contentBlock.html(data);

                    formIsDisplayed = false;
                    alert.recipient.hideError();
                    proceed = false;
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
        deleteRecipient: function (event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            // setTimeout(function () {
            proceed = true;
            $element.trigger('click');
            $element.addClass('disabled');
            // }, 600);

            let url = $element.data('url');
            let $wrapper = $element.closest('.JS--recipientWrapper');
            let $contentBlock = $wrapper.find('.JS--recipientContentBlock');

            let confirm = window.confirm('Biztosan szeretnéd törölni?');
            if (confirm) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    success: function(response) {
                        $contentBlock.html(response);
                        proceed = false;
                    },
                    error: function(jqXHR) {
                        $element.removeClass('text-faded');
                        $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                        $element.prop('disabled', false);
                        alert.recipient.showErrorAt($wrapper.find('.JS--alertBlock'));
                        alert.recipient.hasError = true;
                        proceed = false;
                    }
                });
            } else {
                $element.removeClass('text-faded');
                $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                $element.prop('disabled', false);
                proceed = false;
            }
        },


        pickShipping: function(event) {
            event.preventDefault();
            let $element = $(event.currentTarget);
            let $wrapper = $element.closest('.JS--shippingWrapper');
            let $choiceWrapper = $element.closest('.JS--choiceContainer');
            let url = $element.data('url');
            // $choiceWrapper.find('input').removeAttr("checked");  // NEM MUKODOTT JOL!
            $choiceWrapper.find('input').prop('checked', false);   // Leveszi a pipát
            $choiceWrapper.find('.JS--loadingOverlay').addClass('loading-overlay loading');

            $.ajax({
                url: url,
                method: 'POST',
                success: function () {
                    console.log(url);
                    // $choiceWrapper.find('input')[0].setAttribute("checked","checked");  // NEM MUKODOTT JOL!
                    $choiceWrapper.find('input').prop('checked',true);  // Beleteszi a pipát
                    $choiceWrapper.find('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    alert.shipping.hideError();
                },
                error: function(jqXHR) {
                    $choiceWrapper.find('input').prop('checked',true);  // Beleteszi a pipát
                    $choiceWrapper.find('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    alert.shipping.showErrorAt($wrapper.find('.JS--alertBlock'));
                    alert.shipping.hasError = true;
                }
            });
        },
        pickPayment: function(event) {
            event.preventDefault();
            let $element = $(event.currentTarget);
            let $wrapper = $element.closest('.JS--paymentWrapper');
            let $choiceWrapper = $element.closest('.JS--choiceContainer');
            let url = $element.data('url');
            $choiceWrapper.find('input').prop('checked', false);   // Leveszi a pipát
            $choiceWrapper.find('.JS--loadingOverlay').addClass('loading-overlay loading');

            $.ajax({
                url: url,
                method: 'POST',
                success: function () {
                    console.log(url);
                    $choiceWrapper.find('input').prop('checked',true);  // Beleteszi a pipát
                    $choiceWrapper.find('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    alert.payment.hideError();
                },
                error: function(jqXHR) {
                    $choiceWrapper.find('input').prop('checked',true);  // Beleteszi a pipát
                    $choiceWrapper.find('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    alert.payment.showErrorAt($wrapper.find('.JS--alertBlock'));
                    alert.payment.hasError = true;
                }
            });
        },
        showSenderForm: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.addClass('disabled');

            let url = $element.data('url');
            let $wrapper = $element.closest('.JS--senderWrapper');
            let $contentBlock = $wrapper.find('.JS--senderContentBlock');

            $.ajax({
                url: url,
                method: 'POST',
                success: function(data) {
                    $contentBlock.html(data);

                    formIsDisplayed = true;
                    proceed = false;
                }
            });
        },
        pickSender: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            setTimeout(function () {
                proceed = true;
                $element.trigger('click');
                $element.addClass('disabled');
            }, 600);


            let $wrapper = $element.closest('.JS--senderWrapper');
            let url = $element.attr('href');

            $.ajax({
                url: url,
                method: 'POST',
                success: function(data) {
                    $element.closest('.row').find('.selected').removeClass('selected');
                    $element.closest('.JS--item').addClass('selected');

                    overlay.link.hideOverlay($element);
                    alert.sender.hideError();
                    proceed = false;
                },
                error: function(jqXHR) {
                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                    alert.recipient.showErrorAt($wrapper.find('.JS--alertBlock'));
                    alert.recipient.hasError = true;
                    proceed = false;
                }
            });
        },
        getSenders: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.addClass('disabled');

            let url = $element.data('url');
            let $wrapper = $element.closest('.JS--senderWrapper');
            let $contentBlock = $wrapper.find('.JS--senderContentBlock');
            console.log(url);

            $.ajax({
                url: url,
                method: 'POST',
                success: function(data) {
                    $contentBlock.html(data);

                    formIsDisplayed = false;
                    alert.sender.hideError();
                    proceed = false;
                },
                error: function(jqXHR) {
                    $element.removeClass('text-success-faded');
                    $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                    $element.prop('disabled', false);
                    alert.sender.showErrorAt($wrapper.find('.JS--alertBlock'));
                    alert.sender.hasError = true;
                    proceed = false;
                }
            });
        },
        submitSenderForm: function(event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $form = $(event.currentTarget);
            let $element = $form.find('.JS--Button-submit');
            $element.addClass('text-success-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            proceed = true;
            $element.trigger('click');
            $element.prop('disabled', true);

            let $wrapper = $element.closest('.JS--senderWrapper');
            let $contentBlock = $element.closest('.JS--senderContentBlock');
            let $url = $form.attr('action');
            console.log('form action url:' + $url);

            alert.sender.hideError($wrapper);  // elrejti az esetleges uzenetet, ha volt

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    $contentBlock.html(data);

                    formIsDisplayed = false;
                    alert.sender.hideError($wrapper);
                    proceed = false;
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                    overlay.button.hideOverlay($element);
                    proceed = false;
                }
            });
        },
        deleteSender: function (event) {
            if (proceed) {
                proceed = false;
                return;
            }
            event.preventDefault();
            let $element = $(event.currentTarget);
            $element.addClass('text-faded');  // $element.html('');
            $element.next('.JS--loadingOverlay').addClass('loading-overlay loading');
            // setTimeout(function () {
                proceed = true;
                $element.trigger('click');
                $element.addClass('disabled');
            // }, 600);

            let url = $element.data('url');
            let $wrapper = $element.closest('.JS--senderWrapper');
            let $contentBlock = $wrapper.find('.JS--senderContentBlock');

            let confirm = window.confirm('Biztosan szeretnéd törölni?');
            if (confirm) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    success: function (response) {
                        $contentBlock.html(response);
                        proceed = false;
                    },
                    error: function(jqXHR) {
                        $element.removeClass('text-faded');
                        $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                        $element.prop('disabled', false);
                        alert.sender.showErrorAt($wrapper.find('.JS--alertBlock'));
                        alert.sender.hasError = true;
                        proceed = false;
                    }
                });
            } else {
                $element.removeClass('text-faded');
                $element.next('.JS--loadingOverlay').removeClass('loading-overlay loading');
                $element.prop('disabled', false);
                proceed = false;
            }
        },

    });
})(window, jQuery);


