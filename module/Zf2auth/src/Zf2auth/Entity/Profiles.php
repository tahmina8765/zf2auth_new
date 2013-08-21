<?php

namespace Zf2auth\Entity;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Profiles implements InputFilterAwareInterface
{

    public $user_id;
    public $first_name;
    public $last_name;
    public $created;
    public $modified;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id         = (isset($data['id'])) ? $data['id'] : null;
        $this->user_id    = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->first_name = (isset($data['first_name'])) ? $data['first_name'] : null;
        $this->last_name  = (isset($data['last_name'])) ? $data['last_name'] : null;
        $this->created    = (isset($data['created'])) ? $data['created'] : null;
        $this->modified   = (isset($data['modified'])) ? $data['modified'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    public function getModified()
    {
        return $this->modified;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                        'name'     => 'id',
                        'required' => true,
                        'filters'  => array(
                            array('name' => 'Int'),
                        ),
            )));


            $inputFilter->add($factory->createInput(array(
                        'name'     => 'user_id',
                        'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                        'name'     => 'first_name',
                        'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                        'name'     => 'last_name',
                        'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                        'name'     => 'created',
                        'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                        'name'     => 'modified',
                        'required' => true,
            )));


            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}