

$(document).ready(function(){


// $("#map-canvas").height($(".contact").height());

$(".navbar li").mouseenter(function(){
$(this).addClass("open");
});
$(".navbar li").mouseleave(function(){
$(this).removeClass("open");


});



$("#gallery").owlCarousel({
autoPlay: 3000,
items : 4,
itemsDesktop : [1199,4],
itemsDesktopSmall : [979,3],
stopOnHover : true,
goToFirstSpeed : 2000,

});


$("#loan").owlCarousel({
autoPlay: 3000,
items : 4,
itemsDesktop : [1199,4],
itemsDesktopSmall : [979,3],
stopOnHover : true,
goToFirstSpeed : 2000,

});







$('.img-wap-hover').css({
height: $('.img-wrp').height()-$('.img-caption').height()-9,
width: $('.img-wrp').width(),
});
$('#gallery1 .img-wap-hover').css('height', $('#gallery1 .img-wrp').height());

//$('.img-wap-hover span').css("margin-top", $('.img-wrp').height()/3);



$('.call-btn').tooltip({container:"body"});
if($(window).width()<=840)
{
$('.slider-btn a').tooltip();
}
	$('#inquiry').popover({
        html : true,
        content: function() {

          return $('.inquiry-data').html();

        }


    });


});

$(window).resize(function(){

if($(window).width()<=840)
{
$('.slider-btn a').tooltip();
}

$('.img-wap-hover').css({
height: $('.img-wrp').height()-$('.img-caption').height()-9,
width: $('.img-wrp').width(),
});


//$('.img-wap-hover span').css("margin-top", $('.img-wrp').height()/3);


});

$(window).scroll(function(){
if($(window).scrollTop()>80)
{
$('.menubar').css({
position: 'fixed',
});
}
else
{
$('.menubar').css({
position: 'relative',
});

}
});



$(document).ready(function() {
    function randomNumber(min, max) {
        return Math.floor(Math.random() * (max - min + 1) + min);
    };

    function generateCaptcha() {
        $('#captchaOperation').html([randomNumber(1, 100), '+', randomNumber(1, 200), '='].join(' '));
    };

    generateCaptcha();

    $('#contact')
        .bootstrapValidator({
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                captcha: {
                    validators: {
                        callback: {
                            message: 'Wrong answer',
                            callback: function (value, validator, $field) {
                                // Determine the numbers which are generated in captchaOperation
                                var items = $('#captchaOperation').html().split(' '),
                                    sum   = parseInt(items[0]) + parseInt(items[2]);
                                return value == sum;
                            }
                        }
                    }
                }
            }
        })
        .on('error.form.bv', function (e) {
            generateCaptcha();
        });
});





/*

$(document).ready(function() {


	function randomNumber(min, max) {
        return Math.floor(Math.random() * (max - min + 1) + min);
    };

    function generateCaptcha() {
        $('#captchaOperation').html([randomNumber(1, 100), '+', randomNumber(1, 200), '='].join(' '));
    };

    generateCaptcha();

    $('#contact').on('init.field.bv', function(e, data) {
            var field  = data.field,        // Get the field name
                $field = data.element,      // Get the field element
                bv     = data.bv;           // BootstrapValidator instance

            // Create a span element to show valid message
            // and place it right before the field
            var $span = $('<small/>')
                            .addClass('help-block validMessage')
                            .attr('data-field', field)
                            .insertAfter($field)
                            .hide();

            // Retrieve the valid message via getOptions()
            var message = bv.getOptions(field).validMessage;
            if (message) {
                $span.html(message);
            }
        }).bootstrapValidator({
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
             live: 'enabled',
            fields: {
                fullName: {
                    validators: {
                        notEmpty: {
                            message: 'The full name is required and cannot be empty'
                        }
                        ,
                        stringLength: {
                        max: 50,
                        message: 'The full name must be less than 50 characters'
                    }

                    }
                },

                captcha: {
                    validators: {
                        callback: {
                            message: 'Wrong answer',
                            callback: function (value, validator, $field) {
                                // Determine the numbers which are generated in captchaOperation
                                var items = $('#captchaOperation').html().split(' '),
                                    sum   = parseInt(items[0]) + parseInt(items[2]);
                                return value == sum;
                            }
                        }}}
					,


                email: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter valid email address'
                        }


                    }
                }

			}


        })

        .on('success.form.bv', function(e) {
            // Reset the message element when the form is valid
            $('#errors').html('');
        })

        .on('error.field.bv', function(e, data) {
            // data.bv      --> The BootstrapValidator instance
            // data.field   --> The field name
            // data.element --> The field element

            // Get the messages of field
            var messages = data.bv.getMessages(data.element);

			  generateCaptcha();

            // Remove the field messages if they're already available
            $('#errors').find('li[data-field="' + data.field + '"]').remove();

            // Loop over the messages
            for (var i in messages) {
                // Create new 'li' element to show the message
                $('<li/>')
                    .attr('data-field', data.field)
                    .wrapInner(
                        $('<a/>')
                            .attr('href', 'javascript: void(0);')
                            .html(messages[i])
                            .on('click', function(e) {
                                // Focus on the invalid field
                                data.element.focus();
                            })
                    )
                    .appendTo('#errors');
            }

            // Hide the default message
            // $field.data('bv.messages') returns the default element containing the messages
            data.element
                .data('bv.messages')
                .find('.help-block[data-bv-for="' + data.field + '"]')
                .hide();
        })


});
*/
