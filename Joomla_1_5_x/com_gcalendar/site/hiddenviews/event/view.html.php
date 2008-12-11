<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 2.0.0 $
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
		
		$component	= &JComponentHelper::getComponent('com_gcalendar');
		$menu = &JSite::getMenu();
		$items		= $menu->getItems('componentid', $component->id);
		
		$model = & $this->getModel();
		if (is_array($items)){
			global $mainframe;
			$pathway	= &$mainframe->getPathway();
			foreach($items as $item) {
				$paramsItem	=& $menu->getParams($item->id);
				if($paramsItem->get('name')===$model->getState('calendarName')){
					$pathway->addItem($paramsItem->get('name'),'');
					$pathway->addItem($this->eventID,'');
				}
			}
		}
		
		parent::display($tpl);
	}
}
?>
