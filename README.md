zf2auth_new
===========

Step - 1
======================
Add the following code in onBootstrap at Application/Module.php

        $this->initAcl($e);
        $e->getApplication()->getEventManager()->attach('route', array($this, 'checkAcl'));


Step - 2
======================
Add the following function in Application/Module.php

    public function initAcl(MvcEvent $e)
    {

        $acl         = new \Zend\Permissions\Acl\Acl();
        $application = $e->getApplication();
        $services    = $application->getServiceManager();

        $this->rolesTable         = $services->get('Zf2auth\Table\RolesTable');
        $this->resourcesTable     = $services->get('Zf2auth\Table\ResourcesTable');
        $this->roleResourcesTable = $services->get('Zf2auth\Table\RoleResourcesTable');


        $roles     = $this->rolesTable->fetchAll();
        $resources = $this->resourcesTable->fetchAll();

        foreach ($resources as $resource) {
            $acl->addResource(new \Zend\Permissions\Acl\Resource\GenericResource($resource->name));
        }

        foreach ($roles as $role) {
            $role_id   = $role->id;
            $role_name = ($role->name);

            $role = new \Zend\Permissions\Acl\Role\GenericRole($role_name);
            $acl->addRole($role_name);

            if ($role_name == 'Administrator') {
                $acl->allow($role_name);
            } else {
                $role_resources   = $this->roleResourcesTable->getResourcesBasedRole($role_id);
                $allowd_resources = array();
                foreach ($role_resources as $row) {
                    $allowd_resources[] = $row;
                    $acl->allow($role_name, $row->resource_name);
                }
            }
        }
        $e->getViewModel()->acl = $acl;
    }

Step - 3
======================
Add the following function in Application/Module.php

    public function checkAcl(MvcEvent $e)
    {
        $route          = $e->getRouteMatch()->getMatchedRouteName();
        $Zf2AuthStorage = new \Zf2auth\Model\Zf2AuthStorage;
        $userRole       = $Zf2AuthStorage->getRole();

        $_SESSION['route'] = $route;
        $_SESSION['tab'] = 0;

        if (!$e->getViewModel()->acl->hasResource($route) || !$e->getViewModel()->acl->isAllowed($userRole, $route)) {
            $response = $e->getResponse();
            //location to page or what ever
            $response->getHeaders()->addHeaderLine('Location', $e->getRequest()->getBaseUrl() . '/404');
            $response->setStatusCode(303);
        }
    }

Step - 4
======================
Add the following function in Application/Module.php


    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'ZF2AuthService' => function($sm) {
                    $authService = new AuthenticationService();
                    $authService->setStorage($sm->get('Zf2auth\Model\Zf2AuthStorage'));
                    return $authService;
                },
            ),
        );
    }
