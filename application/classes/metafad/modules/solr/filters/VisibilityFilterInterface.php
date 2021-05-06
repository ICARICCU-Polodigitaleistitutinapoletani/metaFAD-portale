<?php
interface metafad_modules_solr_filters_VisibilityFilterInterface
{
	/**
     * @param string $data dati del record da filtrare
     */
	function applyFilters($data);
}
