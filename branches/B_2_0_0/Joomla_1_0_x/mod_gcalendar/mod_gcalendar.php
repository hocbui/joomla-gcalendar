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

// no direct access
defined('_VALID_MOS') or die('Restricted access');

global $database;

$params->def( 'url', '' );
$params->def( 'scrolling', 'auto' );
$params->def( 'height', '300' );
$params->def( 'width', '100%' );

$name = $params->get('name', '');
$database->setQuery("select id,htmlUrl from #__gcalendar where name='".$name."'");
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
