<?php
class metafad_modules_institutesManagement_views_components_ServiceSection extends pinax_components_Component
{
	function init()
  {
    parent::init();
  }

  function process()
  {
		$instituteKey = __Session::get('instituteKey');
    $it = pinax_ObjectFactory::createModelIterator('metafad.modules.institutesManagement.models.Model')
					->orderBy('name','ASC');
		$consultation = array();
		$digitalization = array();
		$copies = array();
		$instituteProxy = pinax_ObjectFactory::createObject('metafad.institutes.models.proxy.InstituteProxy');

		foreach ($it as $ar) {
			$ins = $instituteProxy->getInstituteVoById($ar->institute_index)->institute_key;
			if($instituteKey)
			{
				if($ins != $instituteKey)
				{
					continue;
				}
			}
			if($ar->consultation === 'true')
			{
				$consultation[] = $this->getData($ar,$ins,'consultation');
			}
			if($ar->digitalization === 'true')
			{
				$digitalization[] = $this->getData($ar,$ins,'digitalization');
			}
			if($ar->copies === 'true')
			{
				$copies[] = $this->getData($ar,$ins,'copies');
			}
		}

		$this->_content['consultation'] = ($consultation) ? : null ;
		$this->_content['digitalization'] = ($digitalization) ? : null ;
		$this->_content['copies'] = ($copies) ? : null ;

  }

	public function getData($ar,$ins,$type)
	{
		return array('name'=>$ar->name,
								'image'=>str_replace('&w=150&h=150','',$ar->image),
								'instituteKey' => $ins,
								'type' =>$type
		);
	}

}
