<?php
class metafad_views_components_SearchSections extends pinax_components_Component
{
	private $arraySearchActions = array(
		'metaindice' => 'metaindice',
		'metaindiceau' => 'metaindiceau',
		'archive' => 'archive',
		'archiveud' => 'archive',
		'archiveca' => 'archive',
		'iccd' => 'iccd',
		'sbn' => 'sbn',
		'index' => 'sbn'
	);

	function init()
	{
		parent::init();
	}

	function process()
	{
		$this->_content['searchs'] = array();
		
    	if(__Config::get('metafad.fe.hasInstitutes') == true)
		{
			$instituteKey = __Session::get('instituteKey');
			//TODO rimuovere da session e tenere in url
			if($instituteKey)
			{
				$instituteProxy = pinax_ObjectFactory::createObject('metafad.institutes.models.proxy.InstituteProxy');
				$instituteConfig = $instituteProxy->getInstituteConfigByKey($instituteKey);
				$this->getSearchByConfigForInstitute($instituteConfig);
			}
			else
			{
				$this->getSearchByConfig();
			}
		}
		else
		{
			$this->getSearchByConfig();
		}
	}

	function getSearchByConfig()
	{
		$searchs = json_decode(__Config::get('metafad.fe.activeSearch'));
		foreach ($searchs as $k => $v) {
			if($v)
			{
				$this->_content['searchs'][] = $k;
			}
		}
		$this->_content['activeSearch'] = $this->arraySearchActions[__Request::get('action')];
	}

	function getSearchByConfigForInstitute($instituteConfig)
	{
		$searchs = json_decode(__Config::get('metafad.fe.activeSearch'));
		foreach ($searchs as $k => $v)
		{
			if($v && $instituteConfig->$k == 'true')
			{
				$this->_content['searchs'][] = $k;
			}
		}
		$this->_content['activeSearch'] = $this->arraySearchActions[__Request::get('action')];
	}
}
