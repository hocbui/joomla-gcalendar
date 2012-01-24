<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.2 $
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

<script language="javascript" type="text/javascript">
function iFrameHeightGC() {
	var h = 0;
	if ( !document.all ) {
		h = document.getElementById('gcalendar').contentDocument.height;
		document.getElementById('gcalendar').style.height = h + 60 + 'px';
	} else if( document.all ) {
		h = document.frames('gcalendar').document.body.scrollHeight;
		document.all.gcalendar.style.height = h + 20 + 'px';
	}
}
</script>
<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">

<?php
if ($this->params->get('page_title')) {
?>
	<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo $this->params->get( 'header' ); ?>
	</div>
<?php
}
// auto height control
if ( $this->params->def( 'height_auto' ) ) {
	$load = 'onload="iFrameHeightGC()"';
} else {
	$load = '';
}
?>
<iframe <?php echo $load; ?>
id="gcalendar"
name="iframe"
src="<?php echo $this->gcalendar; ?>"
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