<?php
class metafad_archive_helpers_GetTreeHelper extends PinaxObject
{
	public function getNodeByRequest($id)
	{
		$request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', __Config::get('metafad.solr.url').'select', 'GET', 'q=id:"'.$id.'"&wt=json', 'application/x-www-form-urlencoded');
        $request->setAcceptType('application/json');
        $request->execute();

		$responseBody = json_decode($request->getResponseBody());
		if($responseBody->response->numFound == 1)
		{
			$doc = $responseBody->response->docs[0];
		}

		return $doc;
	}

	public function getNodesByParentId($parentId)
	{
		$request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', __Config::get('metafad.solr.url').'select', 'GET', 'q=parent_i:"'.$parentId.'"&sort=ordinamentoProvvisorio_i+asc&wt=json&rows=1000', 'application/x-www-form-urlencoded');
        $request->setAcceptType('application/json');
        $request->execute();

		$responseBody = json_decode($request->getResponseBody());
		if($responseBody->response->numFound >= 1)
		{
			$docs = $responseBody->response->docs;
		}
		return $docs;
	}

	public function getUrl($id,$nodeInfo)
    {
	  $doc_store = json_decode($nodeInfo->doc_store[0]);
      if($doc_store->className == 'archivi.models.ComplessoArchivistico')
      {
        $url = __Link::makeUrl('archive_detail',array('id'=>$id));
      }
      else
      {
        $url = __Link::makeUrl('archive_detail_ud',array('id'=>$id));
      }
      return $url;
    }

    public function getTitleExtra($nodeInfo)
    {
      $title = ($nodeInfo->denominazione_s) ?:'Titolo non conosciuto';
	  $cronologia = ($nodeInfo->cronologia_s && strpos($title,$nodeInfo->cronologia_s) === false) ? ' - ' . $nodeInfo->cronologia_s :'';
      return $title . $cronologia;
    }
}
