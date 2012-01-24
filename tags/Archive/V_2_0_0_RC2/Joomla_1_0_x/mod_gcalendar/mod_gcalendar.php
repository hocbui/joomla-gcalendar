<?php


/**
* Google calendar overview module
* @author allon
* @version $Revision: 2.0.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');

global $database;

$params->def( 'url', '' );
$params->def( 'scrolling', 'auto' );
$params->def( 'height', '300' );
$params->def( 'height_auto', '1' );
$params->def( 'width', '100%' );

$name = $params->get('name', '');
$database->setQuery('select id,htmlUrl from #__gcalendar where name=\''.$name.'\'');
$results = $database->loadObjectList();
$url = '';
foreach ($results as $result) {
	$url= $result->htmlUrl;
}
?>

<iframe
id="mod_gcalendar"
src="<?php echo $url; ?>"
width="<?php echo $params->get( 'width' ); ?>"
height="<?php echo $params->get( 'height' ); ?>"
scrolling="<?php echo $params->get( 'scrolling' ); ?>"
align="top"
frameborder="0"
class="mod_gcalendar<?php echo $params->get( 'moduleclass_sfx' ); ?>">
<?php echo _CMN_IFRAMES; ?>
</iframe>
