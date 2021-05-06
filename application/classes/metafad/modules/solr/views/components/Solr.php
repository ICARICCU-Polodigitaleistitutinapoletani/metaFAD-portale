<?php
class metafad_modules_solr_views_components_Solr extends pinax_components_SearchFilters
{
	protected $fields = array();
	protected $facetFields = array();
	protected $facets;
	protected $filters = array();
	protected $search;
	protected $start;
	protected $fieldList = array();
	private $searchQuery = array();
	private $solrResponse;
	private $solrUrl;
	private $instituteFilters = array();

	function init()
	{
		// define the custom attributes
		$this->defineAttribute('queryBuilder',  false, 'metafad.modules.solr.filters.DefaultQueryBuilder', COMPONENT_TYPE_STRING);
		$this->defineAttribute('processResults',  false, null, COMPONENT_TYPE_STRING);
		$this->defineAttribute('facetsField',  false, 'facet', COMPONENT_TYPE_STRING);
		$this->defineAttribute('searchField',  false, 'search', COMPONENT_TYPE_STRING);
		$this->defineAttribute('order',  false, 'score desc', COMPONENT_TYPE_STRING);
		$this->defineAttribute('paginate', false,     NULL,    COMPONENT_TYPE_OBJECT);
		$this->defineAttribute('resultPerPage', false,    __Config::get('metafad.fe.resultPerPage'),    COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('immediateSearch', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('solrUrl', false, 'metafad.solr.url', COMPONENT_TYPE_STRING);
		$this->defineAttribute('generalField', false, 'Tutto', COMPONENT_TYPE_STRING);
		$this->defineAttribute('recordType', false, 'sbn', COMPONENT_TYPE_STRING);
		$this->defineAttribute('solrFieldUrl',  false, 'metafad.solr.field.url', COMPONENT_TYPE_STRING);
		$this->defineAttribute('fieldList', false, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('facetList', false, '', COMPONENT_TYPE_STRING);
		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		$this->processChilds();

		$this->collectFacets();
		//Collezioni i filtri eventuali della ricerca avanzata
		$this->collectAdvancedFields();

		$this->collectFieldList();

		$this->collectSearch();

		if($this->getAttribute('recordType') !== 'sbn' && !__Config::get('metafad.fe.search.hasCustomServices'))
		{
 			$this->setFieldsFromJson(__Config::get($this->getAttribute('fieldList')));
			$this->setFacetFieldsJson(__Config::get($this->getAttribute('facetList')));
		}

		if ($this->search || $this->getAttribute('immediateSearch')) {

			$this->buildSearchQuery();
			$qb = pinax_ObjectFactory::createObject($this->getAttribute('queryBuilder'));
			if (!$qb) {
				throw metafad_modules_solr_Exception::queryBuilderDontExists($this->getAttribute('queryBuilder'));
			}
			$this->searchQuery = $qb->build($this->searchQuery, $this->search, $this->facets);

			// esegue la ricerca
			$this->doSearch();
		}
	}

	function render_html_onStart()
	{
		if (!$this->getAttribute('immediateSearch')) {
			parent::render_html_onStart();
		}
	}

	function render_html_onEnd()
	{
		if (!$this->getAttribute('immediateSearch')) {
			parent::render_html_onEnd();
		}
	}

	public function buildHttpQuery($searchQuery)
	{
		$temp = array_merge($searchQuery, array());
		unset($temp['url']);
		unset($temp['action']);
		foreach($searchQuery as $k=>$v) {
			if (is_array($v)) {
				if ($k=='facet.field' || $k=='fq') {
					foreach($v as $v1) {
						$url .= $k.'='.$v1.'&';
					}
					unset($temp[$k]);
				} else {
					$temp[$k] = implode($v, ',');
				}
			}
		}

		return $url.http_build_query($temp);
	}

	public function buildJsonSimpleQuery($searchQuery)
	{
		//TODO per ora è molto semplificato, per la sola ricerca semplice
		//prevedere anche in $clause campi extra
		$temp = array_merge($searchQuery, array());
		$arrayOperator = array(
			'AND' => 'contains all',
			'OR' => 'contains one',
			'NOT' => 'contains one',
			'NOTALL' => 'contains all',
			'=' => 'contains one',
			'starts with' => 'starts with'
		);
		$query = array('query'=>array());

		//Se non ci sono filtri la ricerca è sul campo tutto
		if(!$this->filters)
		{
			$clause = new StdClass();
			$clause->type = 'CompostedClause';
			$clause->innerOperator = array('operator' => 'AND');
			$clause->clauses = array();

			$c = new StdClass();
			$c->type = 'SimpleClause';
			$c->operator = array('operator' => 'AND');
			$c->field = $this->getAttribute('generalField');
			$c->innerOperator = array('operator' => 'contains one');
			$c->values = array($this->search);

			$clause->clauses[] = $c;
		}
		//Altrimenti costruisco 'clause' con i filtri della ricerca
		else
		{
			$clause = new StdClass();
			$clause->type = 'CompostedClause';
			$clause->innerOperator = array('operator' => 'AND');
			$clause->clauses = array();

			foreach ($this->filters as $key => $value) {
				$range = false;
				if(strpos($key,'-words') !== false) continue;
				if($value == null) continue;
				$c = new StdClass();
				$c->type = 'SimpleClause';
				if(is_array($value))
				{
					$operator = 'AND';
				}
				else
				{
					$operator = ($this->filters[$key.'-words'] == 'NOTALL') ? 'NOT' : $this->filters[$key.'-words'];
				}
				$c->operator = array('operator' => $operator);

				if($c->operator['operator'] == null)
				{
					$c->operator['operator'] = 'AND';
				}

				if($this->getAttribute('recordType') == 'sbn' || __Config::get('metafad.fe.search.hasCustomServices'))
				{
					$c->field = (in_array($key,$this->fieldList)) ? $key : str_replace('_',' ',$key);
				}
				else {
					$c->field = $key.'_txt';
				}

				if(is_array($value))
				{
					$c->innerOperator = array('operator'=>'between');
				}
				else
				{
					$c->innerOperator = array('operator' => $arrayOperator[$this->filters[$key.'-words']]);
				}

				if($c->innerOperator['operator'] == null)
				{
					$c->innerOperator['operator'] = 'AND';
				}

				if($this->filters[$key.'-words'] == '=' && !is_array($value))
				{
					$value = '"'.$value.'"';
				}

				$c->values = is_array($value) ? $value : array($value);
				$clause->clauses[] = $c;
			}
			if(!count($clause->clauses))
			{
				$c = new StdClass();
				$c->type = 'SimpleClause';
				$c->operator = array('operator' => 'AND');
				$c->field = $this->getAttribute('generalField');
				$c->innerOperator = array('operator' => 'contains one');
				$c->values = array($this->search);
				$clause->clauses[] = $c;
			}

		}

		if(!empty($this->instituteFilters))
		{
			$filterClause = new StdClass();
			$filterClause->type = 'CompostedClause';
			$filterClause->innerOperator = array('operator' => 'OR');
			$filterClause->clauses = array();

			$count = 0;
			foreach ($this->instituteFilters as $i) {
				$instistuteFilter = explode(':',$i);
				$operator = ($count === 0) ? 'OR' : 'AND' ;
				$count++;
				$c = new StdClass();
				$c->type = 'SimpleClause';
				$c->operator = array('operator' => $operator);
				$c->field = $instistuteFilter[0];
				$c->innerOperator = array('operator' => '=');
				$c->values = str_replace('"','',array($instistuteFilter[1]));
				$filterClause->clauses[] = $c;
			}
		}

		if(!empty($filterClause))
		{
			$complexClause = new StdClass();
			$complexClause->type = 'CompostedClause';
			$complexClause->innerOperator = array('operator' => 'AND');
			$complexClause->clauses = array();
			$complexClause->clauses[] = $filterClause;
			$complexClause->clauses[] = $clause;
		}
		$query['query']['clause'] = ($complexClause) ?:$clause;
		$query['query']['filters'] = array();

		if($searchQuery['fq'])
		{
			foreach($searchQuery['fq'] as $f)
			{
				$facetVal = explode(":",urldecode($f),2);

				$filter = new StdClass();

				$filter->type = 'SimpleClause';
				$filter->operator = array('operator'=>'AND');
				$filter->field = $facetVal[0];
				$filter->innerOperator = array('operator'=>'contains all');
				$filter->values = array(str_replace("\"","",$facetVal[1]));

				$query['query']['filters'][] = $filter;
			}
		}

		$resultPerPage = $this->getAttribute('resultPerPage');

		$query['query']['start'] = 0 + $resultPerPage * $this->start;
		$query['query']['rows'] = $resultPerPage;
		$query['query']['facetLimit'] = 200;
		$query['query']['facetMinimum'] = 1;
		$query['query']['facets'] = null;
		$query['query']['fq'] = null;
		$query['query']['fieldNamesAreNative'] = false;

		//TODO forse meglio definire i campi sul sorting in un file json
		$arraySortingFields = array('metaindice' => 'Titolo ordinamento', 'metaindiceau' => 'Autore ordinamento',
									'sbn'=>'Titolo [sintetico]','sbnaut'=>'Autore [sintetico]',
									'iccd'=>'Titolo ordinamento','iccdaut'=>'Autore ordinamento');

		$type = $this->getAttribute('recordType');

		if(__Request::get('az'))
		{
			$query['query']['orderClauses'] = array(array('fieldname' => $arraySortingFields[$type],'direction'=>'asc'));
		}
		else if(__Request::get('za'))
		{
			$query['query']['orderClauses'] = array(array('fieldname' => $arraySortingFields[$type],'direction'=>'desc'));
		}
		else if(__Request::get('azaut'))
		{
			$query['query']['orderClauses'] = array(array('fieldname' => $arraySortingFields[$type.'aut'],'direction'=>'asc'));
		}
		else if(__Request::get('zaaut'))
		{
			$query['query']['orderClauses'] = array(array('fieldname' => $arraySortingFields[$type.'aut'],'direction'=>'desc'));
		}

		if($this->getAttribute('recordType') == 'sbn' || __Config::get('metafad.fe.search.hasCustomServices'))
		{
			return json_encode($query);
		}
		else
		{
			return $query;
		}
	}

	protected function doSearch()
	{
		$postBody = $this->buildJsonSimpleQuery($this->searchQuery);

		if($this->getAttribute('recordType') == 'sbn' || __Config::get('metafad.fe.search.hasCustomServices'))
		{
			$url = $this->searchQuery['url'];
			$this->solrUrl = $url.'?'.$postBody;
			$request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url, 'POST', $postBody, 'application/json');
		}
		else
		{
			$url = $this->searchQuery['url'].$this->searchQuery['action'];
			$url .= '?' . $this->createFqFromDefaultQuery($postBody);
			$request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url, 'GET');
		}

		$request->setAcceptType('application/json');
		$request->execute();

		$this->solrResponse = json_decode($request->getResponseBody());

		if ($this->solrResponse) {
			$paginateClass = $this->getAttribute('paginate');
			if ($paginateClass) {
				$paginateClass->setRecordsCount($this->solrResponse->response->numFound);
			}
		}
	}

	private function collectFacets()
	{
		$this->facets = __Request::get($this->getAttribute('facetsField'), array());
		$temp = array();
		foreach( $this->facets as $f ) {
			$temp[] = str_replace('\"', '"', $f );
		}
		$temp = $this->checkAreaDigitale($temp);
		//In questo controllo inserisco nella query un filtro su istituti se settato da utente
		if(__Session::get('instituteKey'))
		{
			$action = __Request::get('action');
			if($action === 'index' || $this->getAttribute('recordType') == 'sbn'){
				$temp[] = 'localizzazione_ss:"'.__Session::get('instituteKeySBN').'"';
			}
			else if($action !== 'metaindice')
			{
				$temp[] = 'institutekey_s:"'.__Session::get('instituteKey').'"';
			}
			else if($action == 'metaindice')
			{
				$this->instituteFilters[] = 'localizzazione:"'.__Session::get('instituteKeyMetaindice').'"';
				$this->instituteFilters[] = 'InstituteKey:"'.__Session::get('instituteKey').'"';
			}
		}
		$this->facets = $temp;
	}

	private function checkAreaDigitale($a)
	{
		$digitale = false;
		foreach ($a as $k => $v) {
			if(strpos($v,'area_digitale_ss') === 0)
			{
				$areaDigitale = $k;
			}
			if(strpos($v,'digitale_s') === 0)
			{
				$digitale = true;
			}
		}

		if(!$digitale && $areaDigitale >= 0)
		{
			$s = array();
			foreach ($a as $k => $v) {
				if($k !== $areaDigitale)
				{
					$s[] = $v;
				}
			}
			return $s;
		}
		else
		{
			return $a;
		}
	}

	private function collectAdvancedFields()
	{
		$request = __Request::getAllAsArray();

		foreach ($request as $key => $value) {
			if(strpos($key,'filter_') === 0)
			{
				$k = str_replace('##',']',$key);
				$k = str_replace('#','[',$k);
				$this->filters[str_replace("filter_","",$k)] = $value;
			}
			if(strpos($key,'filterFrom_') === 0 || strpos($key,'filterTo_') === 0)
			{
				if($value == null) $value = '';
				$k = str_replace('##',']',$key);
				$k = str_replace('#','[',$k);
				$this->filters[str_replace(array("filterFrom_","filterTo_"),array("",""),$k)][] = $value;

				$a = $this->filters[str_replace(array("filterFrom_","filterTo_"),array("",""),$k)];
				if(sizeof($a) === 2)
				{
					if($a[0] === '' && $a[1] === '')
					{
						$this->filters[str_replace(array("filterFrom_","filterTo_"),array("",""),$k)] = null;
					}
				}
			}
		}
	}

	private function collectFieldList()
	{
		if($this->getAttribute('recordType') == 'sbn' || __Config::get('metafad.fe.search.hasCustomServices'))
		{
			$solrList = json_decode(file_get_contents(__Config::get($this->getAttribute('solrFieldUrl'))));
			$arrayRange = array();
			foreach ($solrList->fields as $f) {
				$arrayRange[] = $f->id;
			}
		}
		$this->fieldList = $arrayRange;
	}

	private function collectSearch()
	{
		$this->search = __Request::get($this->getAttribute('searchField'), '');
		$this->start = __Request::get('paginate_pageNum', 1) - 1;
	}

	private function buildSearchQuery()
	{
		$this->searchQuery = array();
		if($this->getAttribute('recordType') == 'sbn' || __Config::get('metafad.fe.search.hasCustomServices'))
		{
			$this->searchQuery['url'] = __Config::get($this->getAttribute('solrUrl'));
		}
		else
		{
			$this->searchQuery['url'] = __Config::get($this->getAttribute('solrUrl')).'select';
		}

		$this->searchQuery['action'] = '';
		$this->searchQuery['version'] = '2.2';
		$this->searchQuery['indent'] = 'on';
		$this->searchQuery['wt'] = 'json';
		$this->searchQuery['fl'] = $this->fields;
		$this->searchQuery['sort'] = $this->getAttribute('order');
		$this->searchQuery['q'] = $this->search;
		$this->searchQuery['facet'] = 'true';
		$this->searchQuery['facet.minimum'] = 1;
		$this->searchQuery['facet.limit'] = 200;
		$this->searchQuery['facet.field'] = $this->facetFields;
		$this->searchQuery['fq'] = array();

		foreach( $this->facets as $f )
		{
			$this->searchQuery['fq'][] = urlencode( str_replace('\"', '"', $f ));
		}

		if(__Request::get('query'))
		{
			foreach(json_decode(__Request::get('query'))->query->filters as $filterFromQuery)
			{
				$this->searchQuery['fq'][] = urlencode( str_replace('\"', '"', $filterFromQuery->field . ':"'.$filterFromQuery->values[0].'"'));
			}
		}
		else
		{
			if(__Request::get('rolesQuery'))
			{
				foreach(json_decode(__Request::get('rolesQuery'))->query->filters as $filterFromQuery)
				{
					$this->searchQuery['fq'][] = urlencode( str_replace('\"', '"', $filterFromQuery->field . ':"'.$filterFromQuery->values[0].'"'));
				}
			}
		}

		// add the pagination
		$this->addPaginateParams();
	}

	private function addPaginateParams()
	{
		$paginateClass = $this->getAttribute('paginate');
		if ($paginateClass) {
			$paginateClass->setRecordsCount();
			$limits = $paginateClass->getLimits();

			$this->searchQuery['start'] = $limits['start'];
			$this->searchQuery['rows'] = $limits['pageLength'];
		}
	}

	public function setFields($fields)
	{
		$this->fields = $fields;
	}

	public function setFieldsFromJson($fields)
	{
		$this->fields = json_decode($fields);
	}

	public function setFacetFields($fields)
	{
		$this->facetFields = $fields;
	}

	public function getFacetsFields()
	{
		return $this->facetFields;
	}

	public function setFacetFieldsJson($fields)
	{
		$this->facetFields = json_decode($fields);
	}

	public function getSearchUrl()
	{
		$url = array();
		$params = array_keys(__Request::getAllAsArray());
		foreach($params as $k) {
			if (strpos($k, '__')===0) continue;
			$v = __Request::get($k, null, PNX_REQUEST_GET);
			if ($v && !is_array($v)) {
				$url[$k] = $v;
			}
		}
		return '?'.http_build_query($url).'&';
	}

	public function getFacets()
	{
		return $this->facets;
	}

	public function getResponse()
	{
		return $this->solrResponse && property_exists($this->solrResponse, 'response') ? $this->solrResponse->response : null;
	}

	public function getResult()
	{
		return $this->solrResponse ? $this->solrResponse : null;
	}

	public function getSolrUrl()
	{
		return $this->solrUrl;
	}

	public function getSearchQuery()
	{
		return $this->searchQuery;
	}

	public function createFqFromDefaultQuery($query)
	{
		$searchQuery = $this->searchQuery;
		$data = $query['query'];
		$clauses = $data['clause']->clauses;
		if($clauses)
		{
			foreach ($clauses as $c) {
				foreach ($c->values as $value) {
					$value = $this->cleanValue($value);
					if($c->field == $this->getAttribute('generalField'))
					{
						if($value == '*')
						{
							$searchQuery['q'] = '*:*';
						}
						else
						{
							$value = explode(' ', $value);
							$appoggio = array();
							foreach ($value as $v) {
								$appoggio[] = '"' . urlencode(str_replace(',','',$v)) . '"';
							}
							$searchQuery['q'] = '(' . implode(' AND ', $appoggio) . ')';
						}
					}
					else
					{
						$operator = $c->operator['operator'];
						if($operator == '=')
						{
							$searchQuery['fq'][] = urlencode($c->field.':"'.str_replace("\"",'',$value).'"');
						}
						else if($operator == 'AND')
						{
							$value = explode(' ',$value);
							$appoggio = array();
							foreach($value as $v)
							{
								$appoggio[] = '*'.$v.'*';
							}
							$searchQuery['fq'][] = urlencode($c->field.':('.implode(' AND ', $appoggio ).')');
						}
						else if($operator == 'OR')
						{
							$orValues = explode(" ", $value);
							foreach ($orValues as $k => $v) {
								$orValues[$k] = '*'.$v.'*';
							}
							$searchQuery['fq'][] = urlencode($c->field.':('.implode(" OR ",$orValues).')');
						}
						else if($operator == 'NOT')
						{
							$searchQuery['fq'][] = urlencode('-'.$c->field.':*'.$value.'*');
						}
					}
				}
			}
		}
		if(__Request::get('sort'))
		{
			$sort = __Request::get('sort');
			$oldSort = __Session::get('sort');
			if($sort != $oldSort)
			{
				__Session::set('sort', $sort);
			}
			$searchQuery['sort'] = $sort . __Config::get('metafad.iccd.solr.sortingPrefix') . ' asc';
		}
		else if(__Session::get('sort'))
		{
			$searchQuery['sort'] = __Session::get('sort') . __Config::get('metafad.iccd.solr.sortingPrefix') . ' asc';
		}

		return $this->buildHttpQuery($searchQuery);
	}

	public function cleanValue($value)
	{
		return str_replace('/','',$value);
	}
}
