jQuery(document).ready(function ($) {
	$( '.mc-tabs' ).each( function ( index ) {
		var tabs = $('.mc-tabs .wptab').length;
		var firstItem = window.location.hash;
		if ( ! firstItem ) {
			var firstItem = '#' + $( '.mc-tabs .wptab:nth-of-type(1)' ).attr( 'id' );
		}		
		$('.mc-tabs .tabs a[href="' + firstItem + '"]').addClass('active').attr( 'aria-selected', 'true' );
		if ( tabs > 1 ) {
			$( '.mc-tabs .wptab' ).not( firstItem ).hide();
			$( firstItem ).show();
			$( '.mc-tabs .tabs a' ).on( 'click', function (e) {
				e.preventDefault();
				$('.mc-tabs .tabs a').removeClass('active').attr( 'aria-selected', 'false' );
				$(this).addClass('active').attr( 'aria-selected', 'true' );
				var target = $(this).attr('href');
				window.location.hash = target;
				$('.mc-tabs .wptab').not(target).hide();
				$(target).show().attr('tabindex','-1').focus();
			});
		}
	});
});