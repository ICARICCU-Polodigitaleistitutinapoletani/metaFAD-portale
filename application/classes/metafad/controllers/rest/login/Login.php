<?php
class metafad_controllers_rest_login_Login extends pinax_rest_core_CommandRest
{
    function __construct( $application=NULL )
    {
        parent::__construct($application);
    }

    function execute($username, $password)
    {
        $result = array();
        if (!$username || !$password) {
			$result['error'] = __T('Username/password sbagliati');
        } else {
            $authClass = pinax_ObjectFactory::createObject(__Config::get('pinax.authentication'));
			try {
                $user = $authClass->login($username, md5($password), false);
                $result = $user;
            } catch(pinax_authentication_AuthenticationException $e) {
                $result['error'] = __T('Username/password sbagliati');
            }
        }

        return $result;
    }
}
