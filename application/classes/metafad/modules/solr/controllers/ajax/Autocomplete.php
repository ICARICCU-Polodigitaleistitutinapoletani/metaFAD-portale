<?php
class metafad_modules_solr_controllers_ajax_Autocomplete extends pinax_mvc_core_CommandAjax
{
    function execute($fieldName, $value, $filters = null)
    {
        $solrFieldName = $fieldName.'_txt';
        $solrFieldNameFacet = $fieldName . '_ss';

        $query = array(
            'q=' . $solrFieldName.':*'.urlencode($value).'*',
            'fl=' . $solrFieldName,
            'facet=true',
            'facet.field='. $solrFieldNameFacet,
            'facet.limit='.((int)__Config::get('search.autocomplete.list.length') + 1),
            'wt=json',
            'rows=0'
        );

        if(!empty($filters))
        {
            foreach($filters as $f => $v)
            {
                $query[] = 'fq='.$f.'_txt:*'.urlencode(str_replace('/','',$v)).'*';
            }
        }

        $url = __Config::get('metafad.iccd.detail.url').'select?'.implode('&', $query);

        $content = json_decode(file_get_contents($url));

        $result = array();

        $facetField = $content->facet_counts->facet_fields;

        $facets = $facetField->$solrFieldNameFacet;

        for ($i = 0; $i < count($facets); $i+=2) {
            $term = $facets[$i];
            $termFreq = $facets[$i+1];

            if ($termFreq > 0) {
                if(stripos($term,$value) !== false)
                {
                    $result[] = $term;
                }
            }
            if(count($result) >= (int)__Config::get('search.autocomplete.list.length'))
            {
                asort($result);
                $limited = true;
                $result[] = '-- digita altro per risultati più accurati --';
                break;
            }
        }

        if(!$limited)
        {
            asort($result);
        }

        $this->directOutput = true;

        return $result;
    }
}
