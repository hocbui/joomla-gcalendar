<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 2.0.0 $
 */

 // no direct access
defined('_JEXEC') or die('Restricted access'); 

if(empty($this->gcalendar)){
  echo JText::_( 'NO_CALENDAR' );
}else{
	$component	= &JComponentHelper::getComponent('com_gcalendar');
	$menu = &JSite::getMenu();
	$items		= $menu->getItems('componentid', $component->id);
	
	$model = & $this->getModel();
	if (is_array($items)){
		global $mainframe;
		$pathway	= &$mainframe->getPathway();
		foreach($items as $item) {
			$paramsItem	=& $menu->getParams($item->id);
			if($paramsItem->get('name')===$this->params->get('name')){
				$pathway->addItem($this->params->get('name'), '');
			}
		}
	}
?>

<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">

<?php
	$calendar_url="";
	if ($this->params->get('use_custom_css')) {
		$calendar_url= JURI::base().'components/com_gcalendar/views/gcalendar/tmpl/googlecal/MyGoogleCal3.php?'.str_replace("http://www.google.com/calendar/embed?","",$this->gcalendar);
	} else {
		$calendar_url=$this->gcalendar;
	}
?>
		<iframe
		id="gcalendar"
		name="iframe"
		src="<?php echo $calendar_url; ?>"
		width="<?php echo $this->params->get( 'width' ); ?>"
		height="<?php echo $this->params->get( 'height' ); ?>"
		scrolling="<?php echo $this->params->get( 'scrolling' ); ?>"
		align="top"
		frameborder="0"
		class="gcalendar<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo JText::_( 'NO_IFRAMES' ); ?>
		</iframe>
		
		</div>
	
<?php
}
?>