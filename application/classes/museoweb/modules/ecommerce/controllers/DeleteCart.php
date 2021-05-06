<?php
class museoweb_modules_ecommerce_controllers_DeleteCart extends pinax_mvc_core_Command
{
    public function execute($deleteCart, $id)
    {
        if ($deleteCart && $id) {
            $selfUrl = PNX_HOST.'/'.__Request::get('__url__');
            $id = base64_decode($id);
            $cart = museoweb_modules_ecommerce_Helper::getCart();
            unset($cart[$id]);
            museoweb_modules_ecommerce_Helper::saveCart($cart);
            pinax_helpers_Navigation::gotoUrl($selfUrl);
        }
    }
}