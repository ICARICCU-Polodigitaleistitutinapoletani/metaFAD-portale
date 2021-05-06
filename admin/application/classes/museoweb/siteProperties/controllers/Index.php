<?php
class museoweb_siteProperties_controllers_Index extends pinax_mvc_core_Command
{
    public function execute()
    {
        $siteProp = unserialize(pinax_Registry::get(__Config::get('REGISTRY_SITE_PROP').$this->application->getEditingLanguage(), ''));
        if ($siteProp) {
            $this->view->setData($siteProp);
        }
    }
}