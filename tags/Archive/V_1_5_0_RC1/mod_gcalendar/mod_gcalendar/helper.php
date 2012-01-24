<?php

/**
* Google calendar overview module
* @author allon
* @version $Revision: 1.5.0 $
**/

class modGCalendarHelper
{
    /**
     * Retrieves the calendar
     *
     * @param array $params An object containing the module parameters
     * @access public
     */    
    function getCalendarUrl( $params )
    {
        if($params->get('url')){
        	return $params->get('url');
        } else {
	        jimport( 'joomla.application.component.model' );
	        
	        JModel::addIncludePath(JPATH_COMPONENT.DS.'models');
	        $model =JModel::getInstance('GCalendar','GCalendarModel');
	        $model->setState('parameters.menu', $params);
	        
	        return $model->getGCalendar();
	    }
    }
}

?>
