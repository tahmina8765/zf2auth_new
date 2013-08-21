<?php

namespace Zf2auth\Form;

use Zend\Form\Form;
use \Zend\Form\Element;

class ResetPasswordForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('users');
        $this->setAttribute('class', '');
        $this->setAttribute('method', 'post');

        $id = new Element\Hidden('id');
        $id->setAttribute('class', 'primarykey');

        $password_access_tocken = new Element\Hidden('password_access_tocken');



        $password = new Element\Password('password');
        $password->setLabel('New Password')
                ->setAttribute('class', 'required input-medium')
                ->setAttribute('id', 'inputPassword')
                ->setAttribute('placeholder', 'New Password');

        $repassword = new Element\Password('repassword');
        $repassword->setLabel('Re-type Password')
                ->setAttribute('class', 'required input-medium')
                ->setAttribute('placeholder', 'Re-type Password');


        $submit = new Element\Submit('submit');
        $submit->setValue('Change')
                ->setAttribute('class', 'btn btn-primary right btn-hossbrag header-btn');

        $this->add($id);
        $this->add($password_access_tocken);
        $this->add($password);
        $this->add($repassword);
        $this->add($submit);
    }

}

