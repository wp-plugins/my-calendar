(function( $ ) { 'use strict';
	$(function(){
		$(document).on('click', '.calendar .my-calendar-nav a', function(e){
			e.preventDefault();
			var link = $(this).attr('href');
			var ref = $(this).attr('data-rel');
			$('#'+ref).html('<div class=\"loading\"><span>Loading...</span></div>');
			$('#'+ref).load(link+' #'+ref+' > *', function() {
				$('.calendar-event').children().not('h3').hide();
			});
		});	
		$(document).on('click', '.list .my-calendar-nav a', function(e){
			e.preventDefault();
			var link = $(this).attr('href');
			var ref = $(this).attr('data-rel');
			$('#'+ref).html('<div class=\"loading\"><span>Loading...</span></div>');
			$('#'+ref).load(link+' #'+ref+' > *', function() {
				$('li.mc-events').children().not('.event-date').hide();
				$('li.current-day').children().show();
			});
		});
		$(document).on('click', '.mini .my-calendar-nav a', function(e){
			e.preventDefault();
			var link = $(this).attr('href');
			var ref = $(this).attr('data-rel');
			$('#'+ref).html('<div class=\"loading\"><span>Loading...</span></div>');
			$('#'+ref).load(link+' #'+ref+' > *', function() {
				$('.mini .has-events').children().not('.trigger').hide();
			});
		});	
	});
}(jQuery));	