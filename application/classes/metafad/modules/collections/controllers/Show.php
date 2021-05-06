<?php
class metafad_modules_collections_controllers_Show extends pinax_mvc_core_Command
{
  public function execute()
  {
    $id = __Request::get('document_id');
    $ar = pinax_ObjectFactory::createModel('metafad.modules.collections.models.Model');
    $ar->load($id);

    $type = $ar->type;
    if($type == 'ICCD')
    {
      //solrUrl="metafad.iccd.url" solrFieldUrl="metafad.iccd.field.url"
      $c = $this->view->getComponentById('recordSetListIccd');
      $c->setAttribute('enabled','true');
    }
    else if($type == 'SBN')
    {
      $c = $this->view->getComponentById('collectionResult');
      $c->setAttribute('solrUrl','metafad.solr.url');
      $c->setAttribute('solrFieldUrl','metafad.solr.field.url');

      $r = $this->view->getComponentById('recordSetListSbn');
      $r->setAttribute('enabled','true');
    }
    else if($type == 'archive')
    {
      $c = $this->view->getComponentById('collectionResult');
      $c->setAttribute('solrUrl','metafad.archive-ud.url');
      $c->setAttribute('solrFieldUrl','metafad.archive-ud.field.url');

      $r = $this->view->getComponentById('recordSetListArchive');
      $r->setAttribute('enabled','true');
    }
    else if($type == 'metaindice')
    {
      $c = $this->view->getComponentById('collectionResult');
      $c->setAttribute('solrUrl','metafad.metaindice.url');
      $c->setAttribute('solrFieldUrl','metafad.metaindice.field.url');

      $r = $this->view->getComponentById('recordSetListMetaindice');
      $r->setAttribute('enabled','true');
    }

    //Leggo la query
    $arr = parse_url($ar->query);
    //Estraggo i parametri
    parse_str($arr['query'],$parameters);
    //Setto la request per simulare una ricerca
    foreach ($parameters as $key => $value) {
      __Request::set($key,$value);
    }
    if(!__Request::get('search'))
    {
      __Request::set('search','*');
    }
  }
}
