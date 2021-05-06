<?php
class metafad_rest_controllers_Metasearch extends pinax_rest_core_CommandRest
{
	//Servizio per per ricerca su metaindice
	protected $fields = array();
	protected $facetFields = array();
	protected $facets;
	protected $filters = array();
	protected $search;
	protected $start;
	protected $fieldList = array();
	private $searchQuery = array();
	private $solrResponse;
	private $solrUrl = 'metafad.metaindice.url';
	private $queryBuilder = 'metafad.modules.solr.filters.DefaultQueryBuilder';
	private $order = 'score desc';
	private $resultPerPage;
	private $generalField = 'Tutto';
	private $recordType = 'metaindice';
	private $solrFieldUrl = 'metafad.metaindice.field.url';
	private $numFound;

	function execute()
	{
		$this->resultPerPage = __Request::get('resultPerPage',15);
		$this->collectFacets();
		$this->collectAdvancedFields();
		$this->collectFieldList();
		$this->collectSearch();

		if ($this->search) {
			$this->buildSearchQuery();
			$qb = pinax_ObjectFactory::createObject($this->queryBuilder);
			if (!$qb) {
				throw metafad_modules_solr_Exception::queryBuilderDontExists($this->queryBuilder);
			}
			$this->searchQuery = $qb->build($this->searchQuery, $this->search, $this->facets);
			// esegue la ricerca
			$this->doSearch();
			if($this->solrResponse)
			{
				return $this->prepareJson();
			}
			else
			{
				return null;
			}
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
		$temp = array_merge($searchQuery, array());
		$arrayOperator = array(
			'AND' => 'contains all',
			'OR' => 'contains one',
			'NOT' => 'contains one',
			'NOTALL' => 'contains all',
			'=' => 'contains one'
		);
		$query = array('query'=>array());

		//Se non ci sono filtri la ricerca è sul campo tutto
		if(!$this->filters)
		{
			$clause = new StdClass();
			$clause->type = 'SimpleClause';
			$clause->operator = array('operator' => 'AND');
			$clause->field = $this->generalField;
			$clause->innerOperator = array('operator' => 'contains one');
			$clause->values = array($this->search);
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

				$c->field = (in_array($key,$this->fieldList)) ? $key : str_replace('_',' ',$key);
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
				$clause = new StdClass();
				$clause->type = 'SimpleClause';
				$clause->operator = array('operator' => 'AND');
				$clause->field = $this->generalField;
				$clause->innerOperator = array('operator' => 'contains one');
				$clause->values = array($this->search);
			}
		}


		$query['query']['clause'] = $clause;
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

		$resultPerPage = $this->resultPerPage;

		$query['query']['start'] = 0 + ($resultPerPage * $this->start);
		$query['query']['rows'] = $resultPerPage;
		$query['query']['facetLimit'] = 200;
		$query['query']['facetMinimum'] = 1;
		$query['query']['facets'] = null;
		$query['query']['fq'] = null;
		$query['query']['fieldNamesAreNative'] = false;

		$arraySortingFields = array('metaindice' => 'Titolo ordinamento');

		$type = $this->recordType;

		return json_encode($query,JSON_UNESCAPED_UNICODE);
	}

	protected function doSearch()
	{
		$url = $this->searchQuery['url'].$this->searchQuery['action'];

		$postBody = $this->buildJsonSimpleQuery($this->searchQuery);

		$this->solrUrl = $url.'?'.$postBody;

		$request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url, 'POST', $postBody, 'application/json');
		$request->setAcceptType('application/json');
		$request->execute();

		$this->solrResponse = json_decode($request->getResponseBody());

		if ($this->solrResponse) {
			$this->numFound = $this->solrResponse->response->numFound;
		}
	}

	private function collectFacets()
	{
		$this->facets = __Request::get('facet', array());
		$temp = array();
		foreach( $this->facets as $f ) {
			$temp[] = str_replace('\"', '"', $f );
		}
		$temp = $this->checkAreaDigitale($temp);

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
		$solrList = json_decode(file_get_contents(__Config::get($this->solrFieldUrl)));
		$arrayRange = array();
		foreach ($solrList->fields as $f) {
			$arrayRange[] = $f->id;
		}
		$this->fieldList = $arrayRange;
	}

	private function collectSearch()
	{
		$this->search = __Request::get('search', '*');
		$this->start = __Request::get('start', 1) - 1;
	}

	private function buildSearchQuery()
	{
		$this->searchQuery = array();
		$this->searchQuery['url'] = __Config::get($this->solrUrl);
		$this->searchQuery['action'] = '';
		$this->searchQuery['version'] = '2.2';
		$this->searchQuery['indent'] = 'on';
		$this->searchQuery['wt'] = 'json';
		$this->searchQuery['fl'] = $this->fields;
		$this->searchQuery['sort'] = $this->order;
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

		$this->addPaginateParams();
	}

	private function prepareJson()
	{
		$json = array();
		$docs = $this->solrResponse->response->docs;
		foreach ($docs as $k => $v) {
			$doc = array();
			$id = $v->id;
			$visible = false;
			$n = $v->nodes[0]->nodes;
			$attributes = $v->attributes;

			foreach ($n as $key => $value) {
				if($value->label == 'dominio')
				{
					$dominio = $value->values[0];
				}
				$doc[$value->label] = $value->values;
			}
			if($attributes->Digitale_idpreview)
			{
				$doc['img'] = $attributes->Digitale_idpreview;
			}

			$arrayType = array('bibliografico'=>'opac','Patrimonio'=>'iccd','Archivi'=>'archive','archivi'=>'archive','patrimonio'=>'iccd');
			$thisType = $arrayType[$dominio];

			$docForJson = array();
			$docForJson['title'] = ($doc['denominazione/titolo']) ? implode(", ",$doc['denominazione/titolo']) : 'Senza titolo';
			$docForJson['img'] = $doc['img'];
			$docForJson['url'] = __Link::makeUrl($thisType.'_detail',array('id'=>$id));

			$json['docs'][] = $docForJson;
		}

		$json['numfound'] = $this->numFound;
		$json['pages'] = (int) ($this->numFound / $this->searchQuery['rows']) + 1;

		return $json;
	}

	private function addPaginateParams()
	{
		$this->searchQuery['start'] = __Request::get('start', 0);
		$this->searchQuery['rows'] = $this->resultPerPage;
	}

}
