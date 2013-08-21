<?php

namespace Zf2auth;

use Zf2auth\Entity\Album;
use Zf2auth\Table\AlbumTable;
use Zf2auth\Entity\Fbprofiles;
use Zf2auth\Table\FbprofilesTable;
use Zf2auth\Entity\Profiles;
use Zf2auth\Table\ProfilesTable;
use Zf2auth\Entity\Resources;
use Zf2auth\Table\ResourcesTable;
use Zf2auth\Entity\RoleResources;
use Zf2auth\Table\RoleResourcesTable;
use Zf2auth\Entity\Roles;
use Zf2auth\Table\RolesTable;
use Zf2auth\Entity\UserRoles;
use Zf2auth\Table\UserRolesTable;
use Zf2auth\Entity\Users;
use Zf2auth\Table\UsersTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zf2auth\Entity\Zf2AuthStorage;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

class Module
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Zf2auth\Table\AlbumTable' => function($sm) {
                    $dbAdapter                      = $sm->get('Zend\Db\Adapter\Adapter');
                    $table                          = new AlbumTable($dbAdapter);
                    return $table;
                },
                'Zf2auth\Table\FbprofilesTable' => function($sm) {
                    $dbAdapter                    = $sm->get('Zend\Db\Adapter\Adapter');
                    $table                        = new FbprofilesTable($dbAdapter);
                    return $table;
                },
                'Zf2auth\Table\ProfilesTable' => function($sm) {
                    $dbAdapter                     = $sm->get('Zend\Db\Adapter\Adapter');
                    $table                         = new ProfilesTable($dbAdapter);
                    return $table;
                },
                'Zf2auth\Table\ResourcesTable' => function($sm) {
                    $dbAdapter                         = $sm->get('Zend\Db\Adapter\Adapter');
                    $table                             = new ResourcesTable($dbAdapter);
                    return $table;
                },
                'Zf2auth\Table\RoleResourcesTable' => function($sm) {
                    $dbAdapter                 = $sm->get('Zend\Db\Adapter\Adapter');
                    $table                     = new RoleResourcesTable($dbAdapter);
                    return $table;
                },
                'Zf2auth\Table\RolesTable' => function($sm) {
                    $dbAdapter                     = $sm->get('Zend\Db\Adapter\Adapter');
                    $table                         = new RolesTable($dbAdapter);
                    return $table;
                },
                'Zf2auth\Table\UserRolesTable' => function($sm) {
                    $dbAdapter                 = $sm->get('Zend\Db\Adapter\Adapter');
                    $table                     = new UserRolesTable($dbAdapter);
                    return $table;
                },
                'Zf2auth\Table\UsersTable' => function($sm) {
                    $dbAdapter       = $sm->get('Zend\Db\Adapter\Adapter');
                    $table           = new UsersTable($dbAdapter);
                    return $table;
                },
                'FacebookConfig' => function($sm) {   // <-- For Facebook
                    $config                        = $sm->get('config');
                    return $config['facebook_config'];
                },
                'Zf2auth\Model\Zf2AuthStorage' => function($sm) { // <-- For Authentication
                    return new \Zf2auth\Model\Zf2AuthStorage('zf2authSession');
                },
                'AuthService' => function($sm) { // <-- For Authentication
                    //My assumption, you've alredy set dbAdapter
                    //and has users table with columns : user_name and pass_word
                    //that password hashed with md5
                    $dbAdapter          = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'users', 'email', 'password', 'MD5(?)');

                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTableAuthAdapter);
                    $authService->setStorage($sm->get('Zf2auth\Model\Zf2AuthStorage'));

                    return $authService;
                },
                'routerConfig' => function($sm) {   // <-- For router
                    $config = $sm->get('config');
                    return $config['router'];
                },
            ),
        );
    }

}
