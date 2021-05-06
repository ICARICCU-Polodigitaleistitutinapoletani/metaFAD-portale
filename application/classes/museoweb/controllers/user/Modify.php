<?php
class museoweb_controllers_user_Modify extends pinax_mvc_core_Command
{
    protected $submit;

    function __construct( $view=NULL, $application=NULL )
    {
        parent::__construct( $view, $application );
        $this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit';
    }

    public function execute()
    {
        if ($this->user->isLogged() && !$this->submit) {
            $ar = pinax_ObjectFactory::createModel('pinax.models.User');
            $ar->load($this->user->id);

            __Request::set('name',          $ar->user_firstName);
            __Request::set('surname',       $ar->user_lastName);
            __Request::set('company',       $ar->user_companyName);
            __Request::set('birthDate',     date("Y-m-d", strtotime(str_replace("/","-",$ar->user_birthday))));
            __Request::set('sex',           $ar->user_sex);
            __Request::set('taxCode',           $ar->user_taxCode);
            __Request::set('username',           $ar->user_loginId);
            __Request::set('sbnUser',           $ar->user_sbnFlag);
            __Request::set('address',       $ar->user_address);
            __Request::set('city',          $ar->user_city);
            __Request::set('userstate',     $ar->user_state);
            __Request::set('zip',           $ar->user_zip);
            __Request::set('country',       $ar->user_country);
            __Request::set('email',         $ar->user_email);
            __Request::set('phone',         $ar->user_phone);
            __Request::set('mobile',        $ar->user_mobile);
            __Request::set('phone2',        $ar->user_phone2);
            __Request::set('web',           $ar->user_www);
            __Request::set('fax',           $ar->user_fax);
            __Request::set('newsletter',    $ar->user_wantNewsletter);
            __Request::set('mailinglist',   $ar->user_isInMailinglist);
        }
    }

    public function executeLater()
    {
        if ($this->user->isLogged() && $this->submit) {
            $ar = pinax_ObjectFactory::createModel('pinax.models.User');
            $ar->load($this->user->id);

            $email = pinax_Request::get('email', false, '');
            if ($email != $ar->user_email) {
                $ar2 = pinax_ObjectFactory::createModel('pinax.models.User');
                if ($ar2->find(array('user_email' => $email)) && $ar2->user_id!=$ar->user_id) {
                    $this->view->validateAddError('L\'email è già presente nel database, usare un\'altra email');
                    return;
                }
            }

            $record = array();

            if(pinax_Request::get('psw') !== pinax_Request::get('psw_confirm') && pinax_Request::get('psw') !== '')
            {
                $this->view->validateAddError('La password non è stata confermata correttamente. Si prega di compilare il campo correttamente');
                return;
            }
            else if(pinax_Request::get('psw') == pinax_Request::get('psw_confirm') && pinax_Request::get('psw') !== '')
            {
              $ar->user_password       = pinax_password(pinax_Request::get('psw', false, ''));
            }

            $ar->user_firstName      = pinax_Request::get('name', false, '');
            $ar->user_lastName       = pinax_Request::get('surname', false, '');
            $ar->user_companyName    = pinax_Request::get('company', false, '');
            $ar->user_title          = pinax_Request::get('jobtitle', false, '');
            $ar->user_address        = pinax_Request::get('address', false, '');
            $ar->user_city           = pinax_Request::get('city', false, '');
            $ar->user_state          = pinax_Request::get('userstate', false, '');
            $ar->user_zip            = pinax_Request::get('zip', false, '');
            $ar->user_country        = pinax_Request::get('country', false, '');
            $ar->user_email          = $email;
            $ar->user_birthday       = pinax_Request::get('birthDate', false, '');
            $ar->user_taxCode        = pinax_Request::get('taxCode', false, '');
            $ar->user_phone          = pinax_Request::get('phone', false, '');
            $ar->user_mobile         = pinax_Request::get('mobile', false, '');
            $ar->user_phone2         = pinax_Request::get('phone2', false, '');
            $ar->user_www            = pinax_Request::get('web', false, '');
            $ar->user_fax            = pinax_Request::get('fax', false, '');
            $ar->user_wantNewsletter = pinax_Request::get('newsletter', false, '')=='1' ? 1 : 0;
            $ar->user_isInMailinglist= pinax_Request::get('mailinglist', false, '')=='1' ? 1 : 0;
            $ar->user_sbnFlag        = pinax_Request::get('sbnUser', false, '');

            $ar->save();
            $this->changeAction('modifyConfirm');
        }
    }
}
