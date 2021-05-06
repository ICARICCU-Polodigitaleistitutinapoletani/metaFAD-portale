<?php
class metafad_modules_solr_views_components_RecordDetail extends pinax_components_Component
{

    function init()
    {
		$this->defineAttribute('processCell',     false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('solrUrl', false, 'metafad.solr.detail.url', COMPONENT_TYPE_STRING);
		$this->defineAttribute('mediaType', false, 'iccd', COMPONENT_TYPE_STRING);
		$this->defineAttribute('titleField', false, 'title_s', COMPONENT_TYPE_STRING);
        parent::init();
    }

    function process()
    {
        $id = __Request::get('id');
		$mediaType = $this->getAttribute('mediaType');
		$titleField = $this->getAttribute('titleField');
        $url = __Config::get($this->getAttribute('solrUrl'));

        $request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest',
															$url.'select',
															'POST',
															'q=id:'.$id.'&wt=json',
															'application/x-www-form-urlencoded');
        $request->setAcceptType('application/json');
        $request->execute();

        $response = json_decode($request->getResponseBody())->response;
		if($response)
		{
        	$content = $response->docs[0];
			$processCell = pinax_ObjectFactory::createObject($this->getAttribute('processCell'), $this->_application);
	        if ($processCell) {
	            call_user_func_array(array($processCell, 'renderCell'), array(&$content));
	        }

			$this->_content['data'] = json_decode($content->data_s);
			if($content->$titleField)
			{
				$this->_content['title'] = $content->$titleField;
			}
			$firstImageUrl = str_replace(__Config::get('url.be').'/rest',__Config::get('url.fe'),$content->digitale_idpreview_s);
			$this->_content['firstImage'] = metafad_modules_dam_Common::replaceUrlWithSize($firstImageUrl, __Config::get('metafad.image.size.detail'));
			$this->_content['imageUrl'] = __Link::makeUrl('link', array('pageId'=> 'Viewer' )) . '#/main/viewer?idMetadato=' . $id . '&type='.$mediaType;
		}

    }
}
