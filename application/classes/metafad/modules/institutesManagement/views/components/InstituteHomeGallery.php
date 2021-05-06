<?php
class metafad_modules_institutesManagement_views_components_InstituteHomeGallery extends pinax_components_Component
{
	function init()
  {
    parent::init();
  }

  function process()
  {
		$instituteKey = __Session::get('instituteKey');
		if(!$instituteKey)
		{
			__Session::set('homeGallery', false);
			return;
		}

		$instituteProxy = __ObjectFactory::createObject('metafad_institutes_models_proxy_InstituteProxy');
		$institute = $instituteProxy->getInstituteConfigByKey($instituteKey);
		$imgArray = array();
		if($institute->homeGallery !== null)
		{			
			if($institute->homeGallery->image)
			{
				foreach($institute->homeGallery->image as $img)
				{
					$imgData = json_decode($img);
					$imgData->src = '/getImage.php?id='.$imgData->id.'&w=1200&h=800&.jpg';
					$imgArray[] = $imgData;
				}
				$this->_content = $imgArray;
				__Session::set('homeGallery', true);
			}
			else
			{
				__Session::set('homeGallery', false);
			}
		}
		else
		{
			__Session::set('homeGallery', false);
		}
  }

}
