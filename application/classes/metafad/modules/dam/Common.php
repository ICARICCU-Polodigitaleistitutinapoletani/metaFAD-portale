<?php
class metafad_modules_dam_Common
{
    public static function getDamUrl($damInstance = null)
    {
        return __Config::get('portale.dam.url.local').($damInstance ? $damInstance : __Config::get('portale.dam.instance'));
    }

    public static function getDamBaseUrl()
    {
        return __Config::get('portale.dam.url.local');
    }

    // fix temporaneo da togliere quando verranno sganciati i servizi del BE funzionali al FE
    public static function replaceUrl($url)
    {
        return str_replace(array('mibac_museowebfad/rest/dam', 'dam/admin/rest/dam'), array('dam', 'dam'), $url);
    }

    /**
     * @param  string $url
     * @param  string $size
     * @return string
     */
    public static function replaceUrlWithSize($url, $size)
    {
        $url = self::replaceUrl($url);
        return in_array($size, array('original', 'thumbnail')) ?
                preg_replace('/(get)\/(.*)\/(.*)(\?timestamp=\d*)?/', 'get/$2/'.$size, $url) :
                preg_replace('/(get)\/(.*)\/(.*)(\?timestamp=\d*)?/', 'resize/$2/original?'.$size, $url);
    }
}
