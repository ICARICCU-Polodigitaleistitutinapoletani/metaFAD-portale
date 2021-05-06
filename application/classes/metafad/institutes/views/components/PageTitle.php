<?php
class metafad_institutes_views_components_PageTitle extends pinax_components_PageTitle
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
			$this->setAttribute('cssClass','block-title gcp-text-weight-100 t-uppercase no-padding');
			$menuId 		= $this->getAttribute('menuId');
			$siteMap 		= &$this->_application->getSiteMap();
			$this->currentMenu 	= is_null($menuId) ? $this->_application->getCurrentMenu() : $siteMap->getNodeById($menuId);

			if (is_null($this->getAttribute('menuDepth')))
			{
				$this->_content = $this->currentMenu->title;
			}
			else
			{
				$this->currentMenu = &$this->currentMenu->parentNodeByDepth( $this->getAttribute('menuDepth') );
				$this->_content = $this->currentMenu->title;
			}
		}
		else{
			$instituteProxy = pinax_ObjectFactory::createObject('metafad.institutes.models.proxy.InstituteProxy');
			$institute = $instituteProxy->getInstituteVoByKey($instituteKey);
			$name = $institute->institute_name;

      $im = pinax_ObjectFactory::createModelIterator('metafad.modules.institutesManagement.models.Model');
      $ar = $im->where('institute_index',$institute->institute_id)->first();

      $image = ($ar->headerImage) ? str_replace("c=1","c=0",json_decode($ar->headerImage)->src) : null;

			$this->_content = '<div class="title-institute-section content clearfix">';
			if($image)
			{
				$this->_content .= '
				<div class="module-column mcp-module-header-sx bw-f institute-image-container">
		      <a href="'.__Link::makeUrl('link',array('pageId'=>1)).'">
		        <img class="institute-image" src="'.$image.'" />
		      </a>
		    </div>';
			}
			$this->_content .=
				'<div class="module-column mcp-module-header-sx bw-f institute-name-container">
		      	<div class="bcp-block-header-page-title padding-left">
							<a href="'.__Link::makeUrl('link',array('pageId'=>1)).'" class="institute_link">
								<h1 class="block-title gcp-text-weight-100 no-padding">'.$name.'</h1>
							</a>
						</div>
					</div>
				</div>';
		}
	}

}
