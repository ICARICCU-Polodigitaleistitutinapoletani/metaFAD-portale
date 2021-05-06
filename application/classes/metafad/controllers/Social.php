<?php
class metafad_controllers_Social extends pinax_mvc_core_Command
{
	public function execute()
	{
		$this->view->getComponentById('socialFooter')->setContent(array('url'=>urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]")));
	}
}
