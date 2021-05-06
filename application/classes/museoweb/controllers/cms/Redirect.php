<?php
class museoweb_controllers_cms_Redirect extends pinax_mvc_core_Command
{
    public function execute($module, $id)
    {
        if($module && $id)
        {
            $types = array(
                'CA' => 'archive_detail',
                'SP' => 'archive_detail_au',
                'SC' => 'archive_detail_au'
            );

            if(array_key_exists('CA',$types))
            {
                pinax_helpers_Navigation::gotoUrl(__Link::makeUrl($types[$module], array('id' => $id)));
            }
        }

        exit;
    }

}