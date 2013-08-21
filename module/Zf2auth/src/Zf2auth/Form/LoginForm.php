<?php

namespace Zf2auth\Form;

use Zend\Form\Form;
use \Zend\Form\Element;

class LoginForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('users');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('method', 'post');

        $email = new Element\Email('email');
        $email->setLabel('Email')
                ->setAttribute('class', 'required')
                ->setAttribute('maxlength', '100')
                ->setAttribute('placeholder', 'Email');


        $password = new Element\Password('password');
        $password->setLabel('Password')
                ->setAttribute('class', 'required')
                ->setAttribute('maxlength', '100')
                ->setAttribute('placeholder', 'Password');

        $rememberme = new Element\Checkbox('rememberme');
        $rememberme->setLabel('remember me')
                ->setAttribute('class', '')
                ->setValue('1');




        $submit = new Element\Submit('submit');
        $submit->setValue('Log in')
                ->setAttribute('class', 'btn btn-success btn-hossbrag header-btn');

        $this->add($email);
        $this->add($password);
        $this->add($rememberme);
        $this->add($submit);
    }

}

