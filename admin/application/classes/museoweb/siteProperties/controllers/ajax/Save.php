<?php
class museoweb_siteProperties_controllers_ajax_Save extends pinax_mvc_core_CommandAjax
{
    // TODO creare un proxy per gestire le proprietà del sito
    public function execute($data)
    {
        $data = json_decode($data);
        $newData = array();
        $newData['title'] = $data->title;
        $newData['address'] = $data->address;
        $newData['logoUrl'] = $data->logoUrl;
        $newData['copyright'] = $data->copyright;
        $newData['slideShow'] = $data->slideShow;
        $newData['analytics'] = $data->analytics;
        $newData['addthis'] = $data->addthis;
		$newData['facebook'] = $data->facebook;
		$newData['twitter'] = $data->twitter;
		$newData['instagram'] = $data->instagram;
		$newData['youtube'] = $data->youtube;
        $newData['collectionsHomeText'] = $data->collectionsHomeText;

        pinax_Registry::set(__Config::get('REGISTRY_SITE_PROP').$this->application->getEditingLanguage(), serialize($newData));
        return true;
    }
}
