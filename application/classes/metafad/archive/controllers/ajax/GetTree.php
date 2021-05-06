<?php
class metafad_archive_controllers_ajax_GetTree extends pinax_mvc_core_CommandAjax
{
	private $id;
	private $getTreeHelper;

	private $iconClasses;

	public function execute($id = null)
	{
		$this->iconClasses = (array)json_decode(__Config::get('metafad.archive.treeIcon'));

		$this->getTreeHelper = pinax_ObjectFactory::createObject('metafad.archive.helpers.GetTreeHelper');
		$this->id = $id;
		$this->directOutput = true;
		$tree = array();

		if ($id) {
			$tree[] = $this->loadTree($id, new StdClass());
		}
		else {
			$node = new StdClass();
			$node->id = (int) $id;
			$node->active = true;
			$node->title = '(Non noto): id = ';
			$node->expanded = true;
			$node->children = array();

			$tree[] = $node;
		}
		return $tree;
	}

    public function loadTree($id, $subTree)
    {
        $nodeInfo = $this->getTreeHelper->getNodeByRequest($id);
        $activeId = $id;

        if ($nodeInfo->parent_i)
		{
			$pid = $nodeInfo->parent_i;
          	$nodeInfo = $this->getTreeHelper->getNodeByRequest($nodeInfo->parent_i);
		}

		if($nodeInfo->visibility_s === '0')
		{
			return null;
		}

        $id = ($pid) ? $pid : $id;

        $url = $this->getTreeHelper->getUrl($id, $nodeInfo);
        $titleExtra = $this->getTreeHelper->getTitleExtra($nodeInfo);

        $tree = new StdClass();
        $tree->id = (int) $id;
        $tree->title = $titleExtra;
        $tree->url = $url;
        $tree->expanded = true;
        if($tree->id == $activeId)
        {
          $tree->active = true;
        }
        $tree->children = array();
		$tree->iconclass = ($nodeInfo->livelloDiDescrizione_s == 'unita-documentaria') ? 'fa fa-paperclip' : $this->iconClasses[current($nodeInfo->document_type_t)];

        $children = $this->getTreeHelper->getNodesByParentId($id);

		if($children)
		{
	        foreach ($children as $c)
			{
				$docType = current($c->document_type_t);
	            if($c->visibility_s === '0')
	            {
	              continue;
	            }
	            if ($c->id == $subTree->id) {
	                $child = $subTree;
	            } else {
	                $child = new StdClass();
	                $child->id = $c->id;
	                $url = $this->getTreeHelper->getUrl($c->id,$c);

	                $titleExtra = $this->getTreeHelper->getTitleExtra($c);

					$children2 = $this->getTreeHelper->getNodesByParentId($c->id);

	                $atLeastOneVisibileChild = false;
					if($children2)
					{
		                foreach ($children2 as $c2) {
		                  if($c2->visibility !== '0')
		                  {
		                    $atLeastOneVisibileChild = true;
		                    break;
		                  }
		                }
					}
	                if($atLeastOneVisibileChild)
	                {
					  $child->folder = true;
	                  $child->lazy = true;
	                }
	                if($c->id == $activeId)
	                {
	                  $child->active = true;
	                }
	                $child->title = $titleExtra;
					$child->url = $url;
					
					//ICONCLASS per POLODEBUG-386
					$child->iconclass = ($c->livelloDiDescrizione_s == 'unita-documentaria') ? 'fa fa-paperclip' : $this->iconClasses[$docType];
	            }

	            $tree->children[] = $child;
	        }
		}
        $tree->folder = !empty($tree->children);

		return $tree;
    }


}
