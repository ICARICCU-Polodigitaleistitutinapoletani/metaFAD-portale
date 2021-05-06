<?php
class metafad_modules_collections_Module
{
    static function registerModule()
    {
        pinax_loadLocale('metafad.modules.collections');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'Collections';
        $moduleVO->name = __T('Collezioni');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.modules.collections';
        $moduleVO->pageType = 'metafad.modules.collections.views.FrontEnd';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page pageType="metafad.modules.collections.views.Admin" id="Collections" value="{i18n:'.$moduleVO->name.'}" icon="icon-circle-blank" adm:acl="*" />';

        pinax_Modules::addModule( $moduleVO );
    }
}
