<?php
class metafad_utils_SkinHelper extends PinaxObject {
	public static function checkDeniminazioneAut($value)
	{
		if(substr($value,0,1) == '[' && substr($value,-1) == ']')
		{
			return substr($value,1, strlen($value) - 2);
		}
		else
		{
			return $value;
		}
	}

	public static function checkForIndentationDiv($value)
	{
		$arrayCase = array('Estremi cronologici della relazione', 'Estremi cronologici di produzione', 'Estremi cronologici di conservazione');
		return in_array($value,$arrayCase);
	}

	public static function getSocialSiteProp()
	{
		$application = pinax_ObjectValues::get('org.pinax', 'application');
		$contentForSkin = array();
		$siteProp = $application->getSiteProperty();

		$socialProp = array();
		$socialProp['facebook'] = $siteProp['facebook'];
		$socialProp['twitter'] = $siteProp['twitter'];
		$socialProp['instagram'] = $siteProp['instagram'];
		$socialProp['youtube'] = $siteProp['youtube'];

		return $socialProp;
	}

	public static function getCollectionsHomeText()
	{
		$application = pinax_ObjectValues::get('org.pinax', 'application');
		$contentForSkin = array();
		$siteProp = $application->getSiteProperty();

		return $siteProp['collectionsHomeText'];
	}

	public static function getLogoUrl()
	{
		$application = pinax_ObjectValues::get('org.pinax', 'application');
		$contentForSkin = array();
		$siteProp = $application->getSiteProperty();

		return $siteProp['logoUrl'];
	}

	public static function rozSearchLink($roz)
	{
		return __Link::makeUrl('solr_search',null,array('search'=>'*',
													'filter_ROZ'=>$roz,
													'filter_ROZ_-words'=>'=')
												);
	}

	public static function rvelSearchLink($rvel)
	{
		$values = explode('#',$rvel);
		$nctr = $values[1];
		$nctn = $values[2];
		$link = __Link::makeUrl('solr_search',null,array('search'=>'*',
													'filter_NCTR'=>$nctr,
													'filter_NCTR-words'=>'=',
													'filter_NCTN'=>$nctn,
													'filter_NCTN-words'=>'='));
		return '<a href="'.$link.'" target="_blank">'.$values[0].'</a>';
	}

	public static function autDetailLink($aut)
	{
		$link = __Link::makeUrl('iccd_detail_au',array('id'=>$aut->id));
		return '<a href="'.$link.'" target="_blank">'.$aut->text.'</a>';
	}

	public static function archiveAutLink($aut)
	{
		$link = __Link::makeUrl('archive_detail_au',array('id'=>$aut->id));
		return '<a href="'.$link.'" target="_blank">'.$aut->text.'</a>';
	}

	public static function getSortingFields($type)
	{
		$fields = json_decode(__Config::get('metafad.'.$type.'.solr.sortingFields'));
		return $fields;
	}

	public static function getNCT($item)
	{
		$arr = get_object_vars($item);
		$nct = '';
		if($arr)
		{
			if($arr['NCTR_ss'])
			{
				$nct .= end($arr['NCTR_ss']);
			}

			if($arr['NCTN_ss'])
			{
				$nct .=  ' ' . end($arr['NCTN_ss']);
			}

			if ($arr['RVEL_ss']) {
				$nct .= ' ' . end($arr['RVEL_ss']);
			}
		}

		return $nct;
	}
}
