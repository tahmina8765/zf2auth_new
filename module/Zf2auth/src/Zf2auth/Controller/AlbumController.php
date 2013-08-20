<?php

namespace Zf2auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zf2auth\Entity\Album;
use Zf2auth\Form\AlbumForm;
use Zf2auth\Form\AlbumSearchForm;


use Zend\Db\Sql\Select;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;





class AlbumController extends Zf2authAppController
{
    public $vm;
//    protected $albumTable;

    function __construct()
    {
        parent::__construct();
        $this->vm = new viewModel();

    }

//    public function getAlbumTable()
//    {
//        if (!$this->albumTable) {
//            $sm               = $this->getServiceLocator();
//            $this->albumTable = $sm->get('Zf2auth\Table\AlbumTable');
//        }
//        return $this->albumTable;
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
        $searchform = new AlbumSearchForm();
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
            if (!empty($formdata['artist'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('artist', '%' . $formdata['artist'] . '%')
                );
            }
            if (!empty($formdata['title'])) {
                $where->addPredicate(
                        new \Zend\Db\Sql\Predicate\Like('title', '%' . $formdata['title'] . '%')
                );
            }
            
        }
        if (!empty($where)) {
            $select->where($where);
        }


        $album = $this->getAlbumTable()->fetchAll($select);
        $totalRecord  = $album->count();
        $itemsPerPage = 10;

        $album->current();
        $paginator = new Paginator(new paginatorIterator($album));
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
            'pageAction' => 'album',
            'form'       => $searchform,
            'totalRecord' => $totalRecord
        ));
        return $this->vm;



    }


    public function addAction()
    {
        $form = new AlbumForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $album = new Album();
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $album->exchangeArray($form->getData());
                $confirm = $this->getAlbumTable()->saveAlbum($album);

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
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
            return $this->redirect()->toRoute('album', array(
                        'action' => 'add'
                    ));
        }
        $album = $this->getAlbumTable()->getAlbum($id);

        $form = new AlbumForm();
        $form->bind($album);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $confirm = $this->getAlbumTable()->saveAlbum($form->getData());

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
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
            return $this->redirect()->toRoute('album');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {

                $id = (int) $request->getPost('id');
                $confirm = $this->getAlbumTable()->deleteAlbum($id);


            // Redirect to list of albums
            return $this->redirect()->toRoute('album');
        }
        $this->vm->setVariables(array(
            'flashMessages'   => $this->flashMessenger()->getMessages(),
            'id'    => $id,
            'album' => $this->getAlbumTable()->getAlbum($id)
        ));

        return $this->vm;
    }

}
