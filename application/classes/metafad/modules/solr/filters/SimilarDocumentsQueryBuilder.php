<?php
class metafad_modules_solr_filters_SimilarDocumentsQueryBuilder implements metafad_modules_solr_filters_IQueryBuilder
{
    public function build($searchQuery, $search, $facets)
    {
        $similarFields = array(
            'title',
            'description',
            'author',
            'classcategory',
        );

        $sf = implode(',', $similarFields);

        $searchQuery['url'] = __Config::get('sia.url.rest');
        $searchQuery['action'] = 'mlt';
        $searchQuery['mlt.fl'] = $sf;
        $searchQuery['q'] = __Request::get('search');

        unset($searchQuery['facet']);
        unset($searchQuery['facet.limit']);
        unset($searchQuery['facet.field']);
        unset($searchQuery['fq']);

        return $searchQuery;
    }
}