window.addEvent('domready', function() {
	var status = {
		'true': 'open',
		'false': 'close'
	};
	
	//-vertical

	var myVerticalSlide = new Fx.Slide('gcalendar_list');

	$('v_toggle').addEvent('click', function(e){
		e.stop();
		myVerticalSlide.toggle();
	});
	
	// When Vertical Slide ends its transition, we check for its status
	// note that complete will not affect 'hide' and 'show' methods
	myVerticalSlide.addEvent('complete', function() {
		$('vertical_status').set('html', status[myVerticalSlide.open]);
	});

});

function updateFrame(calendar){ 
	var orig_url = window.document.getElementById('gcalendar_frame').src; 	
	var new_url = ""; 	
	if(calendar.checked){ 		
		new_url = orig_url+calendar.value; 	
	}else{ 		
		new_url = orig_url.replace(calendar.value,""); 	
	} 	
	window.document.getElementById('gcalendar_frame').src = new_url;
} 
