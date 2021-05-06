<?php
ob_start();
setlocale(LC_TIME, "it_IT", "it", "it_IT.utf8");

require_once(__DIR__ .'/../../vendor/autoload.php');

$application = pinax_ObjectFactory::createObject('pinaxcms.core.application.AdminApplication', '../../admin/application', '../../vendor/icariccu/pinax-2/', '../../application/');
$application->useXmlSiteMap = true;
$application->setLanguage('it');
$application->login();
$user = $application->getCurrentUser();
if (!$user->isLogged()) {
   header("HTTP/1.0 403 Forbidden");
   exit;
}
$application->runAjax();
