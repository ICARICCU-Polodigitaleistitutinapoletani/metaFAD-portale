<?php
ob_start();
require_once(__DIR__ .'/../vendor/autoload.php');

$application = pinax_ObjectFactory::createObject('pinaxcms.core.application.Application', '../application','../vendor/icariccu/pinax-2/', '../application/');
pinax_Paths::addClassSearchPath('admin/application/classes/');
$application->runAjax();
