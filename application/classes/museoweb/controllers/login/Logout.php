<?php
class museoweb_controllers_login_Logout extends pinax_mvc_core_Command
{
    public function execute()
    {
        $authClass = pinax_ObjectFactory::createObject(__Config::get('pinax.authentication'));
        $authClass->logout();

        pinax_helpers_Navigation::gotoUrl( PNX_HOST );
        exit();
    }
}