<?php
require_once(__DIR__ .'/../vendor/autoload.php');

$application = new pinaxcms_core_application_Application('../application', '../vendor/icariccu/pinax-2/', '../application/');
$application->run();