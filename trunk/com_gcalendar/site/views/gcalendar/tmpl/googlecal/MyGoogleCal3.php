<?php
/*******************************************************************************
 * FILE: MyGoogleCal3.php
 *
 * DESCRIPTION:
 *  This script is an intermediary between an iframe and Google Calendar that
 *  allows you to override the default style.
 *
 * USAGE:
 *  <iframe src="MyGoogleCal3.php?src=user%40domain.tld"></iframe>
 *
 *  where user@domain.tld is a valid Google Calendar account.
 *
 * VALID QUERY STRING PARAMETERS:
 *    title:         any valid url encoded string 
 *                   if not present, takes title from first src
 *    showTitle:     0 or 1 (default)
 *    showNav:       0 or 1 (default)
 *    showDate:      0 or 1 (default)
 *    showTabs:      0 or 1 (default)
 *    showCalendars: 0 or 1 (default)
 *    mode:          WEEK, MONTH (default), AGENDA
 *    height:        a positive integer (should be same height as iframe)
 *    wkst:          1 (Sun; default), 2 (Mon), or 7 (Sat)
 *    hl:            en, zh_TW, zh_CN, da, nl, en_GB, fi, fr, de, it, ja, ko, 
 *                   no, pl, pt_BR, ru, es, sv, tr
 *                   if not present, takes language from first src
 *    bgcolor:       url encoded hex color value, #FFFFFF (default)
 *    src:           url encoded Google Calendar account (required)
 *    color:         url encoded hex color value     
 *                   must immediately follow src
 *    
 *    The query string can contain multiple src/color pairs.  It's recommended 
 *    to have these pairs of query string parameters at the end of the query 
 *    string.
 *
 * HISTORY:
 *   21 June     2008 - Reverted to an older custom JavaScript file
 *   18 May      2008 - Corrected a bunch of typos 
 *   24 April    2008 - Original release
 *                      Uses the technique from MyGoogleCal for IE browsers and
 *                      the technique from MyGoogleCal2 for the rest.
 *   
 * ACKNOWLEDGMENTS:
 *   Michael McCall (http://www.castlemccall.com/) for pointing out "htmlembed"
 *   Mike (http://mikahn.com/) for the link to the online CSS formatter
 *
 * copyright (c) by Brian Gibson
 * email: bwg1974 yahoo com
 ******************************************************************************/

/* URL for overriding stylesheet
 * The best way to create this stylesheet is to 
 * 1) Load "http://www.google.com/calendar/embed?src=user%40domain.tld" in a
 *    browser,
 * 2) View the source (e.g., View->Page Source in Firefox),
 * 3) Copy the relative URL of the stylesheet (i.e., the href value of the 
 *    <link> tag), 
 * 4) Load the stylesheet in the browser by pasting the stylesheet URL into 
 *    the address bar so that it reads similar to:
 *    "http://www.google.com/calendar/embed/d003e2eff7c42eebf779ecbd527f1fe0embedcompiled.css"
 * 5) Save the stylesheet (e.g., File->Save Page As in Firefox)
 * Edit this new file to change the style of the calendar.
 *
 * As an alternative method, take the URL you copied in Step 3, and paste it
 * in the URL field at http://mabblog.com/cssoptimizer/uncompress.html.
 * That site will automatically format the CSS so that it's easier to edit.
 */
$stylesheet = 'mygooglecal3.css';

/* For the IE stylesheet replace "embed" with "htmlembed" in step (1) 
 */
$stylesheet_ie = 'mygooglecal3ie.css';

/*******************************************************************************
 * DO NOT EDIT BELOW UNLESS YOU KNOW WHAT YOU'RE DOING
 ******************************************************************************/

// URL for the calendar
$url = "";
$is_ie = FALSE;
if(count($_GET) > 0) {
  if(stristr($_SERVER['HTTP_USER_AGENT'], 'msie') === FALSE) {
    $url = "http://www.google.com/calendar/embed?" . $_SERVER['QUERY_STRING'];
  } else {
    $url = "http://www.google.com/calendar/htmlembed?" . $_SERVER['QUERY_STRING'];
    $is_ie = TRUE;
  }
}

// Request the calendar
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$buffer = curl_exec($ch);
curl_close($ch);

if($is_ie) {
  // Fix hrefs, image sources, and stylesheet
  $pattern = '/(href="render)/';
  $replacement = 'href="http://www.google.com/calendar/render';
  $buffer = preg_replace($pattern, $replacement, $buffer);

  $pattern = '/(href="event)/';
  $replacement = 'href="http://www.google.com/calendar/event';
  $buffer = preg_replace($pattern, $replacement, $buffer);

  $pattern = '/(http:\/\/www.google.com\/calendar\/htmlembed)/';
  $replacement = 'MyGoogleCal3.php';
  $buffer = preg_replace($pattern, $replacement, $buffer);

  $pattern = '/(src="images)/';
  $replacement = 'src="http://www.google.com/calendar/images';
  $buffer = preg_replace($pattern, $replacement, $buffer);

  $pattern = '/(<link.*>)/';
  $replacement = '<link rel="stylesheet" type="text/css" href="' . $stylesheet_ie . '" />';
  $buffer = preg_replace($pattern, $replacement, $buffer);
} else {
  // Point stylesheet and javascript to custom versions
  $pattern = '/(<link.*>)/';
  $replacement = '<link rel="stylesheet" type="text/css" href="' . $stylesheet . '" />';
  $buffer = preg_replace($pattern, $replacement, $buffer);

  $pattern = '/src="(.*js)"/';
  $replacement ='src="MyGoogleCal3.js"';
  $buffer = preg_replace($pattern, $replacement, $buffer);
}

// display the calendar
print $buffer;
?>
