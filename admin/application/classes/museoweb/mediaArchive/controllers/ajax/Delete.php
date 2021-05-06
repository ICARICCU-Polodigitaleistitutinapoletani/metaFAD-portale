<?php
class museoweb_mediaArchive_controllers_ajax_Delete extends pinax_mvc_core_CommandAjax
{
     public function execute($data)
     {
        $ids = explode(',', __Request::get('ids'));
        if (!empty($ids)) {
            $mediaProxy = pinax_ObjectFactory::createObject('pinaxcms.mediaArchive.models.proxy.MediaProxy');
            foreach ($ids as $id) {
                $media = pinaxcms_mediaArchive_MediaManager::getMediaById($id);
                if($media->type === 'IMAGE') {
                    $nisoProxy = pinax_ObjectFactory::createObject('museoweb.mediaArchive.models.proxy.NisoProxy');
                    $nisoProxy->delete($id);
                }
                $mediaProxy->deleteMedia($id);
            }
        }
     }
}