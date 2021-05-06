<?php
class museoweb_modules_ecommerce_Module
{
    private static $helper = null;

    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'museoweb_ecommerce';
        $moduleVO->name = __T('museoweb.modules.ecommerce.views.FrontEnd');
        $moduleVO->description = 'Modulo per l\'ecommerce';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'museoweb.modules.ecommerce';
        $moduleVO->pageType = 'museoweb.modules.ecommerce.views.FrontEnd';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );

        $application = pinax_ObjectValues::get('org.pinax', 'application');
        if ($application && !$application->isAdmin()) {
            self::$helper = pinax_ObjectFactory::createObject('museoweb.modules.ecommerce.Helper');
        }
    }
}
