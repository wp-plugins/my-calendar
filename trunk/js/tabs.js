jQuery(document).ready(function($){
	var tabs = $('.mc-settings-page .wptab').length;
	$('.mc-settings .tabs a[href="#'+firstItem+'"]').addClass('active');
	if ( tabs > 1 ) {
	$('.mc-settings-page .wptab').not('#'+firstItem).hide();
	$('.mc-settings-page .tabs a').on('click',function(e) {
		e.preventDefault();
		$('.mc-settings-page .tabs a').removeClass('active');
		$(this).addClass('active');
		var target = $(this).attr('href');
		$('.mc-settings-page .wptab').not(target).hide();
		$(target).show();
	});
	}
});