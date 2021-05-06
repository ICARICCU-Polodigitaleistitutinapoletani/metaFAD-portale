<?php
class museoweb_modules_ecommerce_controllers_Login extends pinax_mvc_core_Command
{
    public function execute()
    {
        if ( $this->user->isLogged()) {
            $url = __Session::get( 'pinax.loginUrl', PNX_HOST.'/'.__Link::makeUrl( 'museoweb_ecommReport' ) );
            __Session::remove( 'pinax.loginUrl' );
            pinax_helpers_Navigation::gotoUrl( $url );
        }
    }
}