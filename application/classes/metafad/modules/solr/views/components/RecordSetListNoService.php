<?php
class metafad_modules_solr_views_components_RecordSetListNoService extends pinax_components_Component
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
    }

    protected function processInstitute($v)
    {
        $v->consultation = true;
        $v->digitalization = true;
        $v->copies = true;
    }

    function process() {
        $this->solrResponse = $this->dp->getResponse();

        if (is_object($this->solrResponse)) {
            $this->_content = new metafad_modules_solr_views_components_RecordSetListNoServiceVO();
			$this->collectConfigurationInfo();
            $this->_content->title = $this->getAttributeString('title');
            $this->_content->total = number_format($this->solrResponse->numFound, 0, '.', '.');
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
            foreach($this->solrResponse->docs as $d) {
                $this->processInstitute($d);
                $visibility = str_split($d->visibility_nxs[0]);
				if($visibility[0] == '')
				{
					$visibility = array('r','d','v');
				}
				foreach($visibility as $vis) {
                    $d->{'visibility_'.$vis} = true;
                }
				if($d->digitale_idpreview_s)
				{
					$image = metafad_modules_dam_Common::replaceUrlWithSize($d->digitale_idpreview_s, __Config::get('metafad.image.size.detail'));
					$d->digitale_idpreview_s = str_replace(__Config::get('url.be').'/rest',__Config::get('url.fe'),$image);
				}
            }

            $this->_content->records = new metafad_modules_solr_views_components_RecordSetListNoServiceIterator($this->_application, $this, $this->solrResponse->docs, $this->routeUrl, $this->getAttribute('cssClass'), $this->getAttribute('processCell'), $this->getAttribute('processCellParams'));
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


class metafad_modules_solr_views_components_RecordSetListNoServiceVO
{
    var $tile = '';
    var $records = NULL;
    var $total = 0;
    var $solrDebug = '';
    var $url = '';
    var $params = NULL;
}

class metafad_modules_solr_views_components_RecordSetListNoServiceIterator extends PinaxObject implements Iterator
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
        if (!$this->tempCssClass)
        {
            $this->tempCssClass = $this->cssClass;
        }

        if(is_string($this->tempCssClass)){
            $this->tempCssClass = [$this->tempCssClass];
        }
        $current->__cssClass__ = $this->tempCssClass ? array_shift( $this->tempCssClass ) : '';
        foreach( $this->routeUrl as $k => $v ) {
            $current->$k = __Link::makeURL( $v, (array)$current);
        }
        // if ($this->processCell) {
        //     call_user_func_array($this->processCell->renderCell, [$current, $this->processCellParams]);
        // }
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
