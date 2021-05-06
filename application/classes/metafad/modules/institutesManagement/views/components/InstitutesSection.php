<?php
class metafad_modules_institutesManagement_views_components_InstitutesSection extends pinax_components_Component
{
	function init()
  {
    parent::init();
  }

  function process()
  {
    $it = pinax_ObjectFactory::createModelIterator('metafad.modules.institutesManagement.models.Model')
					->orderBy('name','ASC');
		$institutes = array();
		foreach ($it as $ar) {
			$instituteKey = pinax_ObjectFactory::createModelIterator('metafad.institutes.models.Model')
	          ->where('institute_name',$ar->name)->first()->institute_key;
			$institutes[] = array(
												'name'=>$ar->name,
												'text'=>$ar->text,
												'longtext' => $ar->longtext,
												'link' => $ar->link,
												'instituteKey'=>$instituteKey, 
												'image'=>str_replace('&w=150&h=150','',$ar->image)
											);
		}
		$this->_content['institutes'] = $institutes;

  }

}
