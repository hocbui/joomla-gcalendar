window.addEvent('domready', function(){
			//-vertical
			
			var gcSlide= new Fx.Slide('gcalendar_list');
			
			$('toggle_gc').addEvent('click', function(e){
				e = new Event(e);
				gcSlide.toggle();
				
var gcalImage = 'components\/com_gcalendar\/views\/gcalendar\/tmpl\/up.png';
if(gcSlide.open){
var gcalImage = 'components\/com_gcalendar\/views\/gcalendar\/tmpl\/down.png';
}
window.document.getElementById('toggle_gc_status').src = gcalImage ;e.stop();
			});
			gcSlide.hide();
		});


function updateGCalendarFrame(calendar){ 
	var orig_url = window.document.getElementById('gcalendar_frame').src; 	
	var new_url = ""; 	
	if(calendar.checked){ 		
		new_url = orig_url+calendar.value; 	
	}else{ 		
		new_url = orig_url.replace(calendar.value,""); 	
	} 	
	window.document.getElementById('gcalendar_frame').src = new_url;
} 
