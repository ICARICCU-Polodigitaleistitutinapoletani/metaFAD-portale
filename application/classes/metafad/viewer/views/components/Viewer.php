<?php
class metafad_viewer_views_components_Viewer extends pinax_components_Component
{
	function init()
  {
      $this->defineAttribute('id',  false, 'viewer', COMPONENT_TYPE_STRING);
      $this->defineAttribute('src',  false, 'static/viewer/dist/index.html', COMPONENT_TYPE_STRING);
      parent::init();
  }

	function render($outputMode = null, $skipChilds = false)
	{
		$id = $this->getAttribute('id');
		$jscript = $this->getAttribute('jscript');
		$file = file_get_contents($this->getAttribute('src'));
		$file = str_replace(
                        array('<script type="text/javascript" src="js/config.js"></script>',
							'href="',
                                'src="',
                                '<!doctype html><html><head><meta charset="utf-8"><title>metaViewerFe</title><meta name="description" content=""><meta name="viewport" content="width=device-width"></head><body>',
                                '</body></html>'),

                        array('','href="static/viewer/dist/','src="static/viewer/dist/', '', ''),$file);

		$popupEcommerce = (__Config::get('metafad.fe.hasEcommerce')) ? __Link::makeUrl('ecommerce_popup') : '';
		$serverRoot = str_replace('/metadato/','',__Link::makeUrl('viewer-service'));
        $viewerHeight = __Config::get('metafad.fe.viewer.viewerHeight');
        if (!$viewerHeight) {
            $viewerHeight = 'window.innerHeight';
        }

		$output = <<<EOD
<script type="text/javascript">
var METAFAD_VIEWER_CONFIG = {
"instance": "develop",
"serverRoot":"$serverRoot",
"popupEcommerce":"$popupEcommerce",
"viewerHeight":$viewerHeight,
"navigatorBlock":true,
"containerBg": "#DDDDDD"
}
</script>
<div id="$id">
$file
</div>
EOD;
		$this->addOutputCode($output);
	}

}
