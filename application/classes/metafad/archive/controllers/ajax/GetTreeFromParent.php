<?php
class metafad_archive_controllers_ajax_GetTreeFromParent extends pinax_mvc_core_CommandAjax
{
	private $getTreeHelper;

	public function execute($id)
	{
		$iconClasses = (array)json_decode(__Config::get('metafad.archive.treeIcon'));
		$this->getTreeHelper = pinax_ObjectFactory::createObject('metafad.archive.helpers.GetTreeHelper');

		$children = $this->getTreeHelper->getNodesByParentId($id);

		$childrenArray = array();

		foreach($children as $c) {
			$docType = current($c->document_type_t);
			if($c->visibility_s === '0')
			{
				continue;
			}
			$children2 = $this->getTreeHelper->getNodesByParentId($c->id);
			$url = $this->getTreeHelper->getUrl($c->id,$c);

			$titleExtra = $this->getTreeHelper->getTitleExtra($c);

			$atLeastOneVisibileChild = false;
			if($children2)
			{
				foreach ($children2 as $c2) {
					if($c2->visibility_s !== '0')
					{
						$atLeastOneVisibileChild = true;
						break;
					}
				}
			}
			
			//ICONCLASS per POLODEBUG-386
			$iconclass = ($c->livelloDiDescrizione_s == 'unita-documentaria') ? 'fa fa-paperclip' : $iconClasses[$docType];

			$childrenArray[] = array(
				'id' => $c->id,
				'title' => $titleExtra,
				'folder' => $atLeastOneVisibileChild,
				'url' => $url,
				'lazy' => $atLeastOneVisibileChild, // lazy è true se il nodo ha figli
				'iconclass' => $iconclass
			);
		}

		$this->directOutput = true;
		return $childrenArray;
	}
}
