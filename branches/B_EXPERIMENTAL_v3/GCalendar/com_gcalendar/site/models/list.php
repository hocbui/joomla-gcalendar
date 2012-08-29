<?php
/**
 * @package		GAnalytics
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 Digital Peak, All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

class GCalendarModelList extends JModelList {

	public function getEvents() {
		$store = $this->getStoreId('getEvents');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}


		$date = JFactory::getDate();
		$end = JFactory::getDate($this->state->get('filter.search_end'));

		$date->modify('-1 month');
		$start = JFactory::getDate($this->state->get('filter.search_start'));

		$filter = $this->state->get('filter.search');
		$events = array();
		foreach (GCalendarDBUtil::getCalendars($this->getState('gcids')) as $calendar) {
 			$tmp = GCalendarZendHelper::getEvents($calendar, null, null, $this->getState('list.limit'), $filter, GCalendarZendHelper::ORDER_BY_START_TIME, false, GCalendarZendHelper::SORT_ORDER_ASC, $this->getState('list.start'));
 			$this->setState('total', $this->getState('total', 0) + $tmp->getTotalResults()->text);
 			if(!empty($tmp)){
 				foreach ($tmp as $event) {
 					if(!($event instanceof GCalendar_Entry)){
 						continue;
 					}
 					$events[] = $event;
 				}
 			}
		}
		$this->cache[$store] = $events;

		return $events;
	}

	public function getTotal() {
		$store = $this->getStoreId('getTotal');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$this->getEvents();
		$this->cache[$store] = $this->getState('total', 0);
		return $this->getState('total', 0);
	}

	public function getStart()
	{
		$store = $this->getStoreId('getstart');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getTotal();
		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $start;

		return $this->cache[$store];
	}

	protected function populateState($ordering = null, $direction = null) {
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		if(JRequest::getInt('list.limit', null) === null){
			$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
			$this->setState('list.limit', $limit);
		} else {
			$this->setState('list.limit', JRequest::getInt('list.limit', 0));
		}

		$limitstart = JRequest::getVar('limitstart', 1, '', 'int');
		$this->setState('list.start', $limitstart);

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter-search');
		$this->setState('filter.search', $search);
		$date = JFactory::getDate();
		$search = $this->getUserStateFromRequest($this->context.'.filter.search_end', 'filter_search_end');
		$this->setState('filter.search_end', empty($search) ? $date->format('Y-m-d') : $search);
		$date->modify('-1 month');
		$search = $this->getUserStateFromRequest($this->context.'.filter.search_start', 'filter_search_start');
		$this->setState('filter.search_start', empty($search) ? $date->format('Y-m-d') : $search);

		$this->setState('gcids', JRequest::getVar('gcids', $this->state->get('parameters.menu')->get('calendarids')));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_ganalytics');
		$this->setState('params', $params);
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}
}