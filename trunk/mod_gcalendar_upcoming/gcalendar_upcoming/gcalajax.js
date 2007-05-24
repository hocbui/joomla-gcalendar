/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.4.0 $
**/


var RSSRequestObject = false; // XMLHttpRequest Object
var Backend = 'modules/gcalendar_upcoming/eventrss.php'; // Backend url
var is24Hour = true; //24 or 12 hour time
var timeLimit = 0; //How many months timeframe limit

if (window.XMLHttpRequest) // try to create XMLHttpRequest
	RSSRequestObject = new XMLHttpRequest();

else if (window.ActiveXObject)	// if ActiveXObject use the Microsoft.XMLHTTP
	RSSRequestObject = new ActiveXObject("Microsoft.XMLHTTP");

RSSRequest(calendarName);

/*
* onreadystatechange function
*/
function ReqChange() {

	// If data received correctly
	if (RSSRequestObject.readyState==4) {
	
		
		// if data is valid
		if (RSSRequestObject.responseText.indexOf('invalid') == -1) 
		{ 	
			// Parsing Feeds
			var node = RSSRequestObject.responseXML.documentElement; 
            var content = '';
            
			// Get the calendar title - uncomment next two lines if you want it to show up
			//var title = node.getElementsByTagName('title').item(0).firstChild.data;
			//var content = '<div class="channeltitle">' + title + '</div>';
            
            var timezone='';
            try { 
                      timezone = node.getElementsByTagName('timezone').item(0).getAttribute("value");  
                  } 
			catch (e) {	
				try 
				{
					timezone = node.getElementsByTagNameNS('*', 'timezone').item(0).getAttribute("value"); 
				}
				catch (e)
				{
					var timezone = '';
				}
			}
            
			// Browse events
			var items = node.getElementsByTagName('entry');
            var itemTimePrev = new Date();
            itemTimePrev.setTime(0000);
            if (items.length == 0) {
				content += '<div align="center">No events</div>';
			} else {
				for (var n=0; n < items.length; n++)
				{
					var itemTitle="Busy";
					
					if(items[n].getElementsByTagName('title').length>0)
					{
						itemTitle = items[n].getElementsByTagName('title').item(0).firstChild.data;
                    } else
                    {
						if(items[n].getElementsByTagNameNS('*', 'title').length>0)
						{
							itemTitle = items[n].getElementsByTagNameNS('*', 'title').item(0).firstChild.data;
						} 
                    }
					
                    //Here's a little love for our friend IE - he hates standards, like XML namespace. Thanks for making a shitty product Microsoft!
                    try { 
						var itemTimeXML = items[n].getElementsByTagName('when')[0].getAttribute("startTime");  
                        } 
					catch (e) { 
						try 
						{
							var itemTimeXML = items[n].getElementsByTagName('gd:when')[0].getAttribute("startTime");
						} 
						catch (e)
						{
							
							try 
							{
								var itemTimeXML = items[n].getElementsByTagNameNS('*', 'when')[0].getAttribute("startTime");
							} 
							catch (e)
							{
								var itemTimeXML = '';
							}
						}
                    }
                    
                    var isAllDay = false; //init isAllDay variable
                    var dateFound = true;
                    
                    if (itemTimeXML.length <= 10){isAllDay = true;} //just the date is only 10 digits = all day event
                    
                    var itemTime = new Date();
                    
                    if (itemTimeXML.length != 0)
                    {
						itemTime.setTime
							(Date.UTC(itemTimeXML.substr(0,4),(itemTimeXML.substr(5,2)-1),itemTimeXML.substr(8,2)
							,itemTimeXML.substr(11,2),itemTimeXML.substr(14,2)));
					}
					else
					{
						dateFound = false; 
					}
					
					
					try
					{
						var itemLink =  items[n].getElementsByTagName('link')[0].getAttribute("href");
					}
					catch (e) 
					{
						var itemLink = "";
						
					}
                    
                    var itemContent = ' - ';
					try { 
                        itemContent += items[n].getElementsByTagName('content').item(0).firstChild.data;  
                    } 
					catch (e) {	
						try 
						{
							itemContent += items[n].getElementsByTagNameNS('*', 'content').item(0).firstChild.data; 
						}
						catch (e)
						{
							var itemContent = '';
						}
					}
                    
                    content+='<div>';
                    
                    if (dateFound)
                    {
                    	content += +itemTime.getUTCDate()+'.'+(itemTime.getUTCMonth()+1)+'.'+itemTime.getUTCFullYear()+' ';
                    }
                    
                    if (!isAllDay) { content+= getTimeFormatted(itemTime); }
                    content+='</div>';
                    var link = 'href="'+rootUrl+'/index.php?option=com_gcalendar&eventID='+itemLink.substring(itemLink.indexOf('eid=')+4,itemLink.length)+'&name='+calendarName+'&ctz='+timezone+'"';
                    if(openInNewWindow==1)
                      link='href="'+itemLink+'" target="_blank"';
                    content += '<a '+link+'>'+itemTitle+'</a>';
                    content+='<br><hr width="100%">';
                    itemTimePrev.setTime(itemTime); //Save the last timestamp for next iteration comparison
				}
			}
			
			// Display the result
			document.getElementById("gcalajax").innerHTML = content;

			// Tell the reader the everything is done
			//document.getElementById("status").innerHTML = "Done.";
		}
		else {
			// Tell the reader that there was error requesting data
			document.getElementById("st").innerHTML = "<div class=error>Error requesting data.<div>";
		}
		
		Hide('st');
	}
	
}

/*
* Time Format - Month
*/
function getMonthName(dateObject) {
    var m_names = new Array("January", "February", "March", 
    "April", "May", "June", "July", "August", "September", 
    "October", "November", "December");
    return(m_names[dateObject.getUTCMonth()]);
}

/*
* Time Format - Hour
*/
function getTimeFormatted(dateObject) {
    var hours = dateObject.getUTCHours();
    var minutes = dateObject.getUTCMinutes();
    var formattedTime = null;
    if (is24Hour) {
        if (minutes < 10){minutes = "0" + minutes;}
        formattedTime = hours + ':' + minutes;
        return (formattedTime);
    }
    else {
        var ampm = "AM";
        if (hours > 12){
            hours = hours - 12;
            ampm = "PM";}
        if (hours == 12){ampm = 'PM';}
        if (hours == 0) {hours = 12;}
        if (minutes < 10){minutes = "0" + minutes;}
        formattedTime = hours + ':' + minutes + ' ' + ampm;
        return (formattedTime);
    }
}

/*
* Main AJAX RSS reader request
*/
function RSSRequest(gcal_path) {
	document.getElementById("st").innerHTML = ".......";
	
    Backend = Backend + "?gcal_feed=" + escape(gcal_path) + "&timeLimit=" + timeLimit + "&maxResults=" + maxResults;
	// change the status to requesting data

	// Prepare the request
	RSSRequestObject.open("GET", Backend );
	
	// Set the onreadystatechange function
	RSSRequestObject.onreadystatechange = ReqChange;
	
	// Send
	RSSRequestObject.send(null); 
	
}



function Hide(id){
	var el = GetObject(id);
	//if(el.style.display=="none")
	//el.style.display='';
	//else
	el.style.display='none';
}

function GetObject(id){
	var el = document.getElementById(id);
	return(el);
}