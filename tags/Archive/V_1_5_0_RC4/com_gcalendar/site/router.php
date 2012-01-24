<?php
/**
 * @version		$Id: router.php 9764 2007-12-30 07:48:11Z ircmaxell $
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * @param	array
 * @return	array
 */
function GCalendarBuildRoute( &$query )
{	
	$segments = array();
	$task = null;
   	if(isset($query['view']))
   	{
    	$segments[] = $query['view'];
    	unset( $query['view'] );
   	}
   	if(isset($query['task']))
   	{
    	$segments[] = $query['task'];
    	$task=$query['task'];
    	unset( $query['task'] );
   	}
   	if($task === 'content'){
	   	if(isset($query['calendarType']))
	   	{
	    	$segments[] = $query['calendarType'];
	    	unset( $query['calendarType'] );
	   	}
	   	if(isset($query['xmlType']))
	   	{
	    	$segments[] = $query['xmlType'];
	    	unset( $query['xmlType'] );
	   	}else{
	   		$segments[] = 'full';
	   	}
	   	if(isset($query['maxResults']))
	   	{
	    	$segments[] = $query['maxResults'];
	    	unset( $query['maxResults'] );
	   	}
	   	if(isset($query['calendarName']))
	   	{
	    	$segments[] = $query['calendarName'];
	    	unset( $query['calendarName'] );
	   	}
	   	if(isset($query['Itemid']))
	   	{
	    	$segments[] = $query['Itemid'];
	    	unset( $query['Itemid'] );
	   	}
   	} else if($task === 'event'){
	   	if(isset($query['eventID']))
	   	{
	    	$segments[] = $query['eventID'];
	    	unset( $query['eventID'] );
	   	}
	   	if(isset($query['ctz']))
	   	{
	    	$segments[] = $query['ctz'];
	    	unset( $query['ctz'] );
	   	}
	   	if(isset($query['calendarName']))
	   	{
	    	$segments[] = $query['calendarName'];
	    	unset( $query['calendarName'] );
	   	}
   	}else{
		if (isset($query['Itemid'])){
			$itemid = (int) $query['Itemid'];
			
			$menu = &JSite::getMenu();
			$params	=& $menu->getParams($itemid);
			if($params->get('name'))
				$segments[] = $params->get('name');
		}
   	} 
   return $segments;
}

/**
 * @param	array
 * @return	array
 */
function GCalendarParseRoute( $segments )
{
	$vars = array();
	$count = count( $segments );
	switch($segments[0])
    {
		case 'content':
	    	$vars['task'] = 'content';
	    	$vars['format'] = 'raw';
	    	$vars['calendarType'] = $segments[1];
	    	$vars['xmlType'] = $segments[2];
	    	$vars['maxResults'] = $segments[3];
	    	$vars['calendarName'] = $segments[4];
	    	if($count == 6)
	    		$vars['Itemid'] = $segments[5];
	   		break;
	   	case 'event':
	    	$vars['task'] = 'event';
	    	$vars['eventID'] = $segments[1];
	    	$vars['calendarName'] = $segments[$count -1];
	    	$ctz = '';
	    	for ($i=2; $i < $count -1; $i++){
	    		$ctz .= $segments[$i];
	    		if($i < $count -2) $ctz .= '/';
	    	} 
	    	$vars['ctz'] = $ctz;
	   		break;
	   	case 'gcalendar':
	    	$vars['task'] = 'gcalendar';
	   		break;
	   	
	}
	return $vars;
}