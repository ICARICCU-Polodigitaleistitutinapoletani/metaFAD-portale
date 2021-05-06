<?php
class iiif_controllers_Manifest extends iiif_controllers_CachedController
{
    public function execute($uid, $type, $page, $secondary)
    {
        if(!__Request::get('download'))
        {
            header('Content-Type: application/json');
        }
        else
        {
            header("Content-disposition: attachment; filename=\"manifest.json\""); 
        }

        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        return $this->getFromCache($uid, function() use ($uid, $type, $page, $secondary) {
            $command = $this->application->executeCommand('metafad.viewer.controllers.rest.Viewer', $uid, $type, true, $secondary);
            $data = json_decode($command);
            $manifestService = __ObjectFactory::createObject('iiif.services.Manifest');
            $manifest = $manifestService->getManifest($uid, $data, $type, $page);
            echo json_encode($manifest, JSON_PRETTY_PRINT);
            exit;
        });
    }
}
