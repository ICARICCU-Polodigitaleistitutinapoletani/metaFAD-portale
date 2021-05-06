<?php
if (!defined('PNX_LOADED'))
{
	require_once(__DIR__ .'/../vendor/autoload.php');
	$application = pinax_ObjectFactory::createObject('pinaxcms.core.application.Application',  '../application','../vendor/icariccu/pinax-2/', '../application/');
	pinax_Paths::addClassSearchPath('../admin/application/classes/');

	if (file_exists(pinax_Paths::get('APPLICATION_STARTUP')))
	{
		// if the startup folder is defined all files are included
		pinax_require_once_dir(pinax_Paths::get('APPLICATION_STARTUP'));
	}
}

$id 	= isset($_REQUEST['id']) ? $_REQUEST['id'] : NULL;
$w 		= isset($_REQUEST['w']) ? $_REQUEST['w'] : NULL;
$h 		= isset($_REQUEST['h']) ? $_REQUEST['h'] : NULL;
$force 	= isset($_REQUEST['f']) ? $_REQUEST['f']=='true' || $_REQUEST['f']=='1': false;
$crop 	= isset($_REQUEST['c']) ? $_REQUEST['c']=='true' || $_REQUEST['c']=='1' : false;
$useThumbnail 	= isset($_REQUEST['t']) ? $_REQUEST['t']=='true' || $_REQUEST['t']=='1' : false;
$cropOffset 	= isset($_REQUEST['co']) ? $_REQUEST['co'] : 0;
$watermark = isset($_REQUEST['watermark']) ? $_REQUEST['watermark'] : 0;

pinaxcms_Pinaxcms::getMediaArchiveBridge()->serveImage($id, $w, $h, $crop, $cropOffset, $force, $useThumbnail);