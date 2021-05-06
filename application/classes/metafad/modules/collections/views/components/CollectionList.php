<?php
class metafad_modules_collections_views_components_CollectionList extends pinax_components_Component
{
	function init()
  {
		$this->defineAttribute('home',  false, 'false', COMPONENT_TYPE_STRING);
		$this->defineAttribute('limit',  false, 9, COMPONENT_TYPE_INTEGER);
    parent::init();
  }

  function process()
  {
		$instituteKey = __Session::get('instituteKey');

    $it = pinax_ObjectFactory::createModelIterator('metafad.modules.collections.models.Model')
					->orderBy('position','ASC')
          ->orderBy('title','ASC');
    $this->_content['firstCollections'] = array();
    $this->_content['subCollections'] = array();
    $this->_content['allCollections'] = array();
    $tris = array();
		$limit = 0;
    $count = 0;
    foreach ($it as $ar) {
			if($instituteKey)
			{
				$instituteProxy = pinax_ObjectFactory::createObject('metafad_institutes_models_proxy_InstituteProxy');
		    $institute = $instituteProxy->getInstituteVoById($ar->institute->id);
		    if($instituteKey !== $institute->institute_key)
				{
					continue;
        }
        else
        {
          $instituteIndex = $ar->institute->id;
        }
			}

      $tris[] = array('id'=>$ar->document_id,'title'=>$ar->title,'description'=>substr($ar->text,0,100).'...','image'=>str_replace("w=150&h=150&c=1","w=300&h=300&c=0",json_decode($ar->image)->src),'imageThumbnail'=>json_decode($ar->image)->src);
      $this->_content['allCollections'][] = array('id'=>$ar->document_id,'title'=>$ar->menuVoice);
      $count++;
			$limit++;
      if($count == 3)
      {

        $this->_content['firstCollections'][] = $tris;
        $this->_content['subCollections'][] = $tris;
        $tris = array();
        $count = 0;
      }
			if($this->getAttribute('home') === 'true' && $limit >= $this->getAttribute('limit'))
			{
				break;
			}
    }

    if(!empty($tris))
    {
			$this->_content['firstCollections'][] = $tris;
      $this->_content['subCollections'][] = $tris;
    }

		//var_dump(json_encode($this->_content)); exit;

    if(!$instituteKey)
    {
		  $it = pinax_ObjectFactory::createModelIterator('metafad.modules.collections.models.Model')
        ->orderBy('position','ASC')->first();
    }
    else
    {
      $it = pinax_ObjectFactory::createModelIterator('metafad.modules.collections.models.Model')
        ->where('institute_index', $instituteIndex)
        ->orderBy('position', 'ASC')
        ->first();
    }
		$this->_content['firstCollectionLink'] = __Link::makeUrl('collections') . '/' .$it->document_id .'/';
  }

}
