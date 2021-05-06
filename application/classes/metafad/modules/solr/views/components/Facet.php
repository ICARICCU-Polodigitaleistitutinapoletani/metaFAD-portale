<?php
class metafad_modules_solr_views_components_Facet extends pinax_components_Component
{
    protected $facetFields = array();
    protected $dp;
	private $facets;
	private $facetParents;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('title',         false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('limit',         false,    -1,        COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('skipSelected',         false,    true,        COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('dataProvider', false, NULL, COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('routeUrl', false, 'solr', COMPONENT_TYPE_STRING);
        $this->defineAttribute('addParam', false, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('numFacetsToShow', false, 8, COMPONENT_TYPE_STRING);
		$this->defineAttribute('isSbn', false, false, COMPONENT_TYPE_BOOLEAN);

        parent::init();

        if (!$this->_application->isAdmin()) {
            $this->dp = $this->getAttribute('dataProvider');
            if (!($this->dp instanceof metafad_modules_solr_views_components_Solr)) {
                throw metafad_modules_solr_Exception::wrongDataProvider($this->getId());
            }

            // comunica al dataprovider i nomi delle faccette
            $this->dp->setFacetFields($this->facetFields);
        }
    }

    function process()
    {
        $limit = $this->getAttribute('limit');
        $routeUrl = $this->getAttribute('routeUrl');
        $skipSelected = $this->getAttribute('skipSelected');
        $addParam = $this->getAttribute('addParam');
        $this->_content             = array();
        $this->_content['title']     = $this->getAttributeString('title');
        $this->_content['selected'] = null;
        $this->_content['facetLabel']     = null;
        $this->_content['facet']     = null;
        $this->_content['emptyResult'] = true;

        $result = $this->dp->getResult();

		if(__Config::get('metafad.fe.search.hasCustomServices') || $this->getAttribute('isSbn'))
		{
        	$facet_fields = $result->response->facetsFields;
			if ($facet_fields) {
	            $addedFacet = array();
	            $queryUrl = $this->dp->getSearchUrl();
	            $facets = $this->dp->getFacets();
	            $queryUrl = preg_replace('/paginate_pageNum=\d*/','paginate_pageNum=1',$queryUrl);
	            if ($addParam && strpos($queryUrl, $addParam)===false) $queryUrl .= $addParam;

	            $this->_content['selected'] = array();
	            $this->_content['facetLabel'] = array();
	            $this->_content['facetNum'] = 0;

	            foreach ($facet_fields as $facet) {
	                $id = $facet->id;
	                $label = __T($facet->label);

	                if(__Request::get('action') === 'metaindice')
	                {
	                  $this->facets = __Request::get('facet', array());
	              		$temp = array();
	              		foreach( $this->facets as $f ) {
	              			$temp[] = str_replace('\"', '"', $f );
	              		}
	                  if(!in_array('dominio_s:"Archivistico"',$temp) && !in_array('dominio_s:"archivi"',$temp) && $id == 'complessodappartenenza_ss')
	                  {
	                    continue;
	                  }
	                }
	                //dd(__Request::get('facet'));
	                $this->_content['facetLabel'][] = array( 'id' => $id, 'title' => $label );
	                $this->_content['facet'][ $id ] = array();

	                foreach ($facet->values as $k => $v) {
	                    $value = $v->count;
	                    if ( $value > 0 && count( $this->_content['facet'][ $id ] ) < $this->getAttribute('numFacetsToShow') )
	                    {
	                        $name = $v->name;
	                        if (!$name) continue;
	                        $this->_content['facetNum']++;
	                        $facetToAdd = $id.':"'.$name.'"';
	                        //Rimuovi filtro
	                        if ( in_array( $facetToAdd, $facets ) )
	                        {
	                            $link = __Link::makeUrl(
	                                $routeUrl,
	                                array(
	                                    'title' => __T( 'Rimuovi filtro: ').$name,
	                                    'label' => $name.' ('.$value.')'
	                                ),
	                                $queryUrl.$this->createFacetUrl($facets, $facetToAdd, true)
	                            );

	                            $this->_content['selected'][] = array('link'=>$link,'label'=>$name,'fieldName'=>$label);
	                            if($label == 'Digitale')
	                            {
	                              $this->_content['digitalSelected'] = true;
	                            }
	                            if (!$skipSelected) {
	                                $this->_content['facet'][ $name ][] = $name.' ('.$value.')';
	                            }
	                        }
	                        //Aggiungi filtro
	                        else {
	                            $link = __Link::makeLink(
	                                $routeUrl,
	                                array(
	                                    'title' => __T( 'Aggiungi filtro: ').$name,
	                                    'label' => $name
	                                ),
	                                $queryUrl.$this->createFacetUrl($facets, $facetToAdd, false)
	                            );
	                            $this->_content['facet'][ $id ][] = array('link'=>$link,'count'=>'('.$value.')');
	                        }
	                    }
	                }
	            }
	        }
		}
		else
		{
			$facet_fields = $result->facet_counts->facet_fields;
			if($facet_fields)
			{
				$facetFields = $this->dp->getFacetsFields();
	            foreach($facetFields as $v) {
	                if (is_array($v) && $v['parent']) {
	                    $this->facetParents[$v['name']] = $v['parent'];
	                }
	            }

	            $addedFacet = array();

	            $facets = $this->dp->getFacets();
	            $queryUrl = $this->dp->getSearchUrl();
	            if ($addParam && strpos($queryUrl, $addParam)===false) $queryUrl .= $addParam;

				$queryUrl = preg_replace('/paginate_pageNum=\d*/', 'paginate_pageNum=1', $queryUrl);

	            $this->_content['selected'] = array();
	            $this->_content['facetLabel'] = array();
	            $this->_content['facetNum'] = 0;
	            $this->_content['emptyResult'] = false;

	            foreach ($facet_fields as $facetName => $facetValues) {
	                $label = __T($facetName);
	                $this->_content['facetLabel'][] = array( 'id' => $facetName, 'title' => $label );
	                $this->_content['facet'][ $facetName ] = array();

	                for ($i = 0; $i < count($facetValues); $i+=2) {
	                    $value = $facetValues[$i+1];

	                    if ( $value > 0 && count( $this->_content['facet'][ $facetName ] ) < $this->getAttribute('numFacetsToShow') )
	                    {
	                        $name = $facetValues[$i];
	                        if (is_array($name)) {
	                            // nel ccaso di faccette gerarchiche abbiamo due valori la label ed il valore dlela faccetta
	                            $nameValue = $name['value'];
	                            $name = $name['label'];
	                        } else {
	                            $nameValue = $name;
	                        }
	                        if (!$name) continue;
	                        $this->_content['facetNum']++;
	                        $facetToAdd = $facetName.':"'.$name.'"';
	                        if ( in_array( $facetToAdd, $facets ) )
	                        {
	                            $link = __Link::makeLink(
	                                $routeUrl,
	                                array(
	                                    'title' => __T( 'Rimuovi filtro: ').$name,
	                                    'label' => $label.': '.(($label == 'Digitale') ? 'tutti i documenti' : $name),
	                                ),
	                                $queryUrl.$this->createFacetUrl($facets, $facetToAdd, true),
	                                '',
	                                false
	                            );
	                            $this->_content['selected'][] = $link;

	                            if (!$skipSelected) {
	                                $this->_content['facet'][ $facetName ][] = $name.' <span class="counter">('.$value.')</span>';
	                            }
	                        } else {
	                            $link = __Link::makeLink(
	                                $routeUrl,
	                                array(
	                                    'title' => __T( 'Aggiungi filtro: ').$name,
	                                    'label' => $name.' <span class="counter">('.$value.')</span>'
	                                ),
	                                $queryUrl.$this->createFacetUrl($facets, $facetToAdd, false),
	                                '',
	                                false
	                            );
	                            $this->_content['facet'][ $facetName ][] = $link;
	                        }
	                    }
	                }
	            }
			}
		}

    }

    function render( $outputMode = NULL, $skipChilds = false )
    {
        if ( $outputMode != 'jsediting' )
        {
            parent::render();
        }
    }

    private function createFacetUrl($selectedFacet, $newFacet, $delete=false)
    {
        $params = array();
        if (!$delete) {
            $params['facet'] = array_merge(array(), $selectedFacet);
            $params['facet'][] = $newFacet;
        } else {
            $params['facet'] = array();
            foreach($selectedFacet as $v) {
                if ($v!=$newFacet) {
                    $params['facet'][] = $v;
                }
            }
        }
        return http_build_query($params);
    }

    public function addFacet( $name )
    {
        $this->facetFields[] = $name;
    }

    public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
    {
        $compiler->compile_baseTag( $node, $registredNameSpaces, $counter, $parent, $idPrefix, $componentClassInfo, $componentId );

        foreach ($node->childNodes as $n ) {
            if ( $n->nodeName == "solr:facet" ) {
                $name = $n->hasAttribute( 'name' ) ? $n->getAttribute( 'name' ) : '';
                if ($name ) {
                    $compiler->_classSource .= '$n'.$counter.'->addFacet( "'.$name.'" );';
                }
            } else {
                $oldcounter = $counter;
                $compiler->compileChildren($node, $registredNameSpaces, $counter, $oldcounter, $idPrefix );
            }
        }

        return false;
    }
}

if (!class_exists('metafad_modules_solr_views_components_Facet_render'))
{
    class metafad_modules_solr_views_components_Facet_render extends pinax_components_render_Render
    {
        function getDefaultSkin()
        {
            $skin = <<<EOD
<div class="solr solrFacet" tal:condition="php: !is_null(Component['facetLabel'])">
    <h3 tal:condition="not: Component/emptyResult" tal:content="structure Component/title" />
    <span tal:omit-tag="" tal:condition="php: Component['selected']">
        <h4 tal:content="php: __T('Filtri applicati:')"></h4>
        <ul class="selected">
            <li tal:repeat="item Component/selected" tal:content="structure item" />
        </ul>
    </span>
    <span tal:omit-tag="" tal:repeat="item Component/facetLabel">
        <span tal:omit-tag="" tal:condition="php: count( Component['facet'][item['id']] )">
            <h4 tal:content="structure item/title" />
            <ul class="toggle">
                <span tal:omit-tag="" tal:repeat="item2 php:Component['facet'][item['id']]">
                    <li tal:content="structure item2" tal:attributes="class php: (repeat.item2.number GT 5 ? 'more' : '')"/>
                    <li tal:condition="php: repeat.item2.number==5 AND repeat.item2.length GT 5" class="show-more-link"><a class="js-soltFacetOpening" href="#">Altri elementi</a></li>
                    <li tal:condition="php: repeat.item2.number==repeat.item2.length AND repeat.item2.length GT 5" class="show-less-link"><a class="js-soltFacetClosing" href="#">Meno elementi</a></li>
                </span>
            </ul>
        </span>
    </span>
<script type="text/javascript">
// <![CDATA[
$(function(){
    $('a.js-soltFacetOpening').click(function(e){
        e.preventDefault();
        $(this).closest('ul').addClass("show");
        return false;
    });

    $('a.js-soltFacetClosing').click(function(e){
        e.preventDefault();
        $(this).closest('ul').removeClass("show");
        return false;
    });
});
// ]]>
</script>
</div>
EOD;
            return $skin;
        }
    }
}
