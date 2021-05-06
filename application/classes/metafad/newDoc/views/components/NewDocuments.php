<?php
class metafad_newDoc_views_components_NewDocuments extends pinax_components_Component
{
    function init()
    {
        parent::init();
    }

    function process()
    {
        $countSlide = 0;
        $countColumn = 0;
        $appoggioColumn = array();
        $appoggioSlide = array();
        $column = array();
        $slides = array();

        $instituteKey = __Session::get('instituteKey');
        $instituteKeyParam = ($instituteKey) ? '?instituteKey='.$instituteKey : '';
        $newDocService = __ObjectFactory::createObject('metafad.newDoc.services.NewDocService');
        $docs = $newDocService->getNewDocs($instituteKey);

        foreach ($docs as $doc) {
            $appoggioColumn[] = $doc;
            $countColumn++;
            $countSlide++;
            if($countColumn == 3)
            {
              $column[] = $appoggioColumn;
              $appoggioColumn = array();
              $countColumn = 0;
            }
            if($countSlide == 6)
            {
              $slides[] = $column;
              $column = array();
              $appoggioSlide = array();
              $countSlide = 0;
            }
        }

        if (!empty($appoggioColumn)) {
          $column[] = $appoggioColumn;
        }

        if (!empty($column)) {
          $slides[] = $column;
        }

        $this->_content['newDoc'] = $slides;
	  }
}