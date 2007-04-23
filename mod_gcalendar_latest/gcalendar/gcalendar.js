/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.3.0 $
**/

var RSSRequestObject = false; // XMLHttpRequest Object
var Backend = rootUrl+'/modules/gcalendar/eventrss.php?calendarUrl='+calendarUrl; // calendar url
window.setInterval("update_timer()", 1200000); // update the data every 20 mins


if (window.XMLHttpRequest) // try to create XMLHttpRequest
	RSSRequestObject = new XMLHttpRequest();

if (window.ActiveXObject)	// if ActiveXObject use the Microsoft.XMLHTTP
	RSSRequestObject = new ActiveXObject("Microsoft.XMLHTTP");

RSSRequest();

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
			
			// Get the calendar title
			var title = node.getElementsByTagName('title').item(0).firstChild.data;
		
			// Browse events
			var items = node.getElementsByTagName('entry');
			content='';
			if (items.length > 0) {
				for (var n=items.length-1; n >= 0; n--)
				{
					var itemTitle="Busy";
					if(items[n].getElementsByTagName('title').length>0)
						itemTitle = items[n].getElementsByTagName('title').item(0).firstChild.data;
					var Summary = items[n].getElementsByTagName('summary').item(0).firstChild.data;
					var itemLink = items[n].getElementsByTagName('id').item(0).firstChild.data;
					var x=items[n].getElementsByTagName('link');

					for(i=0;i<x.length;i++)
					  {
					  //the attlist variable will hold a NamedNodeMap
					  var attlist=x.item(i).attributes;
					  var att=attlist.getNamedItem("href");
					  if(attlist.getNamedItem("type").value=='text/html')
					  itemLink=att.value;
					  }
					
					try 
					{ 
						var itemPubDate = items[n].getElementsByTagName('published').item(0).firstChild.data;
						if(items[n].getElementsByTagName('updated').length>0)
							itemPubDate = items[n].getElementsByTagName('updated').item(0).firstChild.data;
					} 
					catch (e) 
					{ 
						var itemPubDate = '';
					}
					var link = 'href="'+rootUrl+'/index.php?option=com_gcalendar&eventID='+itemLink.substring(itemLink.indexOf('eid=')+4,itemLink.length)+'&name='+calendarName+'"';
                    content += '<a '+link+'>'+itemTitle+'</a>';
					//content += '<a href="'+rootUrl+'/index.php?option=com_gcalendar&page='+itemLink+'">'+itemTitle+'</a>';
					//content += '<br>'+Summary.substring(Summary.indexOf(': ')+2,Summary.indexOf('<br>'))+'<br>';
					content += '<br>'+itemPubDate+'<br>';
				}
				
			}
			// Display the result
			document.getElementById("ajaxreader").innerHTML = content;

			// Tell the reader the everything is done
			document.getElementById("status").innerHTML = "Done.";
			
		}
		else {
			// Tell the reader that there was error requesting data
			document.getElementById("status").innerHTML = "<div class=error>Error requesting data.<div>";
		}
		
		HideShow('status');
	}
	
}

/*
* Main AJAX RSS reader request
*/
function RSSRequest() {

	// change the status to requesting data
	HideShow('status');
	document.getElementById("status").innerHTML = "...........";
	
	// Prepare the request
	RSSRequestObject.open("GET", Backend , true);
	// Set the onreadystatechange function
	RSSRequestObject.onreadystatechange = ReqChange;
	// Send
	RSSRequestObject.send(null); 
}

/*
* Timer
*/
function update_timer() {
	RSSRequest();
}


function HideShow(id){
	var el = GetObject(id);
	if(el.style.display=="none")
	el.style.display='';
	else
	el.style.display='none';
}

function GetObject(id){
	var el = document.getElementById(id);
	return(el);
}
