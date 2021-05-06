<?php
class metafad_services_CaptchaBuilder
{
	// funzione che genera un campo di tipo "CAPTCHA", utilizzato nella form generata dal modulo "formBuilder"
	public function generateCaptcha($pageId)
	{
		$captchaBuilder = '';
		$captchaImgTag = '';
		$captchaPhrase = '';

		// utilizziamo la libreria: https://github.com/Gregwar/Captcha
		$captchaBuilder = new Gregwar\Captcha\CaptchaBuilder;

		// impostiamo le proprietà del captcha
		$captchaBuilder->setDistortion(true);
		$captchaBuilder->setIgnoreAllEffects(true);
		$captchaBuilder->setBackgroundColor(238, 238, 238);

		// creiamo il captcha
		$captchaBuilder->build($width = 200, $height = 90, $font = null);
		// definiamo il tag HTML <img> che disegna l'immagine associata al captcha
		$captchaImgTag = '<img id="captchaImg" src="'.$captchaBuilder->inline().'" />';
		// definiamo il codice associato al captcha
		$captchaPhrase = $captchaBuilder->getPhrase();
		// memorizziamo le informazioni relative al captcha nella classe pinax_Session
		$this->setInSession($pageId, $captchaImgTag, $captchaPhrase);
	}

	// recuperiamo il tag HTML <img> che disegna l'immagine associata al captcha
	public function getImgTag($pageId)
	{
		return __Session::get('captchaImgTag.'.$pageId, '');
	}

	// recuperiamo il codice associato al captcha
	public function getPhrase($pageId)
	{
		return __Session::get('captchaPhrase.'.$pageId, '');
	}

	// validiamo il codice inserito dall'utente
	public function checkPhrase($pageId, $phrase)
	{
		return __Session::get('captchaPhrase.'.$pageId, '') == $phrase;
	}

	// memorizziamo le informazioni relative al captcha nella classe pinax_Session
	private function setInSession($pageId, $captchaImgTag, $captchaPhrase)
	{
		__Session::set('captchaImgTag.'.$pageId, $captchaImgTag);
		__Session::set('captchaPhrase.'.$pageId, $captchaPhrase);
	}

}
