<?php


/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');

//--------------------------------------------------------------- configuration
define('PHPWG_ROOT_PATH', $params->get('path', '') ); // relative path to your gallery

//-------------------------------------------------------------------- includes
include_once( PHPWG_ROOT_PATH.'include/common.inc.php' );
//------------------------------------------------------------- which picture ?
$where = '';
if ( $user['forbidden_categories'] != '' )
{
  $where = ' WHERE category_id NOT IN ('.$user['forbidden_categories'].')';
}
$query = '
SELECT id, path, tn_ext, category_id, file, date_available
  FROM '.IMAGES_TABLE.'
    INNER JOIN '.IMAGE_CATEGORY_TABLE.' AS ic ON id = ic.image_id
  '.$where.'
  ORDER BY date_available desc
  LIMIT 0, 1;';
$result = mysql_query( $query );
echo mysql_num_rows($result).'al';
if (mysql_num_rows($result) > 0)
{
	$row = mysql_fetch_array( $result );
	//-------------------------- thumbnail URL and direct link to related picture
	// URL of the thumbnail
	$thumb_url = get_thumbnail_src($row['path'], $row['tn_ext']);
	if (substr($thumb_url, 0 , 12) == './galleries/')
	{
		$thumb_url = PHPWG_ROOT_PATH.substr($thumb_url,2);
	}
	//the url to the image 
	$thumb_url = PHPWG_ROOT_PATH.$thumb_url;
	// link to the gallery
	$thumb_link = PHPWG_ROOT_PATH.'picture.php?cat='.$row['category_id']
	.'&amp;image_id='.$row['id'];
	//echo $thumb_link;
	//------------------------------ display thumbnail with related picture link
	//------------------------------ here you can customize your displayfield
	echo '<a href="'.$thumb_link.'" class="thumblink" target="_top" style="text-decoration: none">
	<div align="left">
	<img src="'.$thumb_url.'" alt="" class="thumbnail" />
	<p>
	name = '.$row['file'].'<br>
	date = '.$row['date_available'].'
	</p>
	</div></a>';
}
?>