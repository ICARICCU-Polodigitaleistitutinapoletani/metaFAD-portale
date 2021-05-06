<?php
class museoweb_modules_ecommerce_views_renderers_CellShow extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row, $columnName = null)
    {
        $output = __Link::makeLinkWithIcon( 'actionsMVC',
                                            'icon-plus-sign btn-icon',
                                            array(
                                                'title' => __T('Dettaglio'),
                                                'id' => $key,
                                                'action' => 'show') );
        return $output;
    }
}


