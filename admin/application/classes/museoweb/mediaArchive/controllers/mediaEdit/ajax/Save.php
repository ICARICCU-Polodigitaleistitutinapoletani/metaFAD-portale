<?php
class museoweb_mediaArchive_controllers_mediaEdit_ajax_Save extends pinaxcms_mediaArchive_controllers_mediaEdit_ajax_Save
{
    public function execute($data)
    {
        $res = parent::execute($data);
        if (is_array($res) && $res['errors'] && empty($this->mediaIds)) {
                return $res;
        }

        $data = json_decode($data);
        if(!$data->media_originalFileName) { // Add
            $i = 0;
            foreach ($this->mediaIds as $id) {
                $media = pinaxcms_mediaArchive_MediaManager::getMediaById($id);
                if($media->type === 'IMAGE') {
                    $nisoProxy = pinax_ObjectFactory::createObject('museoweb.mediaArchive.models.proxy.NisoProxy');
                    $originalSize = $media->getOriginalSizes();
                    $name = $data->medias->__originalFileName[$i] ?: $data->mediaFileServer;
                    $nisoData = $nisoProxy->getDataFromImage($id, $name, $originalSize);
                    $nisoProxy->save($nisoData, 'add');
                }
                $i++;
            }
        } else { // Edit
            $type = pinaxcms_mediaArchive_MediaManager::getMediaTypeFromExtension(array_pop(explode('.', $data->media_originalFileName)));
            if($type === 'IMAGE') {
                $nisoProxy = pinax_ObjectFactory::createObject('museoweb.mediaArchive.models.proxy.NisoProxy');
                $nisoProxy->save($data);
            }
        }

        return $res;
    }

}