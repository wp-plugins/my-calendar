jQuery(document).ready(function ($) {
    $('#mc-sortable').sortable({
        update: function (event, ui) {
            $('#mc-sortable-update').html( 'Submit form to save changes' );
        }
    });
    $('#mc-sortable .up').on('click', function (e) {
        e.preventDefault();
        $(this).parents('li').insertBefore($(this).parents('li').prev());
		$( '#mc-sortable li' ).removeClass( 'mc-updated' );
        $(this).parents('li').addClass( 'mc-updated' );
    });
    $('#mc-sortable .down').on('click', function (e) {
        e.preventDefault();
        $(this).parents('li').insertAfter($(this).parents('li').next());
		$( '#mc-sortable li' ).removeClass( 'mc-updated' );		
        $(this).parents('li').addClass( 'mc-updated' );
    });
});