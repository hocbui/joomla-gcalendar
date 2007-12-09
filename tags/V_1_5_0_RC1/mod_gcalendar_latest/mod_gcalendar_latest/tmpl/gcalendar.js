/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.5.0 $
**/


var RSSRequestObjectl = false; // XMLHttpRequest Object
var is24Hourl = true; //24 or 12 hour time

if (window.XMLHttpRequest) // try to create XMLHttpRequest
	RSSRequestObjectl = new XMLHttpRequest();

else if (window.ActiveXObject)	// if ActiveXObject use the Microsoft.XMLHTTP
	RSSRequestObjectl = new ActiveXObject("Microsoft.XMLHTTP");

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
	RSSRequestObjectl.overrideMimeType('text/xml');
	
	// Send
	RSSRequestObjectl.send(null); 
}

/*
* onreadystatechange function
*/
function ReqChangel() {

	// If data received correctly
	if (RSSRequestObjectl.readyState == 4) {
		var nodel = RSSRequestObjectl.responseXML.documentElement; 
		
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
				for (var n=0; n < maxResultsl && n<itemsl.length; n++) {
					var itemTitlel=busyTextl;
					
					if(itemsl[n].getElementsByTagName('title').length>0) {
						itemTitlel = itemsl[n].getElementsByTagName('title').item(0).firstChild.data;
                    } else {
						if(itemsl[n].getElementsByTagNameNS('*', 'title').length>0) {
							itemTitlel = items[n].getElementsByTagNameNS('*', 'title').item(0).firstChild.data;
						} 
                    }
					
                    //Here's a little love for our friend IE - he hates standards, like XML namespace. Thanks for making a shitty product Microsoft!
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
                    
                    var isAllDayl = false; //init isAllDay variable
                    var dateFoundl = true;
                    
                    if (itemTimeXMLl.length <= 10) isAllDayl = true; //just the date is only 10 digits = all day event
                    
                    var itemTimel = new Date();
                    
                    if (itemTimeXMLl.length != 0) {
						itemTimel.setTime
							(Date.UTC(itemTimeXMLl.substr(0,4),(itemTimeXMLl.substr(5,2)-1),itemTimeXMLl.substr(8,2)
							,itemTimeXMLl.substr(11,2),itemTimeXMLl.substr(14,2)));
					} else dateFoundl = false; 
					
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
                    if(!isAllDayl) contentl= contentl+ publishedl+" "+dateFormatl(itemTimel, dfl);
                    else contentl = contentl+ publishedl+" "+dateFormatl(itemTimel, dffl);
                    
                    contentl+='</div>';
                    var linkl = 'href="'+rootUrll+'index.php?option=com_gcalendar&task=event&eventID='+itemLinkl.substring(itemLinkl.indexOf('eid=')+4,itemLinkl.length)+'&calendarName='+calendarNamel+'&ctz='+timezonel+'"';
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

var dateFormatl = function () {
	var	token        = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloZ]|"[^"]*"|'[^']*'/g,
		timezone     = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function (value, length) {
			value = String(value);
			length = parseInt(length) || 2;
			while (value.length < length)
				value = "0" + value;
			return value;
		};

	// Regexes and supporting functions are cached through closure
	return function (date, mask) {
		// Treat the first argument as a mask if it doesn't contain any numbers
		if (
			arguments.length == 1 &&
			(typeof date == "string" || date instanceof String) &&
			!/\d/.test(date)
		) {
			mask = date;
			date = undefined;
		}

		date = date ? new Date(date) : new Date();
		if (isNaN(date))
			throw "invalid date";

		var dF = dateFormat;
		mask   = String(dF.masks[mask] || mask || dF.masks["default"]);

		var	d = date.getDate(),
			D = date.getDay(),
			m = date.getMonth(),
			y = date.getFullYear(),
			H = date.getHours(),
			M = date.getMinutes(),
			s = date.getSeconds(),
			L = date.getMilliseconds(),
			o = date.getTimezoneOffset(),
			flags = {
				d:    d,
				dd:   pad(d),
				ddd:  dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m:    m + 1,
				mm:   pad(m + 1),
				mmm:  dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy:   String(y).slice(2),
				yyyy: y,
				h:    H % 12 || 12,
				hh:   pad(H % 12 || 12),
				H:    H,
				HH:   pad(H),
				M:    M,
				MM:   pad(M),
				s:    s,
				ss:   pad(s),
				l:    pad(L, 3),
				L:    pad(L > 99 ? Math.round(L / 10) : L),
				t:    H < 12 ? "a"  : "p",
				tt:   H < 12 ? "am" : "pm",
				T:    H < 12 ? "A"  : "P",
				TT:   H < 12 ? "AM" : "PM",
				Z:    (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
				o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4)
			};

		return mask.replace(token, function ($0) {
			return ($0 in flags) ? flags[$0] : $0.slice(1, $0.length - 1);
		});
	};
}();

// Some common format strings
dateFormatl.masks = {
	"default":       "ddd mmm d yyyy HH:MM:ss",
	shortDate:       "m/d/yy",
	mediumDate:      "mmm d, yyyy",
	longDate:        "mmmm d, yyyy",
	fullDate:        "dddd, mmmm d, yyyy",
	shortTime:       "h:MM TT",
	mediumTime:      "h:MM:ss TT",
	longTime:        "h:MM:ss TT Z",
	isoDate:         "yyyy-mm-dd",
	isoTime:         "HH:MM:ss",
	isoDateTime:     "yyyy-mm-dd'T'HH:MM:ss",
	isoFullDateTime: "yyyy-mm-dd'T'HH:MM:ss.lo"
};

// Internationalization strings
dateFormatl.i18n = {
	dayNames:   [
		"Sun", "Mon", "Tue", "Wed", "Thr", "Fri", "Sat",
		"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
	],
	monthNames: [
		"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
		"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
	]
};

// For convenience...
Date.prototype.formatl = function (mask) {
	return dateFormatl(this, mask);
}
