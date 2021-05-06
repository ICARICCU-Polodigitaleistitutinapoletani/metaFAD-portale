<?php
class metafad_modules_collections_views_components_NavigateCollections extends pinax_components_Component
{
	function init()
  {
      parent::init();
  }

  function process()
  {
		$instituteKey = __Session::get('instituteKey');
    $it = pinax_ObjectFactory::createModelIterator('metafad.institutes.models.Model');
		$count = 0;
		$institutes = array();
		foreach ($it as $ar) {
			if($instituteKey && $instituteKey != $ar->institute_key)
			{
				continue;
			}
			$institute = array();
			$institute['id'] = $ar->institute_id;
			$institute['name'] = $ar->institute_name;
			//Estraggo le prime 3 ( ?solo? ) collezioni del dato istituto
			$collections = pinax_ObjectFactory::createModelIterator('metafad.modules.collections.models.Model')
										 ->where('institute_index',$ar->institute_id);
			$appoggioC = array();
			$colCount = 0;
			foreach ($collections as $c) {
				$appoggioC[] = array('id' => $c->document_id, 'name' => $c->menuVoice);
				$colCount++;
				if($colCount == 3)
				{
					break;
				}
			}
			$institute['collections'] = $appoggioC;
			if(!empty($appoggioC))
			{
				$institutes[] = $institute;
				$count++;
				if($count == 3)
				{
					$this->_content['institutes'][] = $institutes;
					$institutes = array();
					$count = 0;
				}
			}
		}

		if($institutes)
		{
			$this->_content['institutes'][] = $institutes;
		}

  }
}
