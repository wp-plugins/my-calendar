jQuery(document).ready(function ($) {
    var tabs = $('.mc-tabs .wptab').length;
    $('.mc-tabs .tabs a[href="#' + firstItem + '"]').addClass('active').attr( 'aria-selected', 'true' );
    if ( tabs > 1 ) {
        $( '.mc-tabs .wptab' ).not( '#' + firstItem ).hide();
        $( '.mc-tabs .tabs a' ).on( 'click', function (e) {
            e.preventDefault();
            $('.mc-tabs .tabs a').removeClass('active').attr( 'aria-selected', 'false' );
            $(this).addClass('active').attr( 'aria-selected', 'true' );
            var target = $(this).attr('href');
            $('.mc-tabs .wptab').not(target).hide();
            $(target).show().attr('tabindex','-1').focus();
        });
    }	
});