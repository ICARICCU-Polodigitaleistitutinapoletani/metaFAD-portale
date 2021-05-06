<?php
class Template extends pinaxcms_template_fe_views_AbstractTemplate
{
	public function render($application, $view, $templateData)
	{
		parent::render($application, $view, $templateData);

        $siteProp = unserialize(pinax_Registry::get(__Config::get('REGISTRY_SITE_PROP').$view->_application->getLanguage(), ''));
        $headerLogo = $this->renderImage($templateData, 'headerLogo', $siteProp['title']);
        if ($headerLogo) {
            $view->addOutputCode($headerLogo, 'headerLogo');
        }

        $footer = @$templateData->footer;
        if (is_object($footer)) {
            $footer = pinax_helpers_Convert::formEditObjectToStdObject($footer);
            $footerLogo = array();

            foreach($footer as $item) {
                $logo = $this->renderImage($item, 'footerLogo', @$item->footerLogoTitle);
                if ($logo) {
                    if (@$item->footerLogoLink) {
                        $logo = __Link::makeSimpleLink($logo, $item->footerLogoLink, @$item->footerLogoTitle);
                    }
                    $footerLogo[] = $logo;
                }
            }

            $view->addOutputCode($footerLogo, 'footerLogo');
        }
	}


	public function fixTemplateName($view)
	{
		return true;
	}


    private function renderImage($templateData, $propertyName, $title)
    {
        if (!property_exists($templateData, $propertyName)) return;

        $image = @json_decode($templateData->{$propertyName});
        if (!$image) return '';

        $image = pinaxcms_mediaArchive_MediaManager::getMediaById($image->id);
        $fileName = $image->getFileName();
        return pinax_helpers_Html::renderTag('img', array('src' => $image->getFileName(), 'alt' => $title, 'title' => $title));
    }
}
