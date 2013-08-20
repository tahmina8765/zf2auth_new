<?php
namespace Zf2auth;

return array(
    'controllers' => array(
        'invokables' => array(
            
            'Zf2auth\Controller\Album' => 'Zf2auth\Controller\AlbumController',
            'Zf2auth\Controller\Fbprofiles' => 'Zf2auth\Controller\FbprofilesController',
            'Zf2auth\Controller\Profiles' => 'Zf2auth\Controller\ProfilesController',
            'Zf2auth\Controller\Resources' => 'Zf2auth\Controller\ResourcesController',
            'Zf2auth\Controller\RoleResources' => 'Zf2auth\Controller\RoleResourcesController',
            'Zf2auth\Controller\Roles' => 'Zf2auth\Controller\RolesController',
            'Zf2auth\Controller\UserRoles' => 'Zf2auth\Controller\UserRolesController',
            'Zf2auth\Controller\Users' => 'Zf2auth\Controller\UsersController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'album' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/album[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/search_by/:search_by]',
                    'constraints' => array(
                        'action'    => '(?!\bpage\b)(?!\border_by\b)(?!\bsearch_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Zf2auth\Controller\Album',
                        'action'     => 'index',
                    ),
                ),
            ),
            'fbprofiles' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/fbprofiles[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/search_by/:search_by]',
                    'constraints' => array(
                        'action'    => '(?!\bpage\b)(?!\border_by\b)(?!\bsearch_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Zf2auth\Controller\Fbprofiles',
                        'action'     => 'index',
                    ),
                ),
            ),
            'profiles' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/profiles[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/search_by/:search_by]',
                    'constraints' => array(
                        'action'    => '(?!\bpage\b)(?!\border_by\b)(?!\bsearch_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Zf2auth\Controller\Profiles',
                        'action'     => 'index',
                    ),
                ),
            ),
            'resources' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/resources[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/search_by/:search_by]',
                    'constraints' => array(
                        'action'    => '(?!\bpage\b)(?!\border_by\b)(?!\bsearch_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Zf2auth\Controller\Resources',
                        'action'     => 'index',
                    ),
                ),
            ),
            'role_resources' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/role-resources[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/search_by/:search_by]',
                    'constraints' => array(
                        'action'    => '(?!\bpage\b)(?!\border_by\b)(?!\bsearch_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Zf2auth\Controller\RoleResources',
                        'action'     => 'index',
                    ),
                ),
            ),
            'roles' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/roles[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/search_by/:search_by]',
                    'constraints' => array(
                        'action'    => '(?!\bpage\b)(?!\border_by\b)(?!\bsearch_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Zf2auth\Controller\Roles',
                        'action'     => 'index',
                    ),
                ),
            ),
            'user_roles' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/user-roles[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/search_by/:search_by]',
                    'constraints' => array(
                        'action'    => '(?!\bpage\b)(?!\border_by\b)(?!\bsearch_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Zf2auth\Controller\UserRoles',
                        'action'     => 'index',
                    ),
                ),
            ),
            'users' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/users[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/search_by/:search_by]',
                    'constraints' => array(
                        'action'    => '(?!\bpage\b)(?!\border_by\b)(?!\bsearch_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Zf2auth\Controller\Users',
                        'action'     => 'index',
                    ),
                ),
            ),
            
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            
            'album' => __DIR__ . '/../view',
            'fbprofiles' => __DIR__ . '/../view',
            'profiles' => __DIR__ . '/../view',
            'resources' => __DIR__ . '/../view',
            'role_resources' => __DIR__ . '/../view',
            'roles' => __DIR__ . '/../view',
            'user_roles' => __DIR__ . '/../view',
            'users' => __DIR__ . '/../view',
        ),
        'template_map' => array(
            'paginator-slide' => __DIR__ . '/../view/layout/slidePaginator.phtml',
        ),
    ),

);
