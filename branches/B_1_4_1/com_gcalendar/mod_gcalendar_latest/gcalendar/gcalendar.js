/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.4.1 $
**/

var RSSRequestObject1 = false; // XMLHttpRequest Object
var Backend1 = 'modules/gcalendar/eventrss.php?cal_name='+calendarName1+"&lang="+lang; // calendar url
window.setInterval("update_timer()", 1200000); // update the data every 20 mins


if (window.XMLHttpRequest) // try to create XMLHttpRequest
	RSSRequestObject1 = new XMLHttpRequest();

if (window.ActiveXObject)	// if ActiveXObject use the Microsoft.XMLHTTP
	RSSRequestObject1 = new ActiveXObject("Microsoft.XMLHTTP");

RSSRequest1();

/*
* onreadystatechange function
*/
function ReqChange() {
	// If data received correctly
	if (RSSRequestObject1.readyState==4) {
	
		// if data is valid
		if (RSSRequestObject1.responseText.indexOf('invalid') == -1) 
		{ 	
			// Parsing Feeds
			var node = RSSRequestObject1.responseXML.documentElement;
			
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
					var link = 'href="'+rootUrl+'/index.php?option=com_gcalendar&eventID='+itemLink.substring(itemLink.indexOf('eid=')+4,itemLink.length)+'&name='+calendarName1+'&ctz='+timezone+'"';
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
function RSSRequest1() {

	// change the status to requesting data
	HideShow('status');
	document.getElementById("status").innerHTML = "...........";
	
	// Prepare the request
	RSSRequestObject1.open("GET", Backend1 , true);
	// Set the onreadystatechange function
	RSSRequestObject1.onreadystatechange = ReqChange;
	// Send
	RSSRequestObject1.send(null); 
}

/*
* Timer
*/
function update_timer() {
	RSSRequest1();
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
