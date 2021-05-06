<?php
class metafad_viewer_views_components_ViewerIIIF extends pinax_components_Component
{
	function init()
  {
    $this->defineAttribute('id',  false, 'viewer', COMPONENT_TYPE_STRING);
    parent::init();
  }

  function process()
  {
    $id = __Request::get('id');
    $type = __Request::get('type');

    $url = __Link::makeUrl('manifest', ['uid' => $id], ['type' => $type]);

    $this->_content['drawSidePanel'] = true;
    
    $this->_content['manifestUrl'] = $url;
  }

}
