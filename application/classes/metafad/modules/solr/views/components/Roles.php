<?php
class metafad_modules_solr_views_components_Roles extends pinax_components_Component
{

    function init()
    {
      $this->defineAttribute('type', false, 'opac', COMPONENT_TYPE_STRING);
      parent::init();
    }

    function process()
    {
      $id = strtoupper(__Request::get('id'));
      $type = $this->getAttribute('type');
      $url = __Config::get('metafad.'.$type.'.roles');
      $arrayUrl = array('opac'=>__Link::makeUrl('solr_search'),
	  					'archive'=> __Link::makeUrl('solr_search_archive_ud'),
					 	'iccd'=>__Link::makeUrl('solr_search_iccd'));

      $request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url.$id, 'GET', '', 'application/x-www-form-urlencoded');
      $request->setAcceptType('application/json');
      $request->execute();

      $response = json_decode($request->getResponseBody());

      $list = array();
      if($response->list)
      {
        foreach ($response->list as $l) {
          $list[] = array('name' => $l->role, 'value' => $arrayUrl[$type].'?rolesQuery='.json_encode(array('query'=>$l->query)));
        }
      }

      if($response->error)
      {
        $list[] = array('name' => '', 'value' => $arrayUrl[$type].'?rolesQuery='.json_encode(array('query'=>'null')));
      }

      $this->_content['list'] = $list;
    }

    function render($outputMode = NULL, $skipChilds = false)
    {
      parent::render($outputMode, $skipChilds);
    }
}
