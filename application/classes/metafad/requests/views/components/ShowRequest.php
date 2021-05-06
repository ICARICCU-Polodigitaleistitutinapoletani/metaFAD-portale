<?php
class metafad_requests_views_components_ShowRequest extends pinax_components_Component
{
	function init()
	{
		parent::init();
	}

	function process()
	{
		$id = __Request::get('id');

		$request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', __Config::get('metafad.solr.metaindice.url').'select?q=id:'.strtoupper($id).'&wt=json','POST','','application/x-www-form-urlencoded');
		$request->setAcceptType('application/json');
		$request->execute();

		$doc = json_decode($request->getResponseBody())->response->docs[0];
		$institutes = $this->getInstitutes($doc);
		$image = $this->getImage($doc);

		$type = __Request::get('type');
		$typesArray = array('d' => 'consultazione della scheda di dettaglio', 'v' => 'visualizzazione','a' => 'acquisto','dig' => 'digitalizzazione');

		$this->_content['message'] = str_replace("##what##",__T($typesArray[$type]),__T('request_message'));
		$this->_content['otherMessage'] = ($type == 'a') ? __T('buy_request_message') : __T('note_message');
		$this->_content['info'] = $this->getInfoFromMetaindex($id);
		$this->_content['id'] = $id;
		$this->_content['institutes'] = $institutes;
		$this->_content['type'] = $type;
		$this->_content['image'] = $image;
	}

	public function getImage($doc)
	{
		if($doc->digitale_idpreview_s)
		{
			return (is_array($doc->digitale_idpreview_s)) ? $doc->digitale_idpreview_s[0] : $doc->digitale_idpreview_s;
		}
		else
		{
			return null;
		}
	}

	public function getInstitutes($doc)
	{
		$instituteProxy = pinax_ObjectFactory::createObject('metafad.institutes.models.proxy.InstituteProxy');
		$institutes = array();

		if(is_numeric($doc->id)) {
			$instituteKey = (is_array($doc->institutekey_s[0])) ? $doc->institutekey_s[0] : $doc->institutekey_s;
			$i = $instituteProxy->getInstituteVoByKey($instituteKey);
			$institutes[] = array('key'=>$instituteKey,'value'=>$i->institute_name);
		}
		else {
			$localizzazione = $doc->localizzazione_ss;
			if(is_array($localizzazione))
			{
				foreach ($localizzazione as $l) {
					$i = $instituteProxy->getInstituteVoByName($l);
					$institutes[] = array('key'=>$i->institute_key,'value'=>$l);
				}
			}
		}
		return $institutes;
	}

	public function getInfoFromMetaindex($id)
	{
		$request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', __Config::get('metafad.metaindice.detail.url'), 'POST', 'id='.strtoupper($id), 'application/x-www-form-urlencoded');
		$request->setAcceptType('application/json');
		$request->execute();

		$doc = json_decode($request->getResponseBody())->response->docs[0];
		$html = '';
		$title = '';
		if(!$doc)
		{
			return $html;
		}
		foreach ($doc->nodes[0]->nodes as $key => $value) {
			if($value->id == 'denominazione/titolo')
			{
				$title .= '<h1 class="block-title">'.implode(" | ",$value->values).'</h1>';
			}
			else {
				$html .= '<p class="value"><span class="label">'.__T($value->id).'</span>';
				foreach ($value->values as $v) {
					$html .= $v.'<br>';
				}
				$html .= '</p>';
			}
		}

		return $title.$html;
	}
}
