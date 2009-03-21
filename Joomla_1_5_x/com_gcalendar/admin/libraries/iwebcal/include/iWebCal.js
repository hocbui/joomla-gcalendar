/* iWebCal Version 2.0 beta
 * Copyright (C) 2003-2005 David A. Feldman.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of version 2 of the GNU General Public License 
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU 
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License 
 * along with this program; if not, write to the Free Software Foundation, 
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA. Or, 
 * visit http://gnu.org.
 * 
 * This file is part of the iWebCal calendar-viewing service. The iWebCal
 * service is available on the Web at http://iWebCal.com, and does not
 * require any programming knowledge or Web server configuration to use.
 * Anyone with an iCal or other .ics calendar file and a place to post
 * it on the Web can view the calendar using iWebCal.
 */
 
/*
 * iWebCal.js: JavaScripts used by iWebCal.
 *
 * File version 2.0b3, last modified April 26, 2005.
 */

var savedHeight = "";

function toggleShowCompleted() {
	document.controlForm.iwebcalview.value = "tasks";
	if (document.taskControlForm.showCompleted.checked) {
		document.controlForm.showCompleted.value = "1";
	}
	else {
		document.controlForm.showCompleted.value = "0";
	}
	document.controlForm.submit();	
}												

function eventOver(eventOb) {
	//eventOb.style.overflow = "visible";
}

function eventOut(eventOb) {
	//eventOb.style.overflow = "hidden";
}

function imageSwap(imgID, newSrc) {
	document.getElementById(imgID).src = newSrc;
}