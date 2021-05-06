<?php
class museoweb_controllers_user_LostPassword extends pinax_mvc_core_Command
{
    protected $submit;

    function __construct( $view=NULL, $application=NULL )
    {
        parent::__construct( $view, $application );
        $this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit';
    }

    public function executeLater()
    {
        if ($this->submit) {
			$email = __Request::get('email');
            $ar = pinax_ObjectFactory::createModel('pinax.models.User');
            if (!$ar->find(array('user_email' => $email))) {
                // utente non trovato
                $this->view->validateAddError(__T('MW_LOSTPASSWORD_ERROR'));
                return false;
            }

            // utente trovato
            // genera una nuova password e la invia per email

			$newPassword = md5(date("Y-m-d H:i:s").$ar->user_loginId);

            pinax_import('pinax.helpers.Mail');
            // invia la notifica all'utente
            $subject    = pinax_locale_Locale::get('MW_LOSTPASSWORD_EMAIL_SUBJECT');
            $body       = pinax_locale_Locale::get('MW_LOSTPASSWORD_EMAIL_BODY');
            $body       = str_replace('##USER##', $email, $body);
            $body       = str_replace('##HOST##', pinax_helpers_Link::makeSimpleLink(PNX_HOST, PNX_HOST), $body);
            $body       = str_replace('##PASSWORD##', $newPassword, $body);
            pinax_helpers_Mail::sendEmail(  array('email' => pinax_Request::get('email', ''), 'name' => $ar->user_firstName.' '.$ar->user_lastName),
                                                    array('email' => __Config::get('SMTP_EMAIL'), 'name' => __Config::get('SMTP_SENDER')),
                                                    $subject,
                                                    $body);
			$ar->user_password = md5($newPassword);
			$ar->save();
            $this->changeAction('lostPasswordConfirm');
        }
    }
}
