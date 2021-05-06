<?php
class metafad_modules_solr_views_components_SearchType extends pinax_components_Component
{

    function init()
    {
      $this->defineAttribute('section',  false, 'bibliografico', COMPONENT_TYPE_STRING);
      parent::init();
    }

    function process()
    {
      $section = $this->getAttribute('section');
      $arrayAction = array(
                      'bibliografico'=>'index',
                      'archivi' => 'archive',
                      'patrimonio' => 'iccd',
                      'metaindice' => 'metaindice',
                      'metaindiceau' => 'metaindiceau'
                      );
      $arraySearch = array(
                      'bibliografico'=>'solr_search',
                      'archivi' => 'solr_search_archive',
                      'patrimonio' => 'solr_search_iccd',
                      'metaindice' => 'metaindice',
                      'metaindiceau' => 'metaindiceau'
                      );
      $url = __Config::get('metafad.solr.url');
      $model = __Config::get('advancedSearch.model');
      $isDetail = (__Request::get('id')) ? true : false;

      $request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url.'select', 'POST', 'q='.$model.'&wt=json&rows=100', 'application/x-www-form-urlencoded');
      $request->setAcceptType('application/json');
      $request->execute();

      $response = json_decode($request->getResponseBody());

      $maskList = array();
      if($response->response->docs)
      {
        foreach ($response->response->docs as $doc) {
          if($doc->section[0] != $section) continue;
          $name = $doc->name[0];
          $label = $doc->labelFE[0];
          //TODO la distinzione dalla ricerca avanzata di base forse non dovrebbe
          //basarsi su una semplice stringa
          if($name == 'Ricerca avanzata')
          {
            $this->_content['labelRicercaAvanzata'] = ($label) ? $label : $name ;
            continue;
          }
          if($name == 'Ricerca semplice')
          {
            $this->_content['labelRicercaSemplice'] = ($label) ? $label : $name ;
            continue;
          }
          if($section == 'archivi')
          {
            if($name == 'Ricerca avanzata CA' || $name == 'Ricerca avanzata UN')
            {
              $this->_content['label'.$name] = ($label) ? $label : $name ;
              continue;
            }
          }
          $label = ($label) ? $label : $name;
          $maskList[] = array('name'=>$name,'label'=>$label);
        }
      }

      $this->_content['maskList'] = $maskList;
      $this->_content['isDetail'] = $isDetail;
      $this->_content['urlRicercaAvanzata'] = __Link::makeUrl('solr_advancedSearch',array('action'=>$arrayAction[$section]));
      $this->_content['urlRicercaSemplice'] = __Link::makeUrl('solr_advancedSearch',array('action'=>$arrayAction[$section])) . '?mask=Ricerca semplice';
      $this->_content['urlRicercaAvanzataUN'] = __Link::makeUrl('solr_advancedSearch',array('action'=>'archive')) . '?mask=Ricerca avanzata UN';
      $this->_content['urlRicercaAvanzataCA'] = __Link::makeUrl('solr_advancedSearch',array('action'=>'archive')) . '?mask=Ricerca avanzata CA';
    }

    function render($outputMode = NULL, $skipChilds = false)
    {
      parent::render($outputMode, $skipChilds);
    }
}
