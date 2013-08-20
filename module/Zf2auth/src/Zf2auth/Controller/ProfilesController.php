<?php

namespace Zf2auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zf2auth\Entity\Profiles;
use Zf2auth\Form\ProfilesForm;
use Zf2auth\Form\ProfilesSearchForm;


use Zend\Db\Sql\Select;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;





class ProfilesController extends Zf2authAppController
{
    public $vm;
//    protected $profilesTable;

    function __construct()
    {
        parent::__construct();
        $this->vm = new viewModel();

    }

//    public function getProfilesTable()
//    {
//        if (!$this->profilesTable) {
//            $sm               = $this->getServiceLocator();
//            $this->profilesTable = $sm->get('Zf2auth\Table\ProfilesTable');
//        }
//        return $this->profilesTable;
//    }


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

    public function indexAction() {
        $searchform = new ProfilesSearchForm();
        $searchform->get('submit')->setValue('Search');

        $select = new Select();

        $order_by = $this->params()->fromRoute('order_by') ?
                $this->params()->fromRoute('order_by') : 'id';
        $order = $this->params()->fromRoute('order') ?
                $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $select->order($order_by . ' ' . $order);
        $search_by = $this->params()->fromRoute('search_by') ?
                $this->params()->fromRoute('search_by') : '';


        $where    = new \Zend\Db\Sql\Where();
        $formdata = array();
        if (!empty($search_by)) {
            $formdata = (array) json_decode($search_by);
            if (!empty($formdata['user_id'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('user_id', '%' . $formdata['user_id'] . '%')
                );
            }
            if (!empty($formdata['first_name'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('first_name', '%' . $formdata['first_name'] . '%')
                );
            }
            if (!empty($formdata['last_name'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('last_name', '%' . $formdata['last_name'] . '%')
                );
            }
            if (!empty($formdata['created'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('created', '%' . $formdata['created'] . '%')
                );
            }
            if (!empty($formdata['modified'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('modified', '%' . $formdata['modified'] . '%')
                );
            }
            
        }
        if (!empty($where)) {
            $select->where($where);
        }


        $profiles = $this->getProfilesTable()->fetchAll($select);
        $totalRecord  = $profiles->count();
        $itemsPerPage = 10;

        $profiles->current();
        $paginator = new Paginator(new paginatorIterator($profiles));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        $searchform->setData($formdata);
        $this->vm->setVariables(array(
            'search_by'  => $search_by,
            'order_by'   => $order_by,
            'order'      => $order,
            'page'       => $page,
            'paginator'  => $paginator,
            'pageAction' => 'profiles',
            'form'       => $searchform,
            'totalRecord' => $totalRecord
        ));
        return $this->vm;



    }


    public function addAction()
    {
        $form = new ProfilesForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $profiles = new Profiles();
            $form->setInputFilter($profiles->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $profiles->exchangeArray($form->getData());
                $confirm = $this->getProfilesTable()->saveProfiles($profiles);

                // Redirect to list of profiless
                return $this->redirect()->toRoute('profiles');
            }
        }
        $this->vm->setVariables(array(
            'flashMessages'   => $this->flashMessenger()->getMessages(),
            'form' => $form
        ));

        return $this->vm;
    }

    // Add content to this method:
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('profiles', array(
                        'action' => 'add'
                    ));
        }
        $profiles = $this->getProfilesTable()->getProfiles($id);

        $form = new ProfilesForm();
        $form->bind($profiles);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($profiles->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $confirm = $this->getProfilesTable()->saveProfiles($form->getData());

                // Redirect to list of profiless
                return $this->redirect()->toRoute('profiles');
            }
        }
        $this->vm->setVariables(array(
            'flashMessages'   => $this->flashMessenger()->getMessages(),
            'id'   => $id,
            'form' => $form,
        ));

        return $this->vm;
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('profiles');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {

                $id = (int) $request->getPost('id');
                $confirm = $this->getProfilesTable()->deleteProfiles($id);


            // Redirect to list of profiless
            return $this->redirect()->toRoute('profiles');
        }
        $this->vm->setVariables(array(
            'flashMessages'   => $this->flashMessenger()->getMessages(),
            'id'    => $id,
            'profiles' => $this->getProfilesTable()->getProfiles($id)
        ));

        return $this->vm;
    }

}
