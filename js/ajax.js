(function ($) {
    $(function () {
		// Delete single instances of recurring events.
		$( '.mc_response' ).hide();
        $('button.delete_occurrence').on( 'click', function (e) {
            e.preventDefault();
            var value = $(this).attr( 'data-value' );
            var data = {
                'action': mc_data.action,
                'occur_id': value,
                'security': mc_data.security
            };
            $.post( ajaxurl, data, function (response) {
				if ( response.success == 1 ) {
					$( "button[data-value='"+value+"']" ).parent( 'li' ).hide();
				}
                $('.mc_response').text( response.response ).show( 300 );
            }, "json" );
        });
		// display notice informing users of lack of support for recur month by day
		$( '.mc_recur_notice' ).hide();
		$( '#e_recur' ).on( 'change', function (e) {
			var recur = $(this).val();
			if ( recur == 'U' ) {
				$( '#e_every' ).attr( 'max', 1 ).val( 1 );
				$( '.mc_recur_notice' ).show( 300 );
			} else {
				$( '.mc_recur_notice' ).hide();
			}
		});
		
    });
}(jQuery));