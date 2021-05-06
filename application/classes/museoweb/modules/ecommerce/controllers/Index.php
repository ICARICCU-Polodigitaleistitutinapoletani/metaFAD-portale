<?php
class museoweb_modules_ecommerce_controllers_Index extends pinax_mvc_core_Command
{
    public function execute()
    {
        $itemsInCart = museoweb_modules_ecommerce_Helper::itemsInCart();
        $this->setComponentsVisibility('panelButtons', $itemsInCart!=0);
    }
}