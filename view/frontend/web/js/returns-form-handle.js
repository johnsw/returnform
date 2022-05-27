require(
    [
        'jquery',
        'chosen',
        'loader',
        'domReady!',
        'mage/validation',
        'mage/translate'
    ],
    function($, chosen) {
        'use strict';

        // 	This is to prevent the post before requireJS has finished loading
        //	in result not validating the form.
        //	ATTENTION: Validation still has to happen on the client-side.
        document.getElementById('returns-form-submit-button').disabled = false;

        var dataForm = $('#returns-form');
        var ignore = null;
        var valid = false;

        dataForm.mage('validation', {
            ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
        }).find('input:text').attr('autocomplete', 'off');

        $('button#returns-form-submit-button').on('click', function() {
            dataForm.validation('clearError');
            valid = dataForm.validation('isValid');
            if(!valid){
                showMessage($.mage.__('Please fill the form correctly.'), 'warning');
                return false;
            }
            sendForm();
        });

        function sendForm(){
            $.ajax({
                method: "POST",
                url: "/returns/form/returns",
                data: dataForm.serialize(),
                showLoader: true,
                success: function(data,status,xhr){
                    dataForm[0].reset();
                    if (data.success) {
                        // showMessage($.mage.__('You have succesfully sent your message.'), 'success');
                    } else {
                        showMessage(data.error, 'error');
                    }
                    
                },
                error: function(xhr, status, error){
                    showMessage($.mage.__('Your submission could not be completed. Please try again later.'), 'error')
                },
                complete: function(){

                },
            })
        }

        function showMessage(message, type) {
            $('.sw-messages').html('');
            $('.sw-messages').append(`<div class='alert alert-${type}' role='alert'>${message}</div>`);
        }


        $('#returns-form select').chosen({
            disable_search: true
        });

    }
);
