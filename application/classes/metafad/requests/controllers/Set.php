<?php
class metafad_requests_controllers_Set extends pinax_mvc_core_Command
{
  public function execute($id,$type)
  {
    if (!$this->user->isLogged()) {
      //__Session::set( 'pinax.loginUrl', PNX_HOST.'/'.__Request::get( '__url__' ) );
      $this->changeAction('login');
    }
    else if(__Request::get('go'))
    {
      $typesArray = array('d' => 'consultazione dettaglio', 'v' => 'visualizzazione','dig' => 'digitalizzazione', 'a' => 'acquisto');
      $tipo = $typesArray[$type];
      $ar = pinax_ObjectFactory::createModel('metafad.requests.models.Model');
      $ar->request_title = 'Richiesta '.$tipo.' record '.$id;
      $ar->request_date = new pinax_types_DateTime();
      $ar->request_state = 'toRead';
      $ar->request_FK_user_id = $this->user->id;
      $ar->request_user_firstName = $this->user->firstName;
      $ar->request_user_lastName = $this->user->lastName;
      $ar->instituteKey = (__Request::get('instituteKey')) ?:'Polo';

      //Setting operatore di default indicato in FAD
      $operator = $this->getDefaultOperator($ar->instituteKey);
      if($operator)
      {
        $ar->request_operator_id = $operator->id;
        $ar->request_operator = $operator->text;
      }

      $ar->request_type = $type;
      $ar->request_text = __Request::get('description');
      $ar->request_object_id = $id;

      $ar->save(null,false,'PUBLISHED');
      //Creazione richiesta, messaggio riuscita, tornare pagina ricerca con link
      $url = __Session::get('searchUrlRequest');
      __Request::set('lastSearchUrl', $url);
      __Session::remove('searchUrlRequest');
    }
  }

  public function getDefaultOperator($instituteKey)
  {
    $instituteProxy = pinax_ObjectFactory::createObject('metafad.institutes.models.proxy.InstituteProxy');
    if(strpos($instituteKey,'-') !== false)
    {
      $institute = $instituteProxy->getInstituteVoByKey($instituteKey);
    }
    else
    {
      $institute = $instituteProxy->getInstituteVoByName($instituteKey);
    }
    $operator = unserialize($institute->institute_resp);
    return $operator;
  }
}
