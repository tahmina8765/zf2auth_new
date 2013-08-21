<?php

namespace Zf2auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zf2auth\Entity\Users;
use Zf2auth\Form\UsersForm;
use Zf2auth\Form\UsersSearchForm;
use Zf2auth\Form\LoginForm;
use Zf2auth\Form\RegistrationForm;
use Zf2auth\Form\EmailCheckCodeForm;
use Zf2auth\Form\ForgetPasswordForm;
use Zf2auth\Form\ChangePasswordForm;
use Zf2auth\Form\ResetPasswordForm;
use Zend\Authentication\Result;
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

    public function indexAction()
    {
        $searchform = new UsersSearchForm();
        $searchform->get('submit')->setValue('Search');

        $select = new Select();

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


        $users        = $this->getUsersTable()->fetchAll($select);
        $totalRecord  = $users->count();
        $itemsPerPage = 10;

        $users->current();
        $paginator = new Paginator(new paginatorIterator($users));
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
            'pageAction'  => 'users',
            'form'        => $searchform,
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
            return $this->redirect()->toRoute('users');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {

            $id      = (int) $request->getPost('id');
            $confirm = $this->getUsersTable()->deleteUsers($id);


            // Redirect to list of userss
            return $this->redirect()->toRoute('users');
        }
        $this->vm->setVariables(array(
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'id'            => $id,
            'users'         => $this->getUsersTable()->getUsers($id)
        ));

        return $this->vm;
    }


    public function authenticateAction()
    {
        $form     = new LoginForm();
        $redirect = 'users/login';

        $request = $this->getRequest();
        if ($request->isPost()) {

            $formData = $request->getPost();

            $form->setValidationGroup('email', 'password');
            $form->setData($formData);
            if ($form->isValid()) {
                $this->checkAuthentication($request->getPost('email'), $request->getPost('password'), $request->getPost('rememberme'), $redirect);
            } else {
                $this->flashMessenger()->addMessage(array('error' => $this->confMessage->invalidUsername));
            }
        }
        return $this->redirect()->toRoute($redirect);
    }

    private function checkAuthentication($email, $password, $rememberme = null, $redirect = null)
    {


        if (!empty($email) && !empty($email)) {

        } else {
            $this->flashMessenger()->addMessage(array('error' => $this->confMessage->emptyEmail));
            return $this->redirect()->toRoute($redirect);
        }


        //check authentication...
        $this->getAuthService()->getAdapter()
                ->setIdentity($email)
                ->setCredential($password);

        $result = $this->getAuthService()->authenticate();

        switch ($result->getCode()) {

            case Result::FAILURE_IDENTITY_NOT_FOUND:
                /** do stuff for nonexistent identity * */
                $this->flashMessenger()->addMessage(array('error' => $this->confMessage->emailNotExist));
                break;

            case Result::FAILURE_CREDENTIAL_INVALID:
                /** do stuff for invalid credential * */
                $this->flashMessenger()->addMessage(array('error' => $this->confMessage->passwordNotMatch));

                break;

            case Result::SUCCESS:
                /** do stuff for successful authentication * */
                // $this->flashMessenger()->addMessage(array('success' => 'Successfully logged in.'));

                break;

            default:
                /** do stuff for other failure * */
                break;
        }

        foreach ($result->getMessages() as $message) {
            //save message temporary into flashmessenger
            $this->flashMessenger()->addMessage($message);
        }

        if ($result->isValid()) {
            $redirect = 'home';
            //check if it has rememberMe :
            if ($rememberme == 1) {
                $this->getSessionStorage()->setRememberMe(1);
            }
            //set storage again
            $this->getAuthService()->setStorage($this->getSessionStorage());

            $identity       = $this->getAuthService()->getAdapter()->getIdentity();
            $currentUserObj = $this->getUsersTable()->fetchAllByIdentity($identity);
            if (!empty($currentUserObj)) {
                foreach ($currentUserObj as $user) {
                    $currentUser             = $user;
                }
            }

            $currentUser['identity'] = $email;
            $this->getAuthService()->getStorage()->write($currentUser);

        }
        return $this->redirect()->toRoute($redirect);
    }

    public function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();

        $this->flashMessenger()->addMessage(array('success' => $this->confMessage->logout));
        session_destroy();
        return $this->redirect()->toRoute('home');
    }

    public function loginAction()
    {

        //if already login, redirect to success page
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }
        $form = new LoginForm();
        $form->get('submit')->setValue('Login');

        return array(
            'form'          => $form,
            'flashMessages' => $this->flashMessenger()->getMessages()
        );
    }

    public function registrationAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }
        $this->layout('layout/small-layout');
        $form = new RegistrationForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $users    = new Users();
            $form->setInputFilter($users->getInputFilter());
            $formData = $request->getPost();
            $form->setData($formData);
            if ($form->isValid()) {
                $valid = true;
                /**
                 * Check e-mail or username is exist
                 */
//                if (!empty($formData['username'])) {
//                    $usersobj = $this->getUsersTable()->getUsersByUserName($formData['username']);
//                    if (!empty($usersobj)) {
//                        $valid          = false;
//                        $this->error[0] = array('error' => $this->confMessage->userExist);
//                    }
//                }
                if (!empty($formData['email'])) {
                    $usersobj = $this->getUsersTable()->getUsersByEmail($formData['email']);
                    if (!empty($usersobj)) {
                        $valid          = false;
                        $this->error[0] = array('error' => 'Email already used.');
                    }
                }
                /**
                 * Check password length
                 */
                if ($formData['password'] != $formData['repassword']) {
                    $valid          = false;
                    $this->error[0] = array('error' => $this->confMessage->passwordNotMatch);
                }
                if (strlen($formData['password']) < 6) {
                    $valid          = false;
                    $this->error[0] = array('error' => $this->confMessage->smallPassword);
                }
                if ($valid) {
                    $users->exchangeArray($form->getData());
                    $this->getUsersTable()->saveRegistration($users, $formData);
                    $this->checkAuthentication($request->getPost('email'), $request->getPost('password'), 0, 'users/registration');
                }
                // Redirect to list of userss
                // return $this->redirect()->toRoute('users/authenticate');
            } else {
                $this->error[0] = array('error' => $this->confMessage->requiredFields);
            }
        }
        $vm             = new ViewModel(array(
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'form'          => $form,
            'error'         => $this->error,
        ));
        return $vm;
    }

    public function confirmEmailAction()
    {
        $this->layout('layout/small-layout');
        $id = (int) $this->params()->fromRoute('id', 0);

        if ($id > 0) {

        } else {
            $this->layout("layout/layout");
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $users = $this->getUsersTable()->getUsers($id);
        $users->setEmail_check_code('');

        $form = new EmailCheckCodeForm();
        $form->setValidationGroup('id');
        $form->bind($users);
        $form->get('submit')->setAttribute('value', 'Submit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $formData   = $request->getPost();
            $valid      = true;
            /**
             * Check E-mail check code
             */
            $checkusers = $this->getUsersTable()->getUsers($formData['id']);
            if ($checkusers->getEmail_check_code() == $formData['email_check_code']) {
                $formData['email_check_code'] = '';
            } else {
                $this->error[0] = array('error' => $this->confMessage->invalidEmailCode);
                $valid          = false;
            }

            $form->setInputFilter($users->getInputFilter());
            $form->setData($formData);
            if ($form->isValid()) {
                if ($valid) {
                    $this->getUsersTable()->ConfirmEmailCheckCode($form->getData());

                    $currentUser                     = array();
                    $currentUser                     = $this->getAuthService()->getStorage()->read();
                    $currentUser['email_check_code'] = $formData['email_check_code'];
                    $this->getAuthService()->getStorage()->write($currentUser);
                    return $this->redirect()->toRoute('home');
                }
            }
        }

        $vm = new ViewModel(array(
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'id'            => $id,
            'form'          => $form,
            'error'         => $this->error,
        ));
        return $vm;
    }

    public function forgetPasswordAction()
    {
        $this->layout('layout/small-layout');
        $form = new ForgetPasswordForm();
        $emailer = new \HBMail\Controller\EmailersController();

        $request = $this->getRequest();
        if ($request->isPost()) {

            $users    = new Users();
            $form->setInputFilter($users->getInputFilter());
            $formData = $request->getPost();
            $usersobj = "";
            $valid    = true;
            /**
             * Check e-mail of username is exist
             */
            if (!empty($formData['username'])) {
                $usersobj = $this->getUsersTable()->getUsersByUserName($formData['username']);
            } else if (!empty($formData['email'])) {
                $usersobj = $this->getUsersTable()->getUsersByEmail($formData['email']);
            } else {
                $valid          = false;
                $this->error[0] = array('error' => $this->confMessage->emptyUsername);
            }
            if ($valid) {
                if (!empty($usersobj)) {
                    $formData['id']                      = $usersobj->id;
                    $password_access_tocken              = $formData['password_access_tocken']  = md5($this->getUsersTable()->generatePassword());
                    $access_token_valid_till             = $formData['access_token_valid_till'] = date('Y-m-d h:i:s', strtotime(' +1 day'));
                    $retriveurl                          = $emailer->siteURL() . '/users/reset-password/' . $formData['id'] . '/' . $password_access_tocken;
                } else {
                    $valid          = false;
                    $this->error[0] = array('error' => $this->confMessage->invalidEmail);
                }
            }
            $form->setData($formData);
            $form->setValidationGroup('id', 'password_access_tocken', 'access_token_valid_till');
            if ($form->isValid()) {
                if ($valid) {
                    $profiledata = $this->getViewUsersTable()->getViewUsers($formData['id']);
                    $body        = "
Hi " . trim($profiledata->first_name) . ",<br><br>
The security of your HossBrag profile is very important to us.  We encourage you to choose a strong password that is also easy to remember.<br><br>
Please click here to create a new password:<br><br>
<a href='" . $retriveurl . "'>" . $retriveurl . "</a><br><br>
If you did not make this request, please <a href='mailto:team@hossbrag.com' style='color: #999999; text-decoration: underline;'>contact us</a> now.<br><br>
Thanks!";

                    $mailData = array(
                        'to'           => $usersobj->email,
                        'subject'      => "Forgotten password retrieval",
                        'html_message' => $body,
                    );


                    $mail    = $emailer->sendMail($mailData);

                    if ($mail) {
                        $users->exchangeArray($formData);
                        $result = $this->getUsersTable()->savePasswordAccessToken($users);
                        $this->flashMessenger()->addMessage(array('success' => $this->confMessage->resetPassword));
                        return $this->redirect()->toRoute('users/forget-password');
                    } else {
                        $this->error[0] = array('error' => 'Can not sent email.');
                    }
                }
            }
            // $this->error[0] = array('error' => 'Invalid Information');
        }
        $vm             = new ViewModel(array(
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'form'          => $form,
            'error'         => $this->error,
        ));
        return $vm;
    }

    public function resetPasswordAction()
    {
        $this->layout('layout/small-layout');
        $emailer = new \HBMail\Controller\EmailersController();
        $id                     = (int) $this->params()->fromRoute('id', 0);
        $password_access_tocken = $this->params()->fromRoute('password_access_tocken', 0);

        if ($id > 0) {

        } else {
            $vm = new ViewModel(array(
                'flashMessages' => $this->flashMessenger()->getMessages(),
                'error'         => $this->error,
            ));
            return $vm;
        }

        $users = $this->getUsersTable()->getUsers($id);

        /**
         * Check Valid for access token
         */
        $token_valid = false;
        if ($users->password_access_tocken == $password_access_tocken) {
            $current_time = date('Y-m-d h:i:s');
            if (strtotime($users->access_token_valid_till) > strtotime($current_time)) {
                $token_valid = true;
            } else {
                $this->error[0] = array('error' => $this->confMessage->accessTokenSessionExpired);
            }
        } else {
            $this->error[0] = array('error' => $this->confMessage->invalidAccessToken);
        }


        if ($token_valid) {
            $form = new ResetPasswordForm();
            $form->setValidationGroup('id');
            $form->bind($users);
            $form->get('submit')->setAttribute('value', 'Reset Password');

            $request = $this->getRequest();
            if ($request->isPost()) {

                $users    = new Users();
                $form->setInputFilter($users->getInputFilter());
                $formData = $request->getPost();
                $valid    = true;

                /**
                 * Check whether userid is exist
                 */
                $usersobj = $this->getUsersTable()->getUsers($formData['id']);
                if (empty($usersobj) || $formData['id'] != $id) {
                    $valid          = false;
                    $this->error[0] = array('error' => $this->confMessage->unauthorizedUser);
                }
                /**
                 * Check whether password is valid
                 */
                if ($formData['password'] != $formData['repassword']) {
                    $valid          = false;
                    $this->error[0] = array('error' => $this->confMessage->passwordNotMatch);
                }

                if (strlen($formData['password']) < 6) {
                    $valid          = false;
                    $this->error[0] = array('error' => $this->confMessage->smallPassword);
                }

                if (empty($usersobj->email)) {
                    $valid          = false;
                    $this->error[0] = array('error' => $this->confMessage->emptyUsername);
                }

                $form->setData($formData);
                $form->setValidationGroup('id', 'password');

                if ($form->isValid()) {

                    if ($valid) {
                        $profiledata = $this->getViewUsersTable()->getViewUsers($formData['id']);
                        $body        = "
Hi " . trim($profiledata->first_name) . ",<br><br>Your password has been changed. <br /><br>

Go here: <a href='" . $emailer->siteURL() . "'>" . $emailer->siteURL() . "</a><br />
New Password: " . $formData['password'] . "<br /><br>

If you have any questions, please hesitate to contact us at <a href=\"mailto:team@hossbrag.com\">team@hossbrag.com</a>.";

                        $mailData = array(
                            'to'           => $usersobj->email,
                            'subject'      => "Password changed successfully!",
                            'html_message' => $body,
                        );


                        $mail    = $emailer->sendMail($mailData);

                        $users->exchangeArray($formData);
                        $this->getUsersTable()->changePassword($users);
                        $this->flashMessenger()->addMessage(array('success' => $this->confMessage->changePassword));

                        if ($mail) {
                            return $this->redirect()->toRoute('users/reset-password');
                        } else {
                            $this->error[0] = array('error' => 'Can not sent email.');
                        }
                    }
                }
                // $this->error[0] = array('error' => 'Invalid Information');
            }
        } else {
            $form = '';
        }
        $vm   = new ViewModel(array(
            'flashMessages'          => $this->flashMessenger()->getMessages(),
            'form'                   => $form,
            'error'                  => $this->error,
            'id'                     => $id,
            'password_access_tocken' => $password_access_tocken
        ));
        return $vm;
    }

    public function changePasswordAction()
    {
        $this->layout('layout/inner-layout');
        $emailer = new \HBMail\Controller\EmailersController();

        $id    = (int) $this->getCurrentUser()->id;
        $users = $this->getUsersTable()->getUsers($id);

        $form = new ChangePasswordForm();
        $form->setValidationGroup('id');
        $form->bind($users);
        $form->get('submit')->setAttribute('value', 'Change');

        $request = $this->getRequest();
        if ($request->isPost()) {

            $users    = new Users();
            $form->setInputFilter($users->getInputFilter());
            $formData = $request->getPost();
            $valid    = true;

            /**
             * Check whether userid is exist
             */
            $usersobj = $this->getUsersTable()->getUsers($formData['id']);
            if (empty($usersobj) || $formData['id'] != $id) {
                $valid          = false;
                $this->error[0] = array('error' => $this->confMessage->unauthorizedUser);
            }
            /**
             * Check whether password is valid
             */
            if (MD5($formData['cpassword']) != $usersobj->password) {
                $valid = false;

                $this->error[0] = array('error' => $this->confMessage->unauthorizedPassword);
            }

            if ($formData['password'] != $formData['repassword']) {
                $valid          = false;
                $this->error[0] = array('error' => $this->confMessage->passwordNotMatch);
            }

            if (strlen($formData['password']) < 6) {
                $valid          = false;
                $this->error[0] = array('error' => $this->confMessage->smallPassword);
            }

            if (empty($usersobj->email)) {
                $valid          = false;
                $this->error[0] = array('error' => $this->confMessage->emptyUsername);
            }

            $form->setData($formData);
            $form->setValidationGroup('id', 'password');

            if ($form->isValid()) {
                $profiledata = $this->getViewUsersTable()->getViewUsers($formData['id']);
                if ($valid) {
                    $body = "Hi " . trim($profiledata->first_name) . ",<br><br>Your password has been changed. <br /><br>

Go here: <a href='" . $emailer->siteURL() . "'>" . $emailer->siteURL() . "</a><br />
New Password: " . $formData['password'] . "<br /><br>

If you have any questions, please hesitate to contact us at <a href=\"mailto:team@hossbrag.com\">team@hossbrag.com</a>.";

                    $mailData = array(
                        'to'           => $usersobj->email,
                        'subject'      => "Password Changed successfully!",
                        'html_message' => $body,
                    );


                    $mail    = $emailer->sendMail($mailData);

                    $users->exchangeArray($formData);
                    $this->getUsersTable()->changePassword($users);
                    $this->flashMessenger()->addMessage(array('success' => $this->confMessage->changePassword));

                    if ($mail) {

                    } else {
                        $this->error[0] = array('error' => 'Can not sent email.');
                    }
                    return $this->redirect()->toRoute('users/change-password');
                }
            }
            // $this->error[0] = array('error' => 'Invalid Information');
        }
        $vm             = new ViewModel(array(
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'form'          => $form,
            'error'         => $this->error,
        ));
        return $vm;
    }

}
