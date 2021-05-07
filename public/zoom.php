<?php
if (!defined('PNX_LOADED'))
{
  require_once(__DIR__ .'/../vendor/autoload.php');
  new pinaxcms_core_application_Application('../application', '../vendor/icariccu/pinax-2/', '../application/');
  pinax_Paths::addClassSearchPath('admin/application/classes/');
  if (file_exists(pinax_Paths::get('APPLICATION_STARTUP'))) {
    // if the startup folder is defined all files are included
    pinax_require_once_dir(pinax_Paths::get('APPLICATION_STARTUP'));
  }
  pinax_defineBaseHost();
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : NULL;
if(__Config::get('metafad.url'))
{
    $id = str_replace(__Config::get('metafad.url'),__Config::get('metafad.base'),$id);
}
if (is_null($id)) exit;

$md5 = md5($id);
$cacheFolder = pnx_nestedCacheFile($md5);
$zoomFile = $cacheFolder.$md5.'.xml';

if ( !file_exists( $zoomFile ) ) {
  pinax_import('pinaxcms.mediaArchive.MediaManager');
  set_time_limit(0);

  $fileUrl = $id.'&w='.__Request::get('w');

  $zoomSize = __Config::get('metafad.image.size.zoom');
  if ($zoomSize && $zoomSize!='original') {
    $fileUrl = str_replace('meta_dam/', 'meta_dam/admin/rest/dam/', metafad_modules_dam_Common::replaceUrlWithSize($fileUrl, $zoomSize));
  }
  $tempFile = __Paths::get('CACHE_JS'). md5($fileUrl);

  $r = @copy($fileUrl, $tempFile);
  if (!$r) {
    // pezza perché alcuni media sono nel dam di produzione
    copy(str_replace('meta_dam_dev/', 'meta_dam/', $fileUrl), $tempFile);
  }

  $adapter = new Deepzoom\ImageAdapter\Imagick($tempFile);
  $file = new Deepzoom\StreamWrapper\File;
  $descriptor = new Deepzoom\Descriptor($file);
  $converter = new Deepzoom\ImageCreator($file, $descriptor, $adapter);
  $converter->create( realpath( $tempFile ), $zoomFile );
  @unlink($tempFile);
}
echo  PNX_HOST .'/'.ltrim($zoomFile, '.');

function pnx_nestedCacheFile($filename)
{
    $folder = __Paths::get('CACHE_JS').pnx_nestedCacheFolder($filename, 2);
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    return $folder;
}

function pnx_nestedCacheFolder($filename, $nestLevel, $root=""){
    $nestLevel = max(intval($nestLevel), 0);

    if ($nestLevel>0) {
        $hash = md5($filename);
        for ($i=0 ; $i<$nestLevel; $i++) {
            $root = $root . 'cache_' . substr($hash, 0, $i + 1) . '/';
        }
    }

    return $root;
}
