<?php
class museoweb_modules_ecommerce_controllers_admin_ajax_Save extends pinax_mvc_core_CommandAjax
{
    public function execute($data)
    {
        // TODO: controllo permessi
        $data = json_decode($data);
        if ($data) {
            $result = new StdClass;
            $result->enabled = $data->enabled;
            $result->prefix = $data->prefix;
            $result->confirmEmail = $data->confirmEmail;
            $result->numDownloads = (int)$data->numDownloads;
            $result->downloadExpirationDays = (int)$data->downloadExpirationDays;
            $result->fe = new StdClass;
            $modules = pinax_Modules::getModulesSorted();
            foreach ($modules as $k=>$m) {
                if (property_exists($data, $k)) {
                    $values = json_decode($data->{$k});
                    if ($values && $m->pageType) {
                        $result->{$k} = $values;
                        $result->fe->{$m->pageType} = $values->enabled;
                    }
                }
            }

            museoweb_modules_ecommerce_Helper::setRegistry($result);
        }

        return true;
    }
}