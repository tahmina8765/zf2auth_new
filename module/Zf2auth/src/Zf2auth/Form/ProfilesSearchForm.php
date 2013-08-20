<?php
namespace Zf2auth\Form;

use Zend\Form\Form;
use \Zend\Form\Element;

class ProfilesSearchForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('profiles');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('method', 'post');


	
        $user_id = new Element\Text('user_id');
        $user_id->setLabel('User Id')
                ->setAttribute('class', 'required')
                ->setAttribute('placeholder', 'User Id');
        

        $first_name = new Element\Text('first_name');
        $first_name->setLabel('First Name')
                ->setAttribute('class', 'required')
                ->setAttribute('placeholder', 'First Name');
        

        $last_name = new Element\Text('last_name');
        $last_name->setLabel('Last Name')
                ->setAttribute('class', 'required')
                ->setAttribute('placeholder', 'Last Name');
        

        $created = new Element\Text('created');
        $created->setLabel('Created')
                ->setAttribute('class', 'required')
                ->setAttribute('placeholder', 'Created');
        

        $modified = new Element\Text('modified');
        $modified->setLabel('Modified')
                ->setAttribute('class', 'required')
                ->setAttribute('placeholder', 'Modified');
        



        $submit = new Element\Submit('submit');
        $submit->setValue('Search')
                ->setAttribute('class', 'btn btn-primary');


        $this->add($user_id);
	$this->add($first_name);
	$this->add($last_name);
	$this->add($created);
	$this->add($modified);
	
        $this->add($submit);

    }
}


    