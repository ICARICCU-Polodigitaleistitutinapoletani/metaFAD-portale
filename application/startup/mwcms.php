<?php
pinaxcms_Pinaxcms::init();

pinax_loadLocale('museoweb.*');
pinax_loadLocale('metafad.*');

metafad_modules_collections_Module::registerModule();
metafad_modules_institutesManagement_Module::registerModule();
museoweb_modules_ecommerce_Module::registerModule();
metafad_modules_dam_Module::registerModule();

pinax_ObjectFactory::remapClass('pinax.models.User', 'metafad.users.models.User');
pinax_ObjectFactory::remapClass('pinax.models.UserGroup', 'metafad.users.models.UserGroup');
pinax_ObjectFactory::remapClass('pinaxcms.userManager.models.User', 'metafad.users.models.User');
pinax_ObjectFactory::remapClass('pinaxcms.groupManager.models.UserGroup', 'metafad.users.models.UserGroup');

__Paths::set('CACHE_JS', 'cache/');