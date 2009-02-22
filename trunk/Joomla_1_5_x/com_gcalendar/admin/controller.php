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

jimport('joomla.application.component.controller');

/**
 * GCalendar Component Controller
 *
 */
class GCalendarsController extends JController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct(){
		parent::__construct();
	}


	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
		parent::display();
	}

	/**
	 * display the google calendar
	 * @return void
	 */
	function import()
	{
		global $_SESSION, $_GET;
		if (!isset($_SESSION['sessionToken']) && !isset($_GET['token'])) {
			JRequest::setVar( 'isLogin', 'FALSE');
		} else {
			JRequest::setVar( 'isLogin', 'TRUE');
		}
		
		JRequest::setVar('hidemainmenu', 0);

		$document =& JFactory::getDocument();

		$viewType    = $document->getType();
		$viewName    = JRequest::getCmd( 'view', 'import');
		$viewLayout    = JRequest::getCmd( 'layout', 'default' );
		$view = & $this->getView( $viewName, $viewType, '', array( 'base_path'=>$this->_basePath));

		if ($model = & $this->getModel($viewName)) {
			$view->setModel($model, true);
		}
		$view->setLayout($viewLayout);

		if ($cachable && $viewType != 'feed') {
			global $option;
			$cache =& JFactory::getCache($option, 'view');
			$cache->get($view, 'display');
		} else {
			$view->display();
		}
	}

	/**
	 * display the google calendar
	 * @return void
	 */
	function google()
	{
		JRequest::setVar( 'view', 'google'  );
		JRequest::setVar('hidemainmenu', 0);

		parent::display();
	}

	/**
	 * display the google calendar
	 * @return void
	 */
	function support()
	{
		JRequest::setVar( 'view', 'support'  );
		JRequest::setVar('hidemainmenu', 0);

		parent::display();
	}

}
?>
