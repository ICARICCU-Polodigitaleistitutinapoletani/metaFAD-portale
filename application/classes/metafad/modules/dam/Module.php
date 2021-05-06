<?php
class metafad_modules_dam_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.modules.dam.Module';
        $moduleVO->name = 'DAM';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.modules.dam';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';

        pinax_Modules::addModule($moduleVO);
    }
}