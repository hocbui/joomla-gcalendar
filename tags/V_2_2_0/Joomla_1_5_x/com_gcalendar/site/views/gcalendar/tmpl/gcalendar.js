window
		.addEvent(
				'domready',
				function() {
					var gcSlide = new Fx.Slide('gc_gcalendar_view_list');

					$('gc_gcalendar_view_toggle')
							.addEvent(
									'click',
									function(e) {
										e = new Event(e);
										gcSlide.toggle();

										var oldImage = window.document
												.getElementById('gc_gcalendar_view_toggle_status').src;
										var gcalImage = oldImage;
										var path = oldImage.substring(0,
												oldImage.lastIndexOf('/'));
										if (gcSlide.open)
											var gcalImage = path + '/down.png';
										else
											var gcalImage = path + '/up.png';
										window.document
												.getElementById('gc_gcalendar_view_toggle_status').src = gcalImage;
										e.stop();
									});
					gcSlide.hide();
				});

function updateGCalendarFrame(calendar) {
	if (calendar.checked) {
		jQuery('#gcalendar_component').fullCalendar('addEventSource',
				calendar.value);
	} else {
		jQuery('#gcalendar_component').fullCalendar('removeEventSource',
				calendar.value);
	}
}
