<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.0 $
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the GCalendar Component
 *
 */
class GCalendarViewEvent extends JView
{
	function display($tpl = null)
	{
		$gcalendar = $this->get( 'GCalendar' );
		$this->assignRef( 'gcalendar',	$gcalendar );
		
		$this->assignRef( 'eventID', JRequest::getVar('eventID', null));
		$this->assignRef( 'timezone', JRequest::getVar('ctz', null));
		
		$menu = &JSite::getMenu();
		$items  = $menu->getItems('link', 'index.php?option=com_gcalendar&view=gcalendar');
		
		$model = & $this->getModel();
		foreach($items as $item)
		{
			$params	=& $menu->getParams($item->id);
			if($params->get('name')===$model->getState('calendarName')){
				global $mainframe;
		
				$pathway	= &$mainframe->getPathway();
				$pathway->addItem($item->title, 'index.php?option=com_gcalendar&view=gcalendar&Itemid='.$item->id);
				$pathway->addItem($this->eventID,'');
			}
		}
		
		parent::display($tpl);
	}
}
?>
