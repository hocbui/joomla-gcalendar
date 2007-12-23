<?php // no direct access

/**
* Google calendar overview module
* @author allon
* @version $Revision: 1.5.0 $
**/

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<iframe
id="mod_gcalendar"
src="<?php echo $calendar; ?>"
width="<?php echo $params->get( 'width' ); ?>"
height="<?php echo $params->get( 'height' ); ?>"
scrolling="<?php echo $params->get( 'scrolling' ); ?>"
align="top"
frameborder="0"
class="mod_gcalendar<?php echo $params->get( 'moduleclass_sfx' ); ?>">
<?php echo JText::_( 'NO_IFRAMES' ); ?>
</iframe>