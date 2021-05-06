<?php
class metafad_modules_solr_views_components_Detail extends pinax_components_Component
{

	function init()
	{
		$this->defineAttribute('solrUrl', false, 'metafad.solr.detail.url', COMPONENT_TYPE_STRING);
		$this->defineAttribute('mediaType', false, 'sbn', COMPONENT_TYPE_STRING);
		$this->defineAttribute('detailType', false, 'sbn', COMPONENT_TYPE_STRING);
		$this->defineAttribute('unit', false, false, COMPONENT_TYPE_BOOLEAN);
		parent::init();
	}

	function process()
	{
		$id = strtoupper(__Request::get('id'));
		$url = __Config::get($this->getAttribute('solrUrl'));
		$mediaType = $this->getAttribute('mediaType');
		$detailType = $this->getAttribute('detailType');
		$unit = $this->getAttribute('unit');

		if($unit)
		{
			$getTreeHelper = pinax_ObjectFactory::createObject('metafad.archive.helpers.GetTreeHelper');
			$nodeInfo = $getTreeHelper->getNodeByRequest($id);
			if($nodeInfo->document_type_t == 'archivi.models.UnitaArchivistica')
			{
				$url = __Config::get('metafad.archive-ua.detail.url');
			}

		}

		$content = json_decode(file_get_contents($url.'?id='.$id));

		$attributes = $content->response->docs[0]->attributes;

		$this->_content['firstImage'] = null;
		if($attributes)
		{
			$firstImage = $attributes->Digitale_idpreview;
			if($firstImage)
			{
				$this->_content['firstImage'] = metafad_modules_dam_Common::replaceUrlWithSize($firstImage, __Config::get('metafad.image.size.detail'));
				$this->_content['imageUrl'] = __Link::makeUrl('link', array('pageId'=> 'Viewer' )) . '#/main/viewer?idMetadato=' . $id . '&type='.$mediaType;
			}
		}

		$visibilityValues = ($attributes->Visibility) ? str_split($attributes->Visibility) : null;
		$visibility = (!empty($visibilityValues)) ? $visibilityValues : str_split('rdv');
		foreach ($visibility as $vis) {
			$this->_content['visibility_'.$vis] = true;
		}
		$nodes = $content->response->docs[0]->nodes[0]->nodes;
		$arrayToHide = array('tabSoggettoProduttore_linguaDescrizioneRecordProduttore',
		'tabSoggettoProduttore_compilazioneProduttore',
		'tabSoggettoConservatore_linguaDescrizioneRecordConvervatore',
		'tabSoggettoConservatore_compilazioneConservatore',
		'tabCompilazione_compilazione',
		'tabCompilazione_linguaDescrizioneRecord',
		//  'tabIdentificazione_tipo',
		//  'tabIdentificazione_parent'
	);
	$fieldToHide = array('Livello superiore','Tipologia','Supporto','Consistenza');

	if(__Request::get('filterFields'))
	{
		$filterFields = __Request::get('filterFields');
		//POLODEBUG-497, nascondo tab in base al link con cui ho aperto la modale
		$tabForModalEntity = array('soggettoProduttore'=>array('Identificazione','Soggetto produttore'),
		'soggettoConservatore'=>array('Identificazione','Soggetto conservatore'),
		'autoreResponsabile'=>array('Identificazione'));
		$count = 0;
		foreach ($nodes as $value) {
			if(!in_array($value->label,$tabForModalEntity[$filterFields]))
			{
				unset($nodes[$count]);
			}
			$count++;
		}
	}


	$values = array();
	$parents = array();
	$hideLabel = array();

	if($nodes)
	{
		foreach ($nodes as $value) {
			$arrayAppoggio = array();
			$discard = false;
			foreach ($value->nodes as $v) {

				if(in_array($v->id,$arrayToHide) && $detailType == 'archive')
				{
					$discard = true;
					continue;
				}

				if(in_array($v->label,$fieldToHide) && $detailType == 'archive')
				{
					continue;
				}
				$specificParents = array('tabIdentificazione_cronologia'=>'Estremi cronologici');
				if($v->parentLabel)
				{
					$prevParent = $v->parentLabel;
					if(!in_array($v->parentLabel,$parents))
					{
						$parents[$v->label] = $v->parentLabel;
					}
				}
				else if(array_key_exists($v->id,$specificParents))
				{
					$prevParent = $specificParents[$v->id];
					if(!in_array($specificParents[$v->id],$parents))
					{
						$parents[$v->label] = $specificParents[$v->id];
					}
				}
				else
				{
					if($prevParent)
					{
						$parents[$v->label] = false;
					}
					$prevParent = $v->parentLabel;
				}
				if($v->hideLabel)
				{
					$hideLabel[$v->label] = true;
				}
				if($v->id == 'linkediccd')
				{
					if($mediaType == 'sbn')
					{
						$this->_content['linkedIccd'] = __Link::makeUrl('opac_detail',array('action'=>'detail','id'=>$v->values[0]));
						$this->_content['linkLabel'] = 'Vedi scheda ICCD collegata';
					}
					else
					{
						$this->_content['linkedIccd'] = __Link::makeUrl('iccd_detail',array('action'=>'detail','id'=>$v->values[0]));
						$this->_content['linkLabel'] = 'Vedi scheda SBN collegata';
					}
					continue;
				}
				if($v->label == 'Titolo')
				{
					$this->_content['title'] = $this->checkDates($v->values[0]);
					if(__Request::get('action') == 'detailarchiveaut')
					{
						continue;
					}
				}

				if(sizeof($v->values) === 1)
				{
					$arrayAppoggio[$v->label] = $this->checkLinks($v->values[0]);
				}
				else {
					if(sizeof($v->values) > 1)
					{
							$html = array();;
							foreach ($v->values as $h) {
								$html[] = $this->checkLinks($h);
							}
							$arrayAppoggio[$v->label] = $html;
					}
					else
					{
							$html = '';
							foreach ($v->values as $h) {
								$html .= $this->checkLinks($h);
							}
							$arrayAppoggio[$v->label] = $html;
					}
				}
			}
			if(!$discard)
			{
				if(array_key_exists($value->label,$values))
				{
					$values[$value->label.' '] = $arrayAppoggio;
				}
				else
				{
					$values[$value->label] = $arrayAppoggio;
				}
			}
		}
	}

	if(__Request::get('action') == 'detailarchiveaut')
	{
		//Fix per aggiunta a servizio di campo ambiguo titolo (solo per entità)
		$values['Identificazione']['Titolo'] = ($values['Identificazione']['Titolo entità']) ? $values['Identificazione']['Titolo entità']: $values['Identificazione ']['Titolo entità'];
		unset($values['Identificazione ']);
		unset($values['Identificazione']['Titolo entità']);
	}

	$this->_content['parents'] = $parents;
	$this->_content['hideLabel'] = $hideLabel;
	$this->_content['values'] = $values;
}

function render($outputMode = NULL, $skipChilds = false)
{
	parent::render($outputMode, $skipChilds);
}

function checkLinks($values)
{
	$appoggio = str_replace('${page}?BID=',__Link::makeUrl('opac_detail_popup',array('action'=>'detail','id'=>'')),$values);
	$appoggio = str_replace('${page_detail}?BID=',__Link::makeUrl('opac_detail',array('action'=>'detail','id'=>'')),$appoggio);
	$appoggio = str_replace('${page}?VID=',__Link::makeUrl('opac_detail_au_popup',array('action'=>'detail','id'=>'')),$appoggio);
	$appoggio = str_replace('${page_detail}?VID=',__Link::makeUrl('opac_detail_au',array('action'=>'detail','id'=>'')),$appoggio);
	$appoggio = str_replace('{linkToEntity}',__Link::makeUrl('archive_detail_au_popup',array('action'=>'detail','id'=>'')),$appoggio);
	if(__Request::get('action') == 'detailarchiveud')
	{
		$appoggio = str_replace('{filterEntity}',__Link::makeUrl('solr_search_archive_ud').'?query=',$appoggio);
	}
	else
	{
		$appoggio = str_replace('{filterEntity}',__Link::makeUrl('solr_search_archive').'?query=',$appoggio);
	}
	$appoggio = str_replace('{linkToEntityDetail}',__Link::makeUrl('archive_detail_au',array('action'=>'detail','id'=>'')),$appoggio);
	$appoggio = str_replace('${button}','<i class="fa fa-external-link" aria-hidden="true"></i>',$appoggio);
	$appoggio = str_replace('${page_search}',__Link::makeUrl('solr_search'),$appoggio);
	$appoggio = str_replace('ispart_of_s','legame_al_livello_piu_elevato_set__html_nxtxt',$appoggio);
	$appoggio = str_replace('collezione_txt','collezione_html_nxtxt',$appoggio);
	$appoggio = str_replace('{searchAuthorICCD}',__Link::makeUrl('solr_search_iccd').'?query=',$appoggio);
	$appoggio = str_replace('{linkToAuthorICCD}',__Link::makeUrl('iccd_detail_au',array('action'=>'detail','id'=>'')),$appoggio);
	$appoggio = str_replace('{linkToAuthorICCDPopup}',__Link::makeUrl('iccd_detail_au_popup',array('action'=>'detail','id'=>'')),$appoggio);
	$appoggio = str_replace('(i)','<i class="fa fa-info" aria-hidden="true"></i>',$appoggio);
	$appoggio = $this->checkDates($appoggio);
	return $appoggio;
}

//Traduzione delle date puntuali come richiesto in POLODEBUG-481, punto 7 del FE
function checkDates($value)
{
	$arrayMesiEng = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	$arrayMesiIt = array('gen.','feb.','mar.','apr.','mag.','giu.','lug.','ago.','set.','ott.','nov.','dic.');
	if(preg_match_all("/(?:[0-9]{4}\/[1-9]{2}\/[0-9]{2})/",$value,$prova))
	{
		if($prova[0])
		{
			foreach ($prova[0] as $date) {
				$newDate = str_replace($arrayMesiEng,$arrayMesiIt,date('d M Y',strtotime(str_replace('/','-',$date))));
				$value = str_replace($date,$newDate,$value);
			}
		}
	}

	return $value;
}
}
