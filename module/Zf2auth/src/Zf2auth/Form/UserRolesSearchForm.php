<?php

namespace Zf2auth\Form;

use Zend\Form\Form;
use \Zend\Form\Element;

class UserRolesSearchForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('user_roles');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('method', 'post');



        $user_id = new Element\Select('user_id');
        $user_id->setLabel('User')
                ->setAttribute('class', 'required')
                ->setOptions(array())
                ->setDisableInArrayValidator(true)
                ->setAttribute('placeholder', 'User');


        $role_id = new Element\Select('role_id');
        $role_id->setLabel('Role')
                ->setAttribute('class', 'required')
                ->setOptions(array())
                ->setDisableInArrayValidator(true)
                ->setAttribute('placeholder', 'Role');




        $submit = new Element\Submit('submit');
        $submit->setValue('Search')
                ->setAttribute('class', 'btn btn-primary');


        $this->add($user_id);
        $this->add($role_id);

        $this->add($submit);
    }

}

