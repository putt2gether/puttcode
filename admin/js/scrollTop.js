
$(document).ready(function(){

	// hide #back-top first
	$(".back-top").hide();
	$('body').append("<div class='back-top' style='display:table; position:fixed; right:0px; bottom:0px; z-index:100000; opacity:0.8; -webkit-opacity:0.8; -o-opacity:0.8; -moz-opacity:0.8; -ms-opacity:0.8;   display:none;'><a href='#' style='color:white; font-family:Arial, Helvetica, sans-serif; font-size:20pt;display:block; font-weight:bold;text-align:center; line-height:15px; padding:0px 5px; border-radius:3px 3px 0px 0px; background-color:#72a843;'><i class='fa fa-angle-up'></i></a></div>");
 	
	
	// fade in #back-top
	$(function () {
	
		$(window).scroll(function () {
			if ($(this).scrollTop() > 100) {
				$('.back-top').fadeIn();
			} else {
				$('.back-top').fadeOut();
			}
		});

		// scroll body to 0px on click
		$('.back-top a').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 400);
			return false;
		});
	});

});


