<?php
class metafad_modules_solr_controllers_Index extends pinax_mvc_core_Command
{
  function execute()
  {
    $arrayChecked = array();
    $arrayBasicMeta = array('patrimonio'=>false,'bibliografico'=>false,'archivistico'=>false);
    $arrayBasicMetaAut = array('enti'=>false,'persone'=>false,'famiglie'=>false);
    $arrayDigitale = array('libri'=>false,'manoscritti'=>false,'grafica'=>false,'operadarte'=>false,'documenti'=>false);

    if(__Request::get('fromRA') == 'true')
    {
      $this->setLastSearchParams();
    }

    $countBasicMeta = $this->checkMixedIndex($arrayBasicMeta);
    $countBasicMetaAut = $this->checkMixedIndex($arrayBasicMetaAut);
    $countDigitale = $this->checkMixedIndex($arrayDigitale);
    $addFilter = '';

    if($countBasicMeta == 2)
    {
      $url = __Link::makeUrl('solr_search_metaindice');
      $addFilter = $this->getAddFilter($arrayBasicMeta);
      $search = (__Request::get('search')) ?: '*';
      pinax_helpers_Navigation::gotoUrl($url.'?search='.$search.'&filter_Tutto='.$search.'&filter_Tutto-words=AND'.'&filter_dominio='.$addFilter.'&filter_dominio-words=OR');
    }
    else if($countBasicMetaAut >= 1)
    {
      $url = __Link::makeUrl('solr_search_metaindice_au');
      $addFilter = $this->getAddFilter($arrayBasicMetaAut);
      $search = (__Request::get('search')) ?: '*';
      pinax_helpers_Navigation::gotoUrl($url.'?search='.$search.'&filter_Tutto='.$search.'&filter_Tutto-words=AND'.'&filter_tipoEntita='.$addFilter.'&filter_tipoEntita-words=OR');
    }

    if($countDigitale >= 1)
    {
      $url = __Link::makeUrl('solr_search_metaindice');
      $addFilter = $this->getAddFilter($arrayDigitale);
      $search = (__Request::get('search')) ?: '*';
      pinax_helpers_Navigation::gotoUrl($url.'?search='.$search.'&filter_Area+digitale='.$addFilter.'&filter_Area+digitale-words=OR&filter_Tutto='.$search.'&filter_Tutto-words=AND&facet%5B0%5D=digitale_s%3A"true"');
    }

    //Metaindice generale
    if((__Request::get('patrimonio') == 'true' && __Request::get('bibliografico') == 'true' && __Request::get('archivistico') == 'true') || __Request::get('metaindice') == 'true')
    {
      $search = (__Request::get('search')) ? 'search='.__Request::get('search') : 'search=';
      pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('solr_search_metaindice').'?'.$search);
    }
    //Metaindice authority
    if(__Request::get('enti') == 'true' || __Request::get('persone') == 'true' || __Request::get('famiglie') == 'true')
    {
      //TODO FILTRARE CORRETTAMENTE
      $search = (__Request::get('search')) ? 'search='.__Request::get('search') : 'search=';
      pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('solr_search_metaindice_au').'?'.$search);
    }

    //Prima di andare sugli indici specifici verifico che non ci sia una richiesta
    //su metaindice "incompleto" (solo 2 domini)


    if(__Request::get('patrimonio') == 'true')
    {
      $search = (__Request::get('search')) ? 'search='.__Request::get('search') : 'search=';
      pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('solr_search_iccd').'?'.$search);
    }
    else if(__Request::get('archivistico') == 'true')
    {
      $search = (__Request::get('search')) ? 'search='.__Request::get('search') : 'search=';
      pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('solr_search_archive_ud').'?'.$search);
    }
    else if(__Request::get('bibliografico') == 'true')
    {
      $search = (__Request::get('search')) ? 'search='.__Request::get('search') : 'search=';
      pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('solr_search').'?'.$search);
    }

    if(!__Request::get('search'))
    {
      __Request::set('search','');
    }

    //Fix filtri collezioni e autori
    if(__Request::get('query') || __Request::get('rolesQuery'))
    {
      __Request::set('search','*');
    }

	//POLODEBUG-525: rimuovo filtro digitale e applico direttamente la faccetta
	$current = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	if(strpos($current,'filter_digitale=true') !== false)
	{
		pinax_helpers_Navigation::gotoUrl(str_replace('filter_digitale=true','facet%5B0%5D=digitale_s%3A"true"',$current));
	}
  }

  public function checkMixedIndex(&$array)
  {
    foreach ($array as $key => $value) {
      if(__Request::get($key) == 'true')
      {
        $array[$key] = true;
        $count++;
      }
    }
    return $count;
  }

  public function getAddFilter($array)
  {
    $mapping = array('patrimonio'=>'patrimonio',
                            'bibliografico'=>'bibliografico',
                            'archivistico'=>'archivi',
                            'enti'=>'ente',
                            'persone'=>'persona',
                            'famiglie'=>'famiglia',
                            'libri' => 'libri',
                            'manoscritti' => 'manoscritti',
                            'grafica' => 'grafica',
                            'operadarte' => 'opera d\'arte',
                            'documenti' => 'documenti'
                          );

    foreach ($array as $key => $value) {
      if($value === true)
      {
        $addFilter .= $mapping[$key] . '+';
      }
    }
    return $addFilter;
  }

  public function setLastSearchParams()
  {
    $request = __Request::getAllAsArray();
    $filters = array();
    foreach($request as $k => $v)
    {
      if(strpos($k,'filter_') !== false)
      {
        $filters[$k] = $v;
      }
    }
    __Request::remove('fromRA');
    __Session::set('lastSearchParams', $filters);
  }
}
