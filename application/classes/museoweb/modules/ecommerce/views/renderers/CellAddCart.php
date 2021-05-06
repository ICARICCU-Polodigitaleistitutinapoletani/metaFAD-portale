<?php
class museoweb_modules_ecommerce_views_renderers_CellAddCart extends pinax_components_render_RenderCellRecordSetList
{
    function renderCell(&$ar, $params)
    {
        $ar->__url__ = __Request::get('__url__').'?addToCart=1&licId='.$ar->document_id.'&id='.$params;
    }
}