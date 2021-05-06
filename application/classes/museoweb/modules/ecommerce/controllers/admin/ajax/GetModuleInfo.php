<?php
class museoweb_modules_ecommerce_controllers_admin_ajax_GetModuleInfo extends pinax_mvc_core_CommandAjax
{
    public function execute($id)
    {
        // TODO: controllo permessi
       $module = pinax_Modules::getModule($id);
       if ($module) {
            $moduleHelper = pinax_ObjectFactory::createObject('pinaxcms.helpers.Modules');
            $fields = $moduleHelper->getFields($id);

            $result = array('fields' => array(), 'media' => array());
            foreach($fields as $k=>$v) {
                if ($v->type==pinaxcms_core_models_enum_FieldTypeEnum::IMAGE ||
                    $v->type==pinaxcms_core_models_enum_FieldTypeEnum::MEDIA ||
                    $v->type==pinaxcms_core_models_enum_FieldTypeEnum::REPEATER_IMAGE ||
                    $v->type==pinaxcms_core_models_enum_FieldTypeEnum::REPEATER_MEDIA
                    ) {
                   $result['media'][] = $v;
                } else {
                   $result['fields'][] = $v;
                }
            }
            return $result;
       } else {
           return false;
       }
    }


}