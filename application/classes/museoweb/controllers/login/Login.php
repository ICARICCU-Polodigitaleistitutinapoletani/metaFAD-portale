<?php
class museoweb_controllers_login_Login extends pinax_mvc_core_Command
{
    public function execute()
    {
        $c = $this->view->getComponentById('formLoginPage');
        if (is_object($c)) {
            $c->setAttribute('accessPageId', $this->view->loadContent('loginPage'));
        }
    }

    public function executeLater()
    {
      if($this->user->isLogged())
      {
        pinax_helpers_Navigation::gotoUrl( __Link::makeUrl( 'link', array( 'pageId' => 'Home' ) ) );
      }
    }
}
