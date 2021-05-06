<?php
class metafad_controllers_Reserved extends metafad_controllers_Index
{
	protected $submit;
	protected $captchaBuilderService;
	protected $captchaStuff;

	function __construct( $view=NULL, $application=NULL )
	{
		parent::__construct( $view, $application );
		$this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit';
		$this->captchaBuilderService = $this->getCaptchaBuilderService();
	}

	public function execute()
	{
		if(!$this->submit)
		{
			$captcha = $this->view->getComponentById('captchaImage');
			$this->captchaStuff = $this->generateCaptchaField($this->captchaBuilderService, $this->application->getPageId(), $captcha);
			$captcha->setContent(array('image'=>$this->captchaStuff['image']));
		}
	}

	public function executeLater()
	{
		parent::executeLater();

		$responsabileEmail = $this->view->getComponentById('responsabile')->getContent();

		if ($this->submit) {

			$data = array();

			$captchaPhrase = $this->captchaBuilderService->getPhrase($this->application->getPageId());
			if($captchaPhrase !== __Request::get('captcha'))
			{
				$this->logAndMessage('Errore: il codice captcha inserito è errato.', false, PNX_LOG_ERROR);
				$this->changeAction('reserved');
			}

			$data['Nome'] = __Request::get('name', false, '');
			$data['Cognome'] = __Request::get('surname', false, '');
			$data['Email'] = __Request::get('email', false, '');
			$data['Ente/Azienda'] = __Request::get('company', false, '');
			$data['Telefono ente/azienda'] = __Request::get('companynumber', false, '');
			$data['Email ente/azienda'] = __Request::get('companyemail', false, '');
			$data['Nazione ente/azienda'] = __Request::get('companynation', false, '');
			$data['Motivazione'] = __Request::get('motivation', false, '');

			$body = $this->generateRequest($data);
			$subject = __T('reservedAreaSubject');
			//Notifica richiesta via e-mail
			pinax_import('pinax.helpers.Mail');
			// invia la notifica all'utente

			$r = pinax_helpers_Mail::sendEmail(  array('email' => $responsabileEmail),
			array('email' => __Config::get('SMTP_EMAIL'), 'name' => __Config::get('SMTP_SENDER')),
			$subject,
			$body);
			$this->changeAction('requested');
		}
	}

	private function generateRequest($data)
	{
		$text = __T('reservedAreaStart');
		foreach ($data as $k => $v) {
			$text .= '<br/>'.$k.' : ' . $v;
		}
		return $text;
	}

	public function generateCaptchaField($captchaBuilderService, $pageId, $captchaElement)
	{
		$captchaBuilderService->generateCaptcha($pageId);
		return array('phrase'=>$captchaBuilderService->getPhrase($pageId),'image'=>$captchaBuilderService->getImgTag($pageId));
	}

	private function getCaptchaBuilderService()
	{
		return __ObjectFactory::createObject('metafad.services.CaptchaBuilder');
	}
}
