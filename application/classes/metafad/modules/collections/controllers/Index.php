<?php
class metafad_modules_collections_controllers_Index extends pinax_mvc_core_Command
{
  public function execute()
  {
    $ar = pinax_ObjectFactory::createModelIterator('metafad.modules.collections.models.Model')
          ->orderBy('title')->first();
    pinax_helpers_Navigation::gotoUrl(__Link::makeUrl('collections_detail',array('document_id'=>$ar->document_id)));
  }
}
