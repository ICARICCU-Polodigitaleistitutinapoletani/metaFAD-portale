<?php
class metafad_modules_solr_views_components_AdvancedSearch extends pinax_components_Component
{

	function init()
	{
		$this->defineAttribute('section',  false, 'bibliografico', COMPONENT_TYPE_STRING);
		$this->defineAttribute('solrFieldUrl',  false, 'metafad.solr.field.url', COMPONENT_TYPE_STRING);
		$this->defineAttribute('searchUrl',  false, 'solr_search', COMPONENT_TYPE_STRING);
		$this->defineAttribute('autocompleteUrl', false, 'metafad.solr.autocomplete.url', COMPONENT_TYPE_STRING);
		parent::init();
	}

	function process()
	{
		$url = __Config::get('metafad.solr.url');
		$model = __Config::get('advancedSearch.model');

		if (__Config::get('metafad.fe.hasInstitutes') == false) {
			$instituteProxy = __ObjectFactory::createObject('metafad.institutes.models.proxy.InstituteProxy');
			$institutes = $instituteProxy->getAllInstitutes();
			$instituteKey = $institutes[0]['key'];
		} else {
			$instituteKey = __Session::get('instituteKey');
		}

		if(__Config::get('metafad.fe.search.hasCustomServices'))
		{
			$fc = json_decode(file_get_contents(__Config::get('metafad.solr.field.url')))->fields;
			$fieldsConfig = array();
			foreach ($fc as $f) {
				$fieldsConfig[$f->id] = $f->startsWith;
			}
		}

		$maskName = (!__Request::get('mask')) ? 'Ricerca avanzata' : __Request::get('mask');

		if($maskName == 'Ricerca avanzata UN')
		{
			$this->setAttribute('searchUrl','solr_search_archive_ud');
			$this->setAttribute('solrFieldUrl','metafad.archive-ud.field.url');
			$this->setAttribute('autocompleteUrl','metafad.archive-ud.autocomplete.url');
		}

		$section = $this->getAttribute('section');

		if(__Config::get('metafad.fe.search.checkPermission'))
		{
			$cu = ($this->_user->isLogged()) ? '&fq=controlloUtente:1' : '&fq=-controlloUtente:1';
		}

		$request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url.'select', 'POST', 'q=*&fq=document_type_t:'.$model.$cu.'&wt=json&rows=100', 'application/x-www-form-urlencoded');
		$request->setAcceptType('application/json');
		$request->execute();

		if(__Config::get('metafad.fe.search.hasCustomServices') || $this->getAttribute('section') == 'bibliografico')
		{
			$solrList = json_decode(file_get_contents(__Config::get($this->getAttribute('solrFieldUrl'))));
			$arrayRange = array();
			foreach ($solrList->fields as $f)
			{
				$arrayRange[$f->id] = $f->range;
			}
		}
		else
		{
			$arrayRange = array();
		}

		$response = json_decode($request->getResponseBody());

		$fields = array();
		$accordionList = array();
		$hasFilters = false;
		if($response->response->docs)
		{
			if($instituteKey)
			{
				$searchIndex = -1;
				foreach ($response->response->docs as $doc) {
					$searchIndex++;
					$name = $doc->name[0];
					if($name !== $maskName) continue;
					if($doc->section[0] !== $section) continue;
					if($doc->instituteKey_s != $instituteKey) continue;
					$found = true;
					break;
				}
			}
			$count = -1;
			foreach ($response->response->docs as $doc) {
				$count++;
				$name = $doc->name[0];
				if($name !== $maskName) continue;
				if($doc->section[0] !== $section) continue;
				if($found)
				{
					if($instituteKey && $searchIndex != $count)
					{
						continue;
					}
					if(!$instituteKey)
					{
						if($doc->instituteKey_s != __Config::get('metafad.default.institute') && $doc->instituteKey_s != null)
							continue;
					}
				}
				else if($doc->instituteKey_s != __Config::get('metafad.default.institute'))
				{
					continue;
				}
				$label = ($doc->labelFE[0]) ? $doc->labelFE[0] : $name;
				$fieldList = json_decode($doc->doc_store[0])->fields;
				if($fieldList)
				{
					foreach ($fieldList as $f) {
						$obj = new StdClass();
						$obj->id = str_replace(array('[',']'),array('#','##'),$f->linkedFields);
						$obj->label = $f->label;
						if(__Config::get('metafad.fe.search.hasCustomServices'))
						{
							$obj->startsWith = $fieldsConfig[$obj->id];
						}
						$obj->type = $f->fieldType;
						$obj->accordion = $f->accordion;
						if(!in_array($f->accordion,$accordionList))
						{
							$accordionList[] = $f->accordion;
						}
						$obj->range = $arrayRange[$f->linkedFields];
						if($f->fieldType == 'closed')
						{
							$hasFilters = true;
						}
						$fields[] = $obj;
					}
					break;
				}
			}
		}
		$this->_content['accordionList'] = $accordionList;
		$this->_content['label'] = $label;
		$this->_content['fields'] = $fields;
		$this->_content['hasFilters'] = $hasFilters;
		$this->_content['solrSearch'] = __Link::makeUrl($this->getAttribute('searchUrl'));
		$this->_content['autocompleteUrl'] = __Config::get($this->getAttribute('autocompleteUrl'));

		$lastSearchParams = __Session::get('lastSearchParams');
		if(!empty($lastSearchParams))
		{
			foreach($lastSearchParams as $k => $v)
			{
				$this->_content[$k] = $v;
			}
		}
	}

	function render($outputMode = NULL, $skipChilds = false)
	{
		parent::render($outputMode, $skipChilds);
	}
}
