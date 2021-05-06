<?php
class metafad_modules_solr_controllers_Metaindice extends metafad_modules_solr_controllers_Index
{
	function execute()
	{
		//IL seguente codice è necessario per verificare che non si crei una situazione
		//in cui si inserisca la faccetta dei complessi senza aver indicato il dominio archivistico
		$facets = __Request::get('facet', array());
		$temp = array();
		foreach( $facets as $f ) {
			$temp[] = str_replace('\"', '"', $f );
			if(strpos($f,"complessodappartenenza_ss") !== false)
			{
				$ca = true;
			}
		}
		if(!in_array('dominio_s:"Archivistico"',$temp) && !in_array('dominio_s:"archivi"',$temp) && $ca)
		{
			//Resetto la ricerca su metaindice
			pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('solr_search_metaindice').'?search='.__Request::get('search','*'));

		}
		parent::execute();
	}
}
