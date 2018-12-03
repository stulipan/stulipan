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
    window.InlineEdit = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on('click','.JS--editButton', this.handleEdit.bind(this));
        this.$wrapper.on('submit','.JS--editForm', this.handleFormSubmit.bind(this));


        this.$wrapper.on('click','.JS--Button-removeItem', this.handleRemoveItemFromCart.bind(this));
        this.$wrapper.on('change','.JS--Dropdown-setItemQuantity', this.handleSetItemQuantity.bind(this));

        this.$wrapper.on('click','.JS--Button-pickRecipient', this.handlePickRecipient.bind(this));
        this.$wrapper.on('click','.JS--Button-getRecipients', this.handleGetRecipients.bind(this));

        this.$wrapper.on('click','.JS--editButton-recipient', this.handleEditButtonRecipient.bind(this));
        this.$wrapper.on('submit','.JS--editForm-recipient', this.handleRecipientFormSubmit.bind(this));

        this.$wrapper.on('click','.JS--Button-nextStep', this.handleNextStep.bind(this));

        this.$wrapper.on('click','.JS--Button-pickChoice', this.handlePickChoice.bind(this));



        this.$wrapper.find('tbody tr').on('click', this.handleRowClick.bind(this));
    };

    $.extend(window.InlineEdit.prototype, {

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
                success: function() {
                    // $resultWrapper.empty();
                    $.get(url, function(data) {
                        console.dir(data);
                        // $resultWrapper.empty();
                        $resultWrapper.replaceWith(data);
                        console.dir($resultWrapper);
                    });
                }
                // ,
                // error: function (jqXHR) {
                //     console.log('errir');
                //     $resultWrapper.replaceWith(jqXHR.responseText);
                // }
            });
        },

        handleEditButtonRecipient: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.data('url');
            var $wrapper = $element.closest('.JS--wrapper');
            var $resultWrapper = $wrapper.find('.JS--resultWrapper');
            var $formWrapper = $wrapper.find('.JS--formWrapper');
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
                    // $resultWrapper.hide();
                    // $resultWrapper.empty();
                    //
                    // $.get(url, function(data){
                    //     $formWrapper.show();
                    //     $formWrapper.html(data);
                    // });
                }
            });
        },
        handlePickRecipient: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.attr('href');


            $.ajax({
                url: url,
                method: 'POST',
                success: function() {
                    $.get(url, function (data) {
                        console.log(url);
                        $element.closest('.row').find('.selected').removeClass('selected');
                        console.log($element);
                        $element.closest('.card').addClass('selected');

                    });
                    // $resultWrapper.hide();
                    // $resultWrapper.empty();
                    //
                    // $.get(url, function(data){
                    //     $formWrapper.show();
                    //     $formWrapper.html(data);
                    // });
                }
            });
        },
        handleGetRecipients: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.data('url');
            var $wrapper = $element.closest('.JS--wrapper');
            var $resultWrapper = $wrapper.find('.JS--resultWrapper');
            var $formWrapper = $wrapper.find('.JS--formWrapper');
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
            var $formWrapper = $element.closest('.JS--formWrapper');  //megtatálja a 'JS--formWrapper' DIV-et
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
                    // $formWrapper.hide();
                    // $formWrapper.empty();
                    // $resultWrapper.html(data);
                    // $resultWrapper.show();
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
        handleNextStep: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var url = $element.attr('href');
            var $wrapper = $element.closest('.JS--globalWrapper');
            var $formWrapper = $wrapper.find('.JS--messageWrapper').find('.JS--formWrapper')
            var $messageForm = $formWrapper.find('form');
            console.log($messageForm.attr('action'));
            // alert($messageForm.attr('action'));

            $.ajax({
                url: $messageForm.attr('action'),
                method: 'POST',
                data: $messageForm.serialize(),
                success: function(data) {
                    window.location.href = url;
                    // $formWrapper.html(data);
                },
                error: function(jqXHR) {
                    $messageForm.replaceWith(jqXHR.responseText);
                }

            });
        },
        handlePickChoice: function(event) {
            event.preventDefault();
            var $element = $(event.currentTarget);
            var $wrapper = $element.closest('.JS--choicesContainer');
            $wrapper.find('.JS--choiceContainer').closest('.selected').removeClass('selected');
            $wrapper.find('.JS--choiceContainer input').removeAttr('checked');

            $element.closest('.JS--choiceContainer').addClass('selected');
            $element.closest('.JS--choiceContainer').find('input')[0].setAttribute("checked","checked");
        },
        handleRowClick: function() {
            // console.log('row clicked');
        }
    });
})(window, jQuery);


