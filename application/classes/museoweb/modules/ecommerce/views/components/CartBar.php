<?php
class museoweb_modules_ecommerce_views_components_CartBar extends pinax_components_Component
{
    function render($outputMode=NULL, $skipChilds=false)
    {
        $itemsInCart = museoweb_modules_ecommerce_Helper::itemsInCart();
        $label = __T('Il mio carrello').( $itemsInCart ? ' ('.$itemsInCart.')' : '' );
        $output = __Link::makeLink( 'museoweb_ecommShowCart', array( 'title' => $label ) );
    }
}
