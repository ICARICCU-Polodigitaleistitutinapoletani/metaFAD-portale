<?php
class museoweb_mediaArchive_controllers_mediaEdit_Edit extends pinax_mvc_core_Command
{
    public function execute()
    {
        $id = __Request::get('id');
        $media = pinax_ObjectFactory::createModel('pinaxcms.models.Media');
        $media->load($id);
        $nisoProxy = pinax_ObjectFactory::createObject('museoweb.mediaArchive.models.proxy.NisoProxy');
        $niso = $nisoProxy->get($id);
        $data = array_merge($media->getValuesAsArray(), $niso);
        $this->view->setData($data);
        if ($media->media_type !== 'IMAGE') {
            $this->setComponentsVisibility('exifData', false);
            $this->setComponentsVisibility('nisoData', false);
            $this->setComponentsAttribute('media_zoom', 'readOnly', true);
            $this->setComponentsAttribute( 'media_watermark', 'readOnly', true);
        }
    }
}