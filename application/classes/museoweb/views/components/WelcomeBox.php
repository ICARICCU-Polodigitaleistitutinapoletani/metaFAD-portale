<?php
class museoweb_views_components_WelcomeBox extends pinax_components_ListItem
{
	public function init()
	{
		parent::init();
	}

  function getItem()
	{
    $currentUserName = $this->_application->getCurrentUser()->firstName;

		$key 	= !is_null($this->getAttribute('key')) ? $this->getAttribute('key') : $this->getAttribute('value');
		$value	= $this->getAttribute('value');
		$options = $this->getAttribute('options');

		return array('key' => $key, 'value' => $value . ' ' . $currentUserName, 'selected' => $this->getAttribute('selected'), 'options' => $options, 'cssClass' =>  $this->getAttribute('cssClass') );
	}
}
