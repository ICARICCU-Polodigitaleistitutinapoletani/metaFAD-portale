<?php
class metafad_requests_controllers_Request extends pinax_mvc_core_Command
{
  public function execute()
  {
    if (!$this->user->isLogged()) {
      //__Session::set( 'pinax.loginUrl', PNX_HOST.'/'.__Request::get( '__url__' ) );
      $this->changeAction('login');
    }
  }
}
