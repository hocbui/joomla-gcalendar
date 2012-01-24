/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.4.5 $
**/


var RSSRequestObjectl = false; // XMLHttpRequest Object
var is24Hourl = true; //24 or 12 hour time

if (window.XMLHttpRequest) { // FF, Safari, Opera
	RSSRequestObjectl = new XMLHttpRequest();
	if (RSSRequestObjectl.overrideMimeType) {
    	RSSRequestObjectl.overrideMimeType('text/xml');
    } 
}
else if (window.ActiveXObject){ // IE
    try {
        RSSRequestObjectl = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            RSSRequestObjectl = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {}
    }
}

RSSRequestl();

/*
* Main AJAX RSS reader request
*/
function RSSRequestl() {
	document.getElementById("latest_events_content").innerHTML = checkingtextl;
	
	// Prepare the request
	RSSRequestObjectl.open("GET", Backendl );
	
	// Set the onreadystatechange function
	RSSRequestObjectl.onreadystatechange = ReqChangel;
	
	// Send
	RSSRequestObjectl.send(null); 
}

/*
* onreadystatechange function
*/
function ReqChangel() {

	// If data received correctly
	if (RSSRequestObjectl.readyState == 4) {
		var xmlDocl;
			//Just to check if it is a different navigator from internet explorer
		if (document.implementation && document.implementation.createDocument){
			xmlDocl = RSSRequestObjectl.responseXML;
		//In case to be the internet explorer
		} else if (window.ActiveXObject){
			//Create a xml tag in run time
			var testandoAppend = document.createElement('xml');
			//Put the requester.responseText in the innerHTML of the xml tag
			testandoAppend.setAttribute('innerHTML',RSSRequestObjectl.responseText);
			//Set the xml tag's id to _formjAjaxRetornoXML
			testandoAppend.setAttribute('id','_formjAjaxRetornoXML');
			//Add the created tag to the page context
			document.body.appendChild(testandoAppend);
			//Just for check put the xmlhttp.responseXML in the innerHTML of the tag
			document.getElementById('_formjAjaxRetornoXML').innerHTML = RSSRequestObjectl.responseText;
			//Now we can get the xml tag and put it on a var
			xmlDocl = document.getElementById('_formjAjaxRetornoXML');
			//So we have a valid xml we can remove the xml tag 
			document.body.removeChild(document.getElementById('_formjAjaxRetornoXML'));
		}
		var nodel = xmlDocl.documentElement; 
		
		// if data is valid
		if (nodel.getElementsByTagName('error').length==0) { 	
			// Parsing Feeds
            var contentl = '';
            
			// Get the calendar title - uncomment next two lines if you want it to show up
			//var title = node.getElementsByTagName('title').item(0).firstChild.data;
			//var content = '<div class="channeltitle">' + title + '</div>';
            var timezonel='';
            try { 
            	timezonel = nodel.getElementsByTagName('timezone').item(0).getAttribute("value");  
            } catch (e) {	
				try {
					timezonel = nodel.getElementsByTagNameNS('*', 'timezone').item(0).getAttribute("value"); 
				} catch (e) {
					var timezonel = '';
				}
			}
            
			// Browse events
			var itemsl = nodel.getElementsByTagName('entry');
			
            var itemTimePrevl = new Date();
            itemTimePrevl.setTime(0000);
            if (itemsl.length == 0) {
				contentl += '<div align="center">'+noEventsTextl+'</div>';
			} else {
				for (var n=0; n < itemsl.length; n++) {
					var itemTitlel=busyTextl;
					
					if(itemsl[n].getElementsByTagName('title').length>0) {
						itemTitlel = itemsl[n].getElementsByTagName('title').item(0).firstChild.data;
                    } else {
						if(itemsl[n].getElementsByTagNameNS('*', 'title').length>0) {
							itemTitlel = items[n].getElementsByTagNameNS('*', 'title').item(0).firstChild.data;
						} 
                    }
					
                    //Here's a little love for our friend IE - he hates standards, like XML namespace.
                    var itemTimeXMLl='';
                    try { 
						var itemTimeXMLl = itemsl[n].getElementsByTagName('published').item(0).firstChild.data;
						if(itemsl[n].getElementsByTagName('updated').length>0)
							itemTimeXMLl = itemsl[n].getElementsByTagName('updated').item(0).firstChild.data;
                    } catch (e) { 
						try {
							var itemTimeXMLl = itemsl[n].getElementsByTagName('gd:published').item(0).firstChild.data;
							if(itemsl[n].getElementsByTagName('gd:updated').length>0)
								itemTimeXMLl = itemsl[n].getElementsByTagName('gd:updated').item(0).firstChild.data;
						} catch (e) {
							try {
								var itemTimeXMLl = itemsl[n].getElementsByTagNameNS('*', 'published').item(0).firstChild.data;
								if(itemsl[n].getElementsByTagNameNS('*', 'updated').length>0)
									itemTimeXMLl = itemsl[n].getElementsByTagNameNS('*', 'updated').item(0).firstChild.data;
							} catch (e) {
								var itemTimeXMLl = '';
							}
						}
                    }
                    
                    var itemEndTimeXMLl='';
                     try { 
						var itemEndTimeXMLl = itemsl[n].getElementsByTagName('when')[0].getAttribute("endTime");  
                    } catch (e) { 
						try {
							var itemEndTimeXMLl = itemsl[n].getElementsByTagName('gd:when')[0].getAttribute("endTime");
						} catch (e) {
							try {
								var itemEndTimeXMLl = itemsl[n].getElementsByTagNameNS('*', 'when')[0].getAttribute("endTime");
							} catch (e) {
								var itemEndTimeXMLl = '';
							}
						}
                    }
                    
                    var isAllDayl = false; //init isAllDay variable
                    var isAllDayEndl = false; //init isAllDay variable
                    var dateFoundl = true;
                    
                    if (itemTimeXMLl && itemTimeXMLl.length <= 10) isAllDayl = true; //just the date is only 10 digits = all day event
                    if (itemEndTimeXMLl && itemEndTimeXMLl.length <= 10) isAllDayEndl = true; //just the date is only 10 digits = all day event
                    
                    var itemTimel = new Date();
                    var itemEndTimel = new Date();
                    
                    if (itemTimeXMLl && itemTimeXMLl.length != 0) {
						if(!isAllDayl){
	                    	itemTimel=new Date(itemTimeXMLl.substr(0,4),
	                    		(itemTimeXMLl.substr(5,2)-1),
	                    		itemTimeXMLl.substr(8,2),
	                    		itemTimeXMLl.substr(11,2),
	                    		itemTimeXMLl.substr(14,2));
	                    } else {
	                    	itemTimel=new Date(itemTimeXMLl.substr(0,4),
	                    		(itemTimeXMLl.substr(5,2)-1),
	                    		itemTimeXMLl.substr(8,2));
	                    }
					} else dateFoundl = false; 
					
					if (itemEndTimeXMLl && itemEndTimeXMLl.length != 0) {
                    	if(!isAllDayEndl){
	                    	itemEndTimel=new Date(itemEndTimeXMLl.substr(0,4),
	                    		(itemEndTimeXMLl.substr(5,2)-1),
	                    		itemEndTimeXMLl.substr(8,2),
	                    		itemEndTimeXMLl.substr(11,2),
	                    		itemEndTimeXMLl.substr(14,2));
	                    } else {
	                    	itemEndTimel=new Date(itemEndTimeXMLl.substr(0,4),
	                    		(itemEndTimeXMLl.substr(5,2)-1),
	                    		(itemEndTimeXMLl.substr(8,2)-1));//subtract one day on a full day event
	                    }
					}
					
					try {
						var itemLinkl =  itemsl[n].getElementsByTagName('link')[0].getAttribute("href");
					} catch (e) {
						var itemLinkl = "";
						
					}
                    
                    var itemContentl = ' - ';
					try { 
                        itemContentl += itemsl[n].getElementsByTagName('content').item(0).firstChild.data;  
                    } catch (e) {	
						try {
							itemContentl += itemsl[n].getElementsByTagNameNS('*', 'content').item(0).firstChild.data; 
						} catch (e) {
							var itemContentl = '';
						}
					}
                    
                    contentl+='<div>';
                    try {
	                    if(!isAllDayl) contentl+= publishedl+" "+dateFormat(itemTimel, dfl);
	                    else contentl+= publishedl+" "+dateFormat(itemTimel, dffl);
                    } catch (e) {
						contentl+=itemTimeXMLl;
					}
					if (showEndDatel==1){
						try {
		                    if(!isAllDayEndl) contentl+= ' - ' + dateFormat(itemEndTimel, dfl);
		                    else contentl+= ' - ' + dateFormat(itemEndTimel, dffl);
	                    } catch (e) {
							contentl+=itemEndTimeXMLl;
						}
					}
                    
                    contentl+='</div>';
                    var linkl = 'href="'+backLinkl.replace('{eventPlace}',itemLinkl.substring(itemLinkl.indexOf('eid=')+4,itemLinkl.length)).replace('{ctzPlace}',timezonel)+'"';
                    if(openInNewWindowl==1)
                      linkl='href="'+itemLinkl+'" target="_blank"';
                    contentl += '<a '+linkl+'>'+itemTitlel+'</a>';
                    contentl+='<br><hr width="100%">';
                    itemTimePrevl.setTime(itemTimel); //Save the last timestamp for next iteration comparison
				}
			}
			
			// Display the result
			document.getElementById("latest_events_content").innerHTML = contentl;
		} else {
			// Tell the reader that there was error requesting data
			var xl=nodel.getElementsByTagName('error');
			for (i=0;i<xl.length;i++) {
			  document.getElementById("latest_events_content").innerHTML = "<div class=error>"+xl[i].childNodes[0].nodeValue+"<div>";
			}
		}
	}
	
}
