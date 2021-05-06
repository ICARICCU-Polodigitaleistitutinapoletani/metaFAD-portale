<?php
require_once(__DIR__ .'/../../vendor/autoload.php');
$application = pinax_ObjectFactory::createObject('pinaxcms.core.application.AdminApplication', '../../admin/application', '../../vendor/icariccu/pinax-2/', '../../application/');
$application->runSoft();
$application->executeCommand('pinaxcms.mediaArchive.controllers.Upload');
