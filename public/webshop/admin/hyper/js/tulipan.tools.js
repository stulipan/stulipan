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

'use strict';

(function(window, $) {
    window.handleOrder = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on('click', '.JS--Button-gotoStep2', this.handleSubmitMessageAndCustomerForm.bind(this));
        this.$wrapper.on('click', '.JS--Button-gotoStep3', this.handleSubmitDeliveryDateForm.bind(this));
        this.$wrapper.on('click', '.JS--Button-submitOrder', this.handleSubmitOrderForm.bind(this));
    };

    $.extend(window.handleOrder.prototype, {

        // handleSubmitHiddenDeliveryDateForm: function(event) {
        //     event.preventDefault();
        //     var $element = $(event.currentTarget);
        //     var url = $element.attr('href');
        //     var $wrapper = $element.closest('.JS--globalWrapper');
        //     var $form = $wrapper.find('.JS--deliveryDateForm');
        //     console.log('URL: ' + $form.attr('action'));
        //
        //     // alert($form.attr('action'));
        //
        //     $.ajax({
        //         url: url,
        //         method: 'POST',
        //         success: function () {
        //             $.get(url, function (data) {
        //                 console.log(url);
        //             });
        //         }
        //     });
        // },

        handleSubmitDeliveryDateForm: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.attr('href');
            var $wrapper = $element.closest('.JS--globalWrapper');
            var $form = $wrapper.find('.JS--hiddenDeliveryDateForm');
            // console.log('URL: ' + $form.attr('action'));

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function (data) {
                    // alert($form.attr('action'));
                    console.log($form.attr('action'));
                    window.location.href = url;
                },
                error: function (jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                }

            });
        },
        handleSubmitMessageAndCustomerForm: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.attr('href');
            var $wrapper = $element.closest('.JS--globalWrapper');
            var $form = $wrapper.find('.JS--messageAndCustomerForm');
            console.log('URL: ' + $form.attr('action'));

            // alert($form.attr('action'));

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
                }

            });
        },
        /**
         * Az alábbit akkor használtam amikor az első lépésben csak a Message form volt kint.
         */
        handleSubmitMessageForm: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.attr('href');
            var $wrapper = $element.closest('.JS--globalWrapper');
            var $formWrapper = $wrapper.find('.JS--messageWrapper').find('.JS--formWrapper')
            var $messageForm = $formWrapper.find('form');
            alert('URL: ' + $messageForm.attr('action'));

            console.log($messageForm);

            var $dateForm = $wrapper.find('.JS--dateWrapper').closest('form');
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
        handleSubmitOrderForm: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.attr('href');
            var $wrapper = $element.closest('.JS--globalWrapper');
            var $shipAndPayForm = $element.find('form');
            console.log($shipAndPayForm);

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
                }
            });
        }
    });
})(window, jQuery);

(function(window, $) {
    window.InlineEdit = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on('click','.JS--editButton', this.handleEdit.bind(this));
        this.$wrapper.on('submit','.JS--editForm', this.handleFormSubmit.bind(this));

        // Cart, Message & CustomerBasic: Step1 Page >> Cart items
        this.$wrapper.on('click','.JS--Button-removeItem', this.handleRemoveItemFromCart.bind(this));
        this.$wrapper.on('change','.JS--Dropdown-setItemQuantity', this.handleSetItemQuantity.bind(this));
        this.$wrapper.on('click','.JS--cardMessage', this.handlePickCardMessage.bind(this));

        // Recipient, Delivery Date: Step2 Page >> Recipient
        this.$wrapper.on('focusout','.JS--recipientForm-zip', this.handleSubmitZipAndGetCity.bind(this));

        this.$wrapper.on('click','.JS--Button-pickRecipient', this.handlePickRecipient.bind(this));
        this.$wrapper.on('click','.JS--Button-getRecipients', this.handleGetRecipients.bind(this));
        this.$wrapper.on('click','.JS--Button-editRecipient', this.handleEditButtonRecipient.bind(this));
        this.$wrapper.on('submit','.JS--editForm-recipient', this.handleRecipientFormSubmit.bind(this));

        // Sender, Payment & Shipping: Step3 Page >> Sender
        this.$wrapper.on('focusout','.JS--senderForm-zip', this.handleSubmitZipAndGetCity.bind(this));

        this.$wrapper.on('click','.JS--Button-pickSender', this.handlePickSender.bind(this));
        this.$wrapper.on('click','.JS--Button-getSenders', this.handleGetSenders.bind(this));
        this.$wrapper.on('click','.JS--Button-editSender', this.handleEditButtonSender.bind(this));
        this.$wrapper.on('submit','.JS--editForm-sender', this.handleFormSubmitSender.bind(this));

        // Payment & Shipping: Utils >> Shipping and Payment methods
        this.$wrapper.on('click','.JS--Button-pickChoice', this.handlePickChoice.bind(this));



        this.$wrapper.find('tbody tr').on('click', this.handleRowClick.bind(this));
    };

    $.extend(window.InlineEdit.prototype, {

        /**
         * It is used both by Recipient and Sender forms
         */
        handleSubmitZipAndGetCity: function(event) {
            var $element = $(event.currentTarget);
            var $form = $element.closest('form');

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
            var $element = $(event.currentTarget);
            var url = $element.data('url');
            var $enclosing = $element.closest('span');
            var $next = $enclosing.next();

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
            var $element = $(event.currentTarget);
            var $enclosing = $element.closest('span');
            var $prev = $enclosing.prev();
            var $form = $element;

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
            var $element = $(event.currentTarget);
            var $formWrapper = $element.closest('.JS--formWrapper');
            var $form = $formWrapper.find('form');
            console.log($form.attr('action'));

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    $formWrapper.html(data);
                    // $resultWrapper.show();
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                }
            });
        },
        handleRemoveItemFromCart: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.data('url');
            var $wrapper = $element.closest('.JS--cartWrapper');
            var $resultWrapper = $wrapper.find('.JS--resultWrapper');
            console.dir($resultWrapper);
            console.log(url);

            $.ajax({
                url: url,
                method: 'DELETE',
                success: function(data) {
                    // $resultWrapper.empty();
                    // $.get(url, function(data) {
//                        console.dir(data);
                        $resultWrapper.html(data);
//                        console.dir($resultWrapper);
//                     });
                }
                // ,
                // error: function (jqXHR) {
                //     console.log('errir');
                //     $resultWrapper.replaceWith(jqXHR.responseText);
                // }
            });
        },
        handlePickCardMessage: function (event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var $message = $element.closest('.JS--messageWrapper').find('.JS--messageForm-message');

            $message.val($element.text());
            $element.closest('.JS--messageWrapper').find('.JS--cardMessageDropdown').removeClass('show');

        },


        handleEditButtonRecipient: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.data('url');
            var $wrapper = $element.closest('.JS--recipientWrapper');
            var $resultWrapper = $wrapper.find('.JS--recipientListWrapper');
            var $formWrapper = $wrapper.find('.JS--recipientEditFormWrapper');
            // console.log(url);
            // var $next = $enclosing.next();

            $.ajax({
                url: url,
                method: 'POST',
                success: function() {
                    $.get(url, function (data) {
                        $formWrapper.html(data);
                        $resultWrapper.fadeOut("slow", function () {
                            $formWrapper.show();
                        });
                        $resultWrapper.empty();
                    });
                }
            });
        },
        handlePickRecipient: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var $wrapper = $element.closest('.JS--recipientWrapper');
            var url = $element.attr('href');

            $.ajax({
                url: url,
                method: 'POST',
                success: function() {
                    $.get(url, function (data) {
                        console.log(url);
                        $element.closest('.row').find('.selected').removeClass('selected');
                        $element.closest('.JS--item').addClass('selected');
                        $wrapper.find('.JS--alertMessage').hide();
                    });
                }
            });
        },
        handleGetRecipients: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.data('url');
            var $wrapper = $element.closest('.JS--recipientWrapper');
            var $resultWrapper = $wrapper.find('.JS--recipientListWrapper');
            var $formWrapper = $wrapper.find('.JS--recipientEditFormWrapper');
            console.log(url);
            // var $next = $enclosing.next();

            $.ajax({
                url: url,
                method: 'POST',
                success: function() {
                    $formWrapper.hide();
                    $formWrapper.empty();

                    $.get(url, function(data){
                        $resultWrapper.show();
                        $resultWrapper.html(data);
                    });
                }
            });
        },
        handleRecipientFormSubmit: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var $formWrapper = $element.closest('.JS--recipientEditFormWrapper');  //megtatálja a 'JS--recipientEditFormWrapper' DIV-et
            var $resultWrapper = $formWrapper.prev();                 //megtalálja az elotte levo DIV-et
            var $form = $element;
            var $url = $form.attr('action');
            console.log($resultWrapper);
            console.log('form action url:' + $url);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    $formWrapper.hide();
                    $formWrapper.empty();
                    $resultWrapper.html(data);
                    $resultWrapper.show();
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                }
            });
        },
        handlePickChoice: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var $wrapper = $element.closest('.JS--choicesContainer');
            var url = $element.data('url');
            $wrapper.find('.JS--choiceContainer').closest('.selected').removeClass('selected');
            $wrapper.find('.JS--choiceContainer input').removeAttr('checked');
            $wrapper.find('.JS--alertMessage').hide();

            $element.closest('.JS--choiceContainer').addClass('selected');
            $element.closest('.JS--choiceContainer').find('input')[0].setAttribute("checked","checked");

            $.ajax({
                url: url,
                method: 'POST',
                success: function () {
                    $.get(url, function (data) {
                        console.log(url);
                    });
                }
            });
        },
        handleEditButtonSender: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.data('url');
            var $wrapper = $element.closest('.JS--senderWrapper');
            var $resultWrapper = $wrapper.find('.JS--senderListWrapper');
            var $formWrapper = $wrapper.find('.JS--senderEditFormWrapper');
            // console.log(url);
            // var $next = $enclosing.next();

            $.ajax({
                url: url,
                method: 'POST',
                success: function() {
                    $.get(url, function (data) {
                        $formWrapper.html(data);
                        $resultWrapper.fadeOut("slow", function () {
                            $formWrapper.show();
                        });
                        $resultWrapper.empty();
                    });
                }
            });
        },
        handlePickSender: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var $wrapper = $element.closest('.JS--senderWrapper');
            var url = $element.attr('href');

            $.ajax({
                url: url,
                method: 'POST',
                success: function() {
                    $.get(url, function (data) {
                        console.log(url);
                        $element.closest('.row').find('.selected').removeClass('selected');
                        $element.closest('.JS--item').addClass('selected');
                        $wrapper.find('.JS--alertMessage').hide();

                    });
                }
            });
        },
        handleGetSenders: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.data('url');
            var $wrapper = $element.closest('.JS--senderWrapper');
            var $resultWrapper = $wrapper.find('.JS--senderListWrapper');
            var $formWrapper = $wrapper.find('.JS--senderEditFormWrapper');
            console.log(url);
            // var $next = $enclosing.next();

            $.ajax({
                url: url,
                method: 'POST',
                success: function() {
                    $formWrapper.hide();
                    $formWrapper.empty();

                    $.get(url, function(data){
                        $resultWrapper.show();
                        $resultWrapper.html(data);
                    });
                }
            });
        },
        handleFormSubmitSender: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var $formWrapper = $element.closest('.JS--senderEditFormWrapper');  //megtatálja a 'JS--senderEditFormWrapper' DIV-et
            var $resultWrapper = $formWrapper.prev();                 //megtalálja az elotte levo DIV-et
            var $form = $element;
            var $url = $form.attr('action');
            console.log($resultWrapper);
            console.log('form action url:' + $url);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    $formWrapper.hide();
                    $formWrapper.empty();
                    $resultWrapper.html(data);
                    $resultWrapper.show();
                },
                error: function(jqXHR) {
                    $form.replaceWith(jqXHR.responseText);
                }
            });
        },

        handleRowClick: function() {
            // console.log('row clicked');
        }
    });
})(window, jQuery);


