<?php
class metafad_modules_institutesManagement_Module
{
    static function registerModule()
    {
        pinax_loadLocale('metafad.modules.institutesManagement');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'institutesManagement';
        $moduleVO->name = __T('Gestione Istituti');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.modules.institutesManagement';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page pageType="metafad.modules.institutesManagement.views.Admin" id="institutesManagement" value="{i18n:'.$moduleVO->name.'}" icon="icon-circle-blank" adm:acl="*" />';

		if(__Config::get('metafad.fe.hasInstitutes'))
		{
        	pinax_Modules::addModule( $moduleVO );
		}
    }
}
