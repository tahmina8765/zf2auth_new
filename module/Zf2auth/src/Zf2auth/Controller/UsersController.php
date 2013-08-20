<?php

namespace Zf2auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zf2auth\Entity\Users;
use Zf2auth\Form\UsersForm;
use Zf2auth\Form\UsersSearchForm;


use Zend\Db\Sql\Select;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;





class UsersController extends Zf2authAppController
{
    public $vm;
//    protected $usersTable;

    function __construct()
    {
        parent::__construct();
        $this->vm = new viewModel();

    }

//    public function getUsersTable()
//    {
//        if (!$this->usersTable) {
//            $sm               = $this->getServiceLocator();
//            $this->usersTable = $sm->get('Zf2auth\Table\UsersTable');
//        }
//        return $this->usersTable;
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
        $searchform = new UsersSearchForm();
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
            if (!empty($formdata['username'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('username', '%' . $formdata['username'] . '%')
                );
            }
            if (!empty($formdata['email'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('email', '%' . $formdata['email'] . '%')
                );
            }
            if (!empty($formdata['password'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('password', '%' . $formdata['password'] . '%')
                );
            }
            if (!empty($formdata['email_check_code'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('email_check_code', '%' . $formdata['email_check_code'] . '%')
                );
            }
            if (!empty($formdata['is_disabled'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('is_disabled', '%' . $formdata['is_disabled'] . '%')
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


        $users = $this->getUsersTable()->fetchAll($select);
        $totalRecord  = $users->count();
        $itemsPerPage = 10;

        $users->current();
        $paginator = new Paginator(new paginatorIterator($users));
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
            'pageAction' => 'users',
            'form'       => $searchform,
            'totalRecord' => $totalRecord
        ));
        return $this->vm;



    }


    public function addAction()
    {
        $form = new UsersForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $users = new Users();
            $form->setInputFilter($users->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $users->exchangeArray($form->getData());
                $confirm = $this->getUsersTable()->saveUsers($users);

                // Redirect to list of userss
                return $this->redirect()->toRoute('users');
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
            return $this->redirect()->toRoute('users', array(
                        'action' => 'add'
                    ));
        }
        $users = $this->getUsersTable()->getUsers($id);

        $form = new UsersForm();
        $form->bind($users);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($users->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $confirm = $this->getUsersTable()->saveUsers($form->getData());

                // Redirect to list of userss
                return $this->redirect()->toRoute('users');
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
            return $this->redirect()->toRoute('users');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {

                $id = (int) $request->getPost('id');
                $confirm = $this->getUsersTable()->deleteUsers($id);


            // Redirect to list of userss
            return $this->redirect()->toRoute('users');
        }
        $this->vm->setVariables(array(
            'flashMessages'   => $this->flashMessenger()->getMessages(),
            'id'    => $id,
            'users' => $this->getUsersTable()->getUsers($id)
        ));

        return $this->vm;
    }

}
