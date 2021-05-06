<?php
class museoweb_mediaArchive_controllers_Delete extends pinaxcms_mediaArchive_controllers_Delete
{
    public function execute($id)
    {
        $media = pinaxcms_mediaArchive_MediaManager::getMediaById($id);
        if($media->type === 'IMAGE') {
            $nisoProxy = pinax_ObjectFactory::createObject('museoweb.mediaArchive.models.proxy.NisoProxy');
            $nisoProxy->delete($id);
        }
        parent::execute($id);
    }
}