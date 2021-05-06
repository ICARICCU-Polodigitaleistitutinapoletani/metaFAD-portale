<?php
if (!defined('PNX_LOADED'))
{
    require_once(__DIR__ .'/../../vendor/autoload.php');
    $application = pinax_ObjectFactory::createObject('pinaxcms.core.application.Application', '../../admin/application', '../../vendor/icariccu/pinax-2/', '../../application/');
    pinax_Paths::addClassSearchPath('application/classes/');

    if (file_exists(pinax_Paths::get('APPLICATION_STARTUP')))
    {
        // if the startup folder is defined all files are included
        pinax_require_once_dir(pinax_Paths::get('APPLICATION_STARTUP'));
    }
}


include('../getFile.php');