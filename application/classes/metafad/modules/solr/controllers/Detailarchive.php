<?php
class metafad_modules_solr_controllers_Detailarchive extends pinax_mvc_core_Command
{
  function execute()
  {
    $this->setComponentsAttribute('id','value',__Request::get('id'));
  }
}
