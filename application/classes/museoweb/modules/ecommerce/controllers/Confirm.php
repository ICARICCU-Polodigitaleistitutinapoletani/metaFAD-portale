<?php
class museoweb_modules_ecommerce_controllers_Confirm extends pinax_mvc_core_Command
{
    public function execute()
    {
        $gateway = pinax_ObjectFactory::createObject(__Config::get('museoweb.ecommerce.gateway'));
        if ($gateway) {
            $gateway->confirm();
        }
    }
}