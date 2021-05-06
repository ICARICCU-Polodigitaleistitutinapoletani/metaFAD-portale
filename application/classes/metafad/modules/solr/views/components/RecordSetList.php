<?php
class metafad_modules_solr_views_components_RecordSetList extends pinax_components_Component
{
    protected $routeUrl = array();
    protected $fields = array();
    protected $dp;
    protected $solrResponse;
    protected $institutes = array();

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('title', false, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('routeUrl', false, NULL, COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClass', false, NULL, COMPONENT_TYPE_STRING);
        $this->defineAttribute('processCell', false, NULL, COMPONENT_TYPE_STRING);
        $this->defineAttribute('processCellParams',    false,    NULL,        COMPONENT_TYPE_STRING);
        $this->defineAttribute('dataProvider', false, NULL, COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('params', false, NULL, COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('recordType', false, 'sbn', COMPONENT_TYPE_STRING);
		$this->defineAttribute('multipleConcatString', false, ', ', COMPONENT_TYPE_STRING);

        parent::init();

        if (!$this->_application->isAdmin()) {
            $this->dp = $this->getAttribute('dataProvider');
            if (!($this->dp instanceof metafad_modules_solr_views_components_Solr)) {
                throw metafad_modules_solr_Exception::wrongDataProvider($this->getId());
            }

            // comunica al dataprovider i nomi dei campi
            $this->dp->setFields($this->fields);
        }

        $instituteProxy = pinax_ObjectFactory::createObject('metafad.institutes.models.proxy.InstituteProxy');
        $it = pinax_ObjectFactory::createModelIterator('metafad.modules.institutesManagement.models.Model');

        foreach ($it as $ar) {
            $instituteKey = $instituteProxy->getInstituteVoById($ar->institute_index)->institute_key;
            $this->institutes[$instituteKey] = array(
                'consultation' => $ar->consultation == 'true',
                'digitalization' => $ar->digitalization == 'true',
                'copies' => $ar->copies == 'true'
            );
        }
    }

    protected function processInstitute($v)
    {
        $actions = $this->institutes[$v->attributes->InstituteKey];
        $v->consultation = $actions['consultation'];
        $v->digitalization = $actions['digitalization'];
        $v->copies = $actions['copies'];
    }

    function process() {
        $this->solrResponse = $this->dp->getResponse();

        if (is_object($this->solrResponse)) {
            $this->_content = new metafad_modules_solr_views_components_RecordSetListVO();
			$this->collectConfigurationInfo();
            $this->_content->title = $this->getAttributeString('title');
            $this->_content->total = number_format($this->solrResponse->numFound, 0, '.', '.');
            $this->_content->solrDebug = $this->dp->getSolrUrl();
            $this->_content->url = __Link::removeParams();
            $this->_content->urlRelevance = __Link::addParams(array('order' => 'relevance'));
            $this->_content->urlMostRecent = __Link::addParams(array('order' => 'mostRecent'));
            $this->_content->urlLeastRecent = __Link::addParams(array('order' => 'leastRecent'));
            $params = $this->getAttribute('params');
            if (is_string($params)) {
                $temp = @json_decode($params);
                $params = $temp ? : $params;
            }
            $this->_content->params = $params;

            //Questo ciclo permette di estrarre i valori dei campi dalla risposta solr

            foreach($this->solrResponse->docs as $k => $v) {
                $visible = false;
                $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
                if ($this->getAttribute('recordType') == 'metaindice') {
                    $n = $v->nodes[0]->nodes;
                    foreach($n as $key => $value) {
                        if ($value->label == 'dominio') {
                            $dominio = $value->values[0];
                            break;
                        }
                    }
                    $arrayType = array('bibliografico' => 'sbn', 'Patrimonio' => 'iccd', 'Archivi' => 'archive', 'archivi' => 'archive', 'patrimonio' => 'iccd');
                    $thisType = $arrayType[$dominio];
                } else {
                    $thisType = $this->getAttribute('recordType');
                }

                $attributes = $v->attributes;
                if ($thisType != 'authority') {
                    if ($attributes->Digitale_idpreview && strpos($attributes->Digitale_idpreview, 'http://') === 0) {
                        $v->size = 1;
                        $v->firstImage = $attributes->Digitale_idpreview;
                    }
					else {
                        $v->firstImage = null;
                    }
                }

                foreach($v->nodes as $node) {
                    foreach($node->nodes as $n) {
                        if (in_array($n->label, $this->fields) || in_array($n->id, $this->fields)) {
                            if (sizeof($n->values) == 1) {
                                $v->{$n->label} = $n->values[0];
                            }
                            else {
                                if ($n->label == 'denominazione/titolo') {
                                    $v->{'denominazione/titolo'} = $n->values[0];
                                }
                                else {
                                    $string = '';
                                    foreach($n->values as $val) {
                                        $string .= $val.$this->getAttribute('multipleConcatString');
                                        if ($n->label == 'Definizione culturale: autore') {
                                            break;
                                        }
                                    }
                                    $v->{$n->label} = rtrim($string, $this->getAttribute('multipleConcatString'));
                                }
                            }
                        }
                    }
                }

                //Ottengo valore Ecommerce e Visibility
                if ($attributes->Ecommerce) {
                    $v->Ecommerce = json_decode($attributes->Ecommerce);
                }
                $visibilityValues = ($attributes->Visibility) ? str_split($attributes->Visibility) : null;
                $visibility = (!empty($visibilityValues)) ? $visibilityValues : str_split('rdv');
                foreach($visibility as $vis) {
                    $v->{'visibility_'.$vis} = true;
                }
                $this->processInstitute($v);
            }
            $this->_content->records = new metafad_modules_solr_views_components_RecordSetListIterator($this->_application, $this, $this->solrResponse->docs, $this->routeUrl, $this->getAttribute('cssClass'), $this->getAttribute('processCell'), $this->getAttribute('processCellParams'));
        }
        else {
            $this->setAttribute('visible', false);
        }
    }

    public  function addRoute($mapTo, $routUrl)
    {
        $this->routeUrl[$mapTo] = $routUrl;
    }

    public function addField($name)
    {
        $this->fields[] = $name;
    }

    public static  function compile($compiler, & $node, & $registredNameSpaces, & $counter, $parent = 'NULL', $idPrefix, $componentClassInfo, $componentId)
    {
        $compiler->compile_baseTag($node, $registredNameSpaces, $counter, $parent, $idPrefix, $componentClassInfo, $componentId);

        $routeUrl = $node->hasAttribute('routeUrl') ? $node->getAttribute('routeUrl') : '';
        if ($routeUrl) $compiler->_classSource .= '$n'.$counter.'->addRoute( "__url__", "'.$routeUrl.'" );';
        foreach($node->childNodes as $n) {
            if ($n->nodeName == "pnx:routeUrl") {
                $mapTo = $n->hasAttribute('mapTo') ? $n->getAttribute('mapTo') : '';
                $name = $n->hasAttribute('name') ? $n->getAttribute('name') : '';
                if ($mapTo && $name) {
                    $compiler->_classSource .= '$n'.$counter.'->addRoute( "'.$mapTo.'", "'.$name.'" );';
                }

            } else if ($n->nodeName == "solr:field") {
                $name = $n->hasAttribute('name') ? $n->getAttribute('name') : '';
                if ($name) {
                    $compiler->_classSource .= '$n'.$counter.'->addField( "'.$name.'" );';
                }

            } else {
                $oldcounter = $counter;
                $compiler->compileChildren($node, $registredNameSpaces, $counter, $oldcounter, $idPrefix);
            }
        }

        return false;
    }

	private function collectConfigurationInfo()
	{
		$this->_content->hasRequests = __Config::get('metafad.fe.hasRequests');
		$this->_content->hasSocial = __Config::get('metafad.fe.hasSocial');
	}
}


class metafad_modules_solr_views_components_RecordSetListVO
{
    var $tile = '';
    var $records = NULL;
    var $total = 0;
    var $solrDebug = '';
    var $url = '';
    var $params = NULL;
}

class metafad_modules_solr_views_components_RecordSetListIterator extends PinaxObject implements Iterator
{
    private $parent;
    private $records;
    private $routeUrl;
    private $cssClass;
    private $processCell;
    private $pos = 0;
    private $tempCssClass = array();
    private $processCellParams;

    function __construct( $application, $parent, $records, $routeUrl, $cssClass, $processCell=null, $processCellParams=null)
    {
        $this->parent = $parent;
        $this->records = $records;
        $this->routeUrl = $routeUrl;
        $this->cssClass = $cssClass;
        $this->processCellParams = $processCellParams;
        if ($processCell) {
            $this->processCell = pinax_ObjectFactory::createObject($processCell, $application);
        }
    }

    function current()
    {
        //Personalizzazione per estrazione campi
        $current = $this->records[$this->pos];
        if (!count( $this->tempCssClass ) )
        {
            $this->tempCssClass = $this->cssClass;
        }

        $current->__cssClass__ = count( $this->tempCssClass ) ? array_shift( $this->tempCssClass ) : '';
        foreach( $this->routeUrl as $k => $v ) {
            $current->$k = __Link::makeURL( $v, (array)$current);
        }
        if ($this->processCell) {
            call_user_func_array(array($this->processCell, 'renderCell'), array($current, $this->processCellParams));
        }
        return $current;
    }

    function key()
    {
        return $this->pos;
    }

    function next()
    {
        $this->pos++;
    }

    function rewind()
    {
        $this->pos = 0;
    }

    function valid()
    {
        return $this->pos < count($this->records);
    }

    function count()
    {
        return count($this->records);
    }
}
