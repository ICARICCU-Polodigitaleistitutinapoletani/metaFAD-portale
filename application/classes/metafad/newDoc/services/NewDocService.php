<?php
class metafad_newDoc_services_NewDocService extends PinaxObject
{
    public function getNewDocs($instituteKey)
    {
		$mapping = array('archivi'=>'archive','patrimonio'=>'iccd','bibliografico'=>'sbn');
        $instituteKeyParam = ($instituteKey) ? ' AND institutekey_s:'.$instituteKey: '' ;
        $request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', __Config::get('metafad.solr.metaindice.url').'select', 'POST', 'q=digitale_idpreview_s:*'.$instituteKeyParam.'&rows=36&wt=json&sort=timestamp+desc', 'application/x-www-form-urlencoded');
        $request->setAcceptType('application/json');
        $request->execute();

        $docs = json_decode($request->getResponseBody())->response->docs;

        $result = array();

        if (!$docs) {
            return $result;
        }

        $size = __Config::get('metafad.image.size.newDocs');
        foreach ($docs as $doc) {
            $obj = new stdClass;
            $obj->id = $doc->id;
            $obj->type = $mapping[$doc->dominio_t];
			$obj->titolo = pinax_strtrim($doc->title_t,100,' ...');
            $obj->descrizione = pinax_strtrim($doc->description_t,100,' ...');
            $obj->firstImage = metafad_modules_dam_Common::replaceUrlWithSize($doc->digitale_idpreview_t, $size);
            $obj->visibility = is_array($doc->visibility_nxs) ? implode($doc->visibility_nxs) : '';
			$result[] = $obj;
        }

        return $result;
    }
}
