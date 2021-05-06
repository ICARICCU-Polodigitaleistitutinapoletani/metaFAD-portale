<?php
/**
 * Class pinax_components_PaginateResult
 */
class metafad_views_components_PaginateResult extends pinax_components_PaginateResult
{
	var $pageUrl;
	var $pageLength;
    /** @var pinax_SessionEx $_sessionEx */
	var $_sessionEx		= NULL;
	private $paramName;

	function init()
	{
		$this->defineAttribute('showSortingFilters',	false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		parent::init();
	}

	function process()
	{
    	$this->_content['showSortingFilters']	= $this->getAttribute('showSortingFilters');
	}
}
