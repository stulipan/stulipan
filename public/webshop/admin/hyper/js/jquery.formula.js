/**
* jquery.formula.js
* @version: v1.0
* @author: Rafael Campana*
*
* Title: jQuery Plugin To Create Formula-based Calculation Inputs
*
* Created by Rafael Campana on 2016-04-19. Please report any bug at contato@rafaelcampana.com
*
* Copyright (c) 2016 Rafael Campana http://rafaelcampana.com
*
* The MIT License (http://www.opensource.org/licenses/mit-license.php)
*
* Permission is hereby granted, free of charge, to any person
* obtaining a copy of this software and associated documentation
* files (the "Software"), to deal in the Software without
* restriction, including without limitation the rights to use,
* copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the
* Software is furnished to do so, subject to the following
* conditions:
*
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
* OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
* HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
* WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
* OTHER DEALINGS IN THE SOFTWARE.
*/

$(function () {

    // looking for inputs with data-formula attribute
    $("input:text[data-formula]").each(function () {
        var formula = $(this).attr('data-formula');
        var inputFieldsInvolved = formula.match(/#([_a-zA-Z0-9]){1,15}#/g); // search for all fields that match #sample#
        var currentField = $(this);
        var initialFormula = formula;

        $.each(inputFieldsInvolved, function (key, name) {
            var inputField = $("[data-input-field ='" + name.replace('#', '').replace('#', '') + "'")[0];//Name of the field involved
            if (!inputField)
                return;

            $(inputField).bind('input', function () {//binds one or more related functions
                var finalFormula = initialFormula;
                $.each(inputFieldsInvolved, function (keycampo, fieldName) {
                    finalFormula = finalFormula.replace(fieldName, $("[data-input-field ='" + fieldName.replace('#', '').replace('#', '') + "'").val().replace(",", "."));
                });
                try {
                    if (inputFieldsInvolved.length > 1)
                        currentField.val(eval(finalFormula).toFixed(0));//Corrige erro da subtrao de 0.3 - 0.1 = 0,1999999...
                    else
                        currentField.val(finalFormula);

                    var mascara = currentField.attr("data-mask");

                    if (mascara != undefined && mascara != "" && currentField.mask != undefined) {//verifica se utiliza componente mascara
                        currentField.mask(mascara, {
                            translation: {
                                S: { pattern: /^\-/, optional: true },
                                9: { pattern: /[0-9]/ }
                            }
                        });

                    }
                    currentField.trigger('input');//Call the actions associated with it
                } catch (e) {
                    //alert or
                    currentField.val("0");
                }
            });
        });
    });

});
