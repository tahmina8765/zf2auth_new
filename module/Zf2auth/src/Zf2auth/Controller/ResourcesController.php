<?php

namespace Zf2auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zf2auth\Entity\Resources;
use Zf2auth\Form\ResourcesForm;
use Zf2auth\Form\ResourcesSearchForm;
use Zend\Db\Sql\Select;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;

class ResourcesController extends Zf2authAppController
{

    public $vm;

//    protected $resourcesTable;

    function __construct()
    {
        parent::__construct();
        $this->vm = new viewModel();
    }

//    public function getResourcesTable()
//    {
//        if (!$this->resourcesTable) {
//            $sm               = $this->getServiceLocator();
//            $this->resourcesTable = $sm->get('Zf2auth\Table\ResourcesTable');
//        }
//        return $this->resourcesTable;
//    }

    public function getRouterConfig()
    {
        if (!$this->routerConfig) {
            $sm                 = $this->getServiceLocator();
            $this->routerConfig = $sm->get('RouterConfig');
        }
        return $this->routerConfig;
    }

    public function searchAction()
    {

        $request = $this->getRequest();

        $url = 'index';

        if ($request->isPost()) {
            $formdata    = (array) $request->getPost();
            $search_data = array();
            foreach ($formdata as $key => $value) {
                if ($key != 'submit') {
                    if (!empty($value)) {
                        $search_data[$key] = $value;
                    }
                }
            }
            if (!empty($search_data)) {
                $search_by = json_encode($search_data);
                $url .= '/search_by/' . $search_by;
            }
        }
        $this->redirect()->toUrl($url);
    }

    public function indexAction()
    {
        $searchform = new ResourcesSearchForm();
        $searchform->get('submit')->setValue('Search');

        $select    = new Select();
        $order_by  = $this->params()->fromRoute('order_by') ?
                $this->params()->fromRoute('order_by') : 'id';
        $order     = $this->params()->fromRoute('order') ?
                $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
        $page      = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $select->order($order_by . ' ' . $order);
        $search_by = $this->params()->fromRoute('search_by') ?
                $this->params()->fromRoute('search_by') : '';


        $where    = new \Zend\Db\Sql\Where();
        $formdata = array();
        if (!empty($search_by)) {
            $formdata = (array) json_decode($search_by);
            if (!empty($formdata['name'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('name', '%' . $formdata['name'] . '%')
                );
            }
        }
        if (!empty($where)) {
            $select->where($where);
        }


        $resources    = $this->getResourcesTable()->fetchAll($select);
        $totalRecord  = $resources->count();
        $itemsPerPage = 10;

        $resources->current();
        $paginator = new Paginator(new paginatorIterator($resources));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        $searchform->setData($formdata);
        $this->vm->setVariables(array(
            'search_by'   => $search_by,
            'order_by'    => $order_by,
            'order'       => $order,
            'page'        => $page,
            'paginator'   => $paginator,
            'pageAction'  => 'resources',
            'form'        => $searchform,
            'totalRecord' => $totalRecord
        ));
        return $this->vm;
    }

    public function addAction()
    {
        $form = new ResourcesForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $resources = new Resources();
            $form->setInputFilter($resources->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $resources->exchangeArray($form->getData());
                $confirm = $this->getResourcesTable()->saveResources($resources);

                // Redirect to list of resourcess
                return $this->redirect()->toRoute('resources');
            }
        }
        $this->vm->setVariables(array(
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'form'          => $form
        ));

        return $this->vm;
    }

    // Add content to this method:
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('resources', array(
                        'action' => 'add'
            ));
        }
        $resources = $this->getResourcesTable()->getResources($id);

        $form = new ResourcesForm();
        $form->bind($resources);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($resources->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $confirm = $this->getResourcesTable()->saveResources($form->getData());

                // Redirect to list of resourcess
                return $this->redirect()->toRoute('resources');
            }
        }
        $this->vm->setVariables(array(
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'id'            => $id,
            'form'          => $form,
        ));

        return $this->vm;
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('resources');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {

            $id      = (int) $request->getPost('id');
            $confirm = $this->getResourcesTable()->deleteResources($id);


            // Redirect to list of resourcess
            return $this->redirect()->toRoute('resources');
        }
        $this->vm->setVariables(array(
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'id'            => $id,
            'resources'     => $this->getResourcesTable()->getResources($id)
        ));

        return $this->vm;
    }

    /**
     * Custom functions for automated actions
     */
    public function refreshResourcesAction()
    {

        $form              = new ResourcesForm();
        $newly_added       = array();
        $delete_from_exist = array();
//      Read All link from Module.comfig
        $routerConfig      = $this->getRouterConfig();
        $allNode           = array();
        foreach ($routerConfig['routes'] as $node => $data) {
            $allNode[] = $node;
            if (!empty($data['child_routes'])) {
                foreach ($data['child_routes'] as $childnode => $childdata) {
                    $allNode[]    = $node . '/' . $childnode;
                }
            }
        }
//      Read All link from Database
        $db_resources = $this->getResourcesTable()->fetchAll();

//      Compare and add new
        foreach ($allNode as $node) {
            $exist = false;
            foreach ($db_resources as $data) {
                $link_db = $data->getName();
                if ($link_db == $node) {
                    $exist = true;
                }
            }
            if (!$exist) {
                $resources = new Resources();
                $form->setInputFilter($resources->getInputFilter());
                $formdata  = array(
                    'name' => $node,
                );
                $form->setData($formdata);

                if ($form->isValid()) {
                    $resources->exchangeArray($form->getData());
                    $confirm = $this->getResourcesTable()->saveResources($resources);
                    if ($confirm) {
                        $newly_added[] = $node;
                    }
                }
            }
        }
//      Compare and delete old
        foreach ($db_resources as $data) {
            $exist   = true;
            $link_db = $data->getName();
            if (!in_array($link_db, $allNode)) {
                $id      = (int) $data->getId();
                $confirm = $this->getResourcesTable()->deleteResources($id);
                if ($confirm) {
                    $delete_from_exist[] = "[" . $id . "]" . $link_db;
                }
            }
        }

//      Read All link from Database (After Refresh)
        $db_resources = $this->getResourcesTable()->fetchAll();
        $this->vm->setVariables(array(
            'resources'         => $db_resources,
            'allNode'           => $allNode,
            'newly_added'       => $newly_added,
            'delete_from_exist' => $delete_from_exist
        ));
        return $this->vm;
    }

    

}
