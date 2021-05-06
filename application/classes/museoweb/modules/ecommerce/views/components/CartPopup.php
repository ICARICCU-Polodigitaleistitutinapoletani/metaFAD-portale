<?php
class museoweb_modules_ecommerce_views_components_CartPopup extends pinax_components_Component
{
    function process()
    {
      $id = __Request::get('id');
      $type = __Request::get('type');

      $mediaId = __Request::get('mediaId');

      $request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', __Config::get('metafad.fe.urlViewerService').$id.'?type='.$type, 'GET', 'wt=json', 'application/x-www-form-urlencoded');
      $request->setAcceptType('application/json');
      $request->execute();

      $response = json_decode($request->getResponseBody());
      $globalLicenses = $response->licenses;
      $licenze = array();
      if($globalLicenses)
      {
        foreach ($globalLicenses as $key => $value) {
          $licenze[$value->id] = $value;
        }
      }

      if($response->physicalSTRU->image)
      {
        foreach ($response->physicalSTRU->image as $i) {
          if($i->id == $mediaId)
          {
            $image = $i;
          }
        }
      }
      if($image)
      {
        $licenceSingle = json_decode($image->ecommerce);
        if($licenceSingle)
        {
          foreach ($licenceSingle as $key => $value) {
            $licenze[$value->id] = $value;
          }
        }
        $this->_content['Image'] = $image;
      }

      $this->_content['id'] = $id;


      if(!empty($licenze))
      {
        $this->_content['Ecommerce'] = true;
        $this->_content['Licenses'] = $licenze;
      }


    }
}
