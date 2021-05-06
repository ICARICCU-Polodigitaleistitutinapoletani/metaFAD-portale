<?php
class metafad_archive_views_components_Breadcrumbs  extends pinax_components_Component
{
	private $breadcrumbs;
	private $getTreeHelper;

	public function init()
	{
		$this->getTreeHelper = pinax_ObjectFactory::createObject('metafad.archive.helpers.GetTreeHelper');
		parent::init();
	}


	public function process() {
		$this->breadcrumbs = array();
		$id = __Request::get('id');

		$this->parentRecursion($id);

		$this->_content['breadcrumbs'] = array_reverse($this->breadcrumbs);
	}

	public function parentRecursion($id)
	{
		$data = $this->getTreeHelper->getNodeByRequest($id);
		$parentId = $data->parent_i;
		$titleExtra = $this->getTreeHelper->getTitleExtra($data);
		array_push($this->breadcrumbs,array('title'=>substr($titleExtra,0,40).' ...','url'=>$this->getTreeHelper->getUrl($id,$data),'tooltip'=>$titleExtra));
		if($parentId)
		{
			$this->parentRecursion($parentId);
		}
	}

}
