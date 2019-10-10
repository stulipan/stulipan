/**
 * jQuery Black Calculator
 * @name jquery.calculator.js
 * @description Calculator
 * @author Rafael Carvalho Oliveira - http://www.blackhauz.com.br/
 * @version 1.0
 * @copyright (c) 2018 Tulipanfutar.hu
 */
(function($)
{
    // ## public functions

    // primary function
    $.fn.sumCalculator = function(options) {
        // var settings = $.extend({}, $.fn.sumCalculator.defaults, options);



        return this;
    };

    $.fn.blackCalculator.defaults = {
        type: 'simple',
        allowKeyboard: false,
        cssManual: false,
        css: 'css/',
        language: {
            value: 'Value',
            backspace: 'Backspace',
            clear: 'Clear'
        }
    };


})(jQuery);