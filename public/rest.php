<?php
require_once(__DIR__ .'/../vendor/autoload.php');

$application = pinax_ObjectFactory::createObject('pinax.rest.core.Application', '../application','../vendor/icariccu/pinax-2/', '../application/');
$application->setInitSiteMap(true);
$application->sitemapFactory(function($forceReload=false) {
    $siteMap = &__ObjectFactory::createObject('pinaxcms.core.application.SiteMapDB');
    $siteMap->getSiteArray($forceReload);
    return $siteMap;
});
__Paths::add('APPLICATION_TO_ADMIN_CACHE', __Paths::get('CACHE'));
$application->run();
