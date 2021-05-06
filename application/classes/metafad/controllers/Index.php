<?php
class metafad_controllers_Index extends pinax_mvc_core_Command
{
	public function executeLater()
	{
		$menus = array('menuVoice1','menuVoice2','menuVoice3');
		$actionMapping = array('menuVoice1'=>'index','menuVoice2'=>'group','menuVoice3'=>'reserved');
		$active = array('menuVoice1'=>__Request::get('action') == 'index',
						'menuVoice2'=>__Request::get('action') == 'group',
						'menuVoice3'=>__Request::get('action') == 'reserved' || __Request::get('action') == 'requested');
		$menuVoices = array();
		foreach ($menus as $m) {
			$menuVoice = array('active'=>$active[$m],
							   'text'=>$this->view->getComponentById($m)->getContent(),
						   	   'url'=>__Link::makeUrl('linkChangeAction',array('action'=>$actionMapping[$m])));
			$menuVoices[] = $menuVoice;
		}
		$this->view->getComponentById('navigation')->setContent(array('menu'=>$menuVoices));
	}
}
