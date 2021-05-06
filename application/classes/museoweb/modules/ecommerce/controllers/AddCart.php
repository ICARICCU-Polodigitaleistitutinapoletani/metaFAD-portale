<?php
class museoweb_modules_ecommerce_controllers_AddCart extends pinax_mvc_core_Command
{
    public function execute($addToCart, $license, $id, $recordDetailUrl, $image, $buyAll, $mediaId, $imagesNumber = 1)
    {
        if ($addToCart && $license && $id) {
            $selfUrl = __Request::get('fromUrl');
            // $modulePref = museoweb_modules_ecommerce_Helper::getModuleSetting($this->application->getPageType());
            $arL = pinax_ObjectFactory::createModel('museoweb.modules.ecommerce.models.Licence');
            $r = $arL->load($license);
            $title = $this->recordExistsGetTitle($id);
            if ($title && $r) {
                $cart = museoweb_modules_ecommerce_Helper::getCart();
                $cartkey = museoweb_modules_ecommerce_Helper::createCode('media', $mediaId, $id, $license);
                if ( !array_key_exists( $cartkey, $cart ) ) {
                    $cart[$cartkey] = array( 'recordId' => $id,
                                             'licenceId' => $arL->license_id,
                                             'licence_name' => $arL->license_title,
                                             'licence_use' => $arL->license_description,
                                             'licence_price' => $arL->license_price * $imagesNumber,
                                             'title' => $title, //verificare cosa inserire
                                             'image' => $image,
                                             '__code__' => $cartkey,
                                             '__url__' => $selfUrl,
                                             'detailUrl' => $recordDetailUrl,
                                             'buyAll' => ($buyAll == 'true') ? true : false, //per gestire l'acquisto dell'intero set di media del record
                                             'imagesNumber' => $imagesNumber,
                                             'mediaId' => $mediaId
                                            );
                    // todo: localizzare
                    pinax_application_MessageStack::add(__T('Prodotto aggiunto al carrello'), PNX_MESSAGE_SUCCESS);
                    museoweb_modules_ecommerce_Helper::saveCart($cart);
                }
                else
                {
                  pinax_application_MessageStack::add(__T('Prodotto già presente nel carrello!'), PNX_MESSAGE_SUCCESS);
                  museoweb_modules_ecommerce_Helper::saveCart($cart);
                }
            }
            pinax_helpers_Navigation::gotoUrl($selfUrl);
        }
    }

    public function recordExistsGetTitle($id)
    {
      $url = __Config::get('metafad.solr.metaindice.url');
      $r = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url.'select/?q=id:'.$id.'&wt=json', 'GET', '', 'application/json');
      $r->execute();

      if(json_decode($r->getResponseBody())->response->numFound >= 1) {
        $title = json_decode($r->getResponseBody())->response->docs[0]->denominazione_titolo_ss[0];
        return ($title)?:'Senza titolo';
      }
      else {
        return false;
      }
    }
}
