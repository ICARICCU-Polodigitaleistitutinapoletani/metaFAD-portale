<?php
class metafad_modules_solr_filters_DefaultQueryBuilder implements metafad_modules_solr_filters_IQueryBuilder
{
    public function build($searchQuery, $search, $facets)
    {
        $searchQuery['q'] = '*'.$search.'*';
        return $searchQuery;
    }
}
