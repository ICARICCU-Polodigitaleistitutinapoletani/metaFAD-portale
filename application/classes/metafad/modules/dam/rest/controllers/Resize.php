<?php
set_time_limit (0);

class metafad_modules_dam_rest_controllers_Resize extends pinax_rest_core_CommandRest
{
    function execute($instance, $w)
    {
        if (__Request::get('bytestreamName') == 'original' && (!$w || $w == '*' || $w > __Config::get('metafad.modules.dam.maxResizeWidth') )) {
            return;
        }

        $url = metafad_modules_dam_Common::getDamBaseUrl().str_replace('dam/', '', $_SERVER['QUERY_STRING']);
        $method = __Request::getMethod();
        $postBody = __Request::getBody();

        $request = pinax_ObjectFactory::createObject('pinax.rest.core.RestRequest', $url, $method, $postBody, 'application/json');
        $request->setTimeout(1000);
        $request->setAcceptType('application/json');
        $request->execute();

        while (ob_get_level()) {
            ob_end_clean();
        }

        foreach ($request->getResponseHeaders() as $header) {
            if (strpos($header, 'Set-Cookie')!==false) continue;
            header($header);
        }

        echo $request->getResponseBody();
        exit;
    }
}
