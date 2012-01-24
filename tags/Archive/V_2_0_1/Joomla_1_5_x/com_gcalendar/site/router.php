<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @author Allon Moritz
 * @copyright 2007-2009 Allon Moritz
 * @version $Revision: 2.0.1 $
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