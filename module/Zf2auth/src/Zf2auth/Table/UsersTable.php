<?php

namespace Zf2auth\Table;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zf2auth\Entity\Users;

class UsersTable extends AbstractTableGateway
{

    protected $table = 'users';

    public function __construct(Adapter $adapter)
    {
        $this->adapter            = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Users());

        $this->initialize();
    }

    public function fetchAll(Select $select = null)
    {
        if (null === $select)
            $select    = new Select();
        $select->from($this->table);
        $resultSet = $this->selectWith($select);
        $resultSet->buffer();
        return $resultSet;
    }

    public function getUsers($id)
    {
        $id     = (int) $id;
        $rowset = $this->select(array('id' => $id));
        $row    = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveUsers(Users $formdata)
    {
        $data = array(
            'username'         => $formdata->username,
            'email'            => $formdata->email,
            'password'         => $formdata->password,
            'email_check_code' => $formdata->email_check_code,
            'is_disabled'      => $formdata->is_disabled,
            'created'          => $formdata->created,
            'modified'         => $formdata->modified,
        );

        $id = (int) $formdata->id;
        if ($id == 0) {
            $this->insert($data);
        } else {
            if ($this->getUsers($id)) {
                $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteUsers($id)
    {
        $this->delete(array('id' => $id));
    }

    public function dropdownUsers(Select $select = null)
    {
        if (null === $select)
            $select    = new Select();
        $select->from($this->table);
        $resultSet = $this->selectWith($select);
        $resultSet->buffer();

        $options = array();
        $options[''] = '--- Users ---';
        if (count($resultSet) > 0) {
            foreach ($resultSet as $row)
                $options[$row->getId()] = $row->getUsername();
        }
        return $options;
    }


    /**
     * Custom finction
     */
    public function getUsersByUserName($username)
    {
        $rowset = $this->select(array('username' => $username));
        $row    = $rowset->current();
        if (!$row) {
            return false;
        }
        return $row;
    }

    public function getUsersByEmail($email)
    {
        $rowset = $this->select(array('email' => $email));
        $row    = $rowset->current();
        if (!$row) {
            return false;
        }
        return $row;
    }


    public function ConfirmEmailCheckCode(Users $users)
    {

        $data = array(
            'email_check_code' => $users->email_check_code,
            'is_disabled'      => 0,
        );

        $id = (int) $users->id;
        if ($id == 0) {
            $this->insert($data);
        } else {
            if ($this->getUsers($id)) {
                $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }


    public function saveRegistration(Users $users, $formData)
    {
        $adapter          = $this->adapter;
//        echo "<pre>";
//        print_r($formData);
//        echo "</pre>";
//        die();
        $email_check_code = $this->getEmailCheckCode();
        $data             = array(
            'username'         => $users->email,
            'email'            => $users->email,
            'password'         => md5($users->password),
            'email_check_code' => $email_check_code,
            'is_disabled'      => 1,
            'created'          => date('Y-m-d H:i:s'),
            'modified'         => date('Y-m-d H:i:s'),
        );

        $id = (int) $users->id;
        if ($id == 0) {
            unset($data['modified']);
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $this->insert($data);
            $user_id = $this->lastInsertValue;

            /**
             * Insert Profile Table
             */
            $profiledata = array(
                'user_id'    => $user_id,
                'first_name' => $formData['first_name'],
                'last_name'  => $formData['last_name'],
                'created'    => date('Y-m-d H:i:s'),
            );
            $sql         = new Sql($this->adapter);
            $insert      = $sql->insert('profiles')->values(
                    $profiledata
            );
            $statement   = $sql->getSqlStringForSqlObject($insert);
            $resultSet   = $adapter->query($statement, $adapter::QUERY_MODE_EXECUTE);
            $resultSet->buffer();

            /**
             * Insert User Role Table
             */
            $userrolesdata      = array(
                'user_id' => $user_id,
                'role_id' => 2,
            );
            $sqluserroles       = new Sql($this->adapter);
            $insertuserroles    = $sqluserroles->insert('user_roles')->values(
                    $userrolesdata
            );
            $statementuserroles = $sqluserroles->getSqlStringForSqlObject($insertuserroles);
            $resultSetuserroles = $adapter->query($statementuserroles, $adapter::QUERY_MODE_EXECUTE);
            $resultSetuserroles->buffer();
            $this->adapter->getDriver()->getConnection()->commit();
        } else {
            unset($data['created']);
            if ($this->getUsers($id)) {
                $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function resetPassword(Users $users)
    {
        $adapter = $this->adapter;
//        echo "<pre>";
//        print_r($users);
//        echo "</pre>";
//        die();
        $data    = array(
            'password' => md5($users->password),
        );

        $id = (int) $users->id;
        if ($id > 0) {
            if ($this->getUsers($id)) {
                return $result = $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function savePasswordAccessToken(Users $users)
    {
        $adapter = $this->adapter;
//        echo "<pre>";
//        print_r($users);
//        echo "</pre>";
//        die();
        $data    = array(
            'password_access_tocken'  => $users->password_access_tocken,
            'access_token_valid_till' => $users->access_token_valid_till,
        );

        $id = (int) $users->id;
        if ($id > 0) {
            if ($this->getUsers($id)) {
                return $result = $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function changePassword(Users $users)
    {
        $adapter = $this->adapter;
//        echo "<pre>";
//        print_r($users);
//        echo "</pre>";
//        die();
        $data    = array(
            'password' => md5($users->password),
            'password_access_tocken'  => '',
            'access_token_valid_till' => '',
        );

        $id = (int) $users->id;
        if ($id > 0) {
            if ($this->getUsers($id)) {
                $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function fetchAllByIdentity($identity, $identitykey = 'email')
    {
        $adapter = $this->adapter;
        $select  = new Select();
        $select->from($this->table);
        $select->join('profiles', 'profiles.user_id = users.id', array('profile_id' => 'id', 'first_name', 'last_name', 'country_id'), 'left');
        $where   = new \Zend\Db\Sql\Where();
        $where->addPredicate(
                new \Zend\Db\Sql\Predicate\Expression("(users." . $identitykey . " = '" . $identity . "')")
        );
        if (!empty($where)) {
            $select->where($where);
        }

//        echo "<pre>";
//        echo $select->getSqlString();
//        die();

        $sql       = new Sql($adapter);
        $statement = $sql->getSqlStringForSqlObject($select);
        $resultSet = $adapter->query($statement, $adapter::QUERY_MODE_EXECUTE);
        $resultSet->buffer();
        return $resultSet;
    }

    /**
     * Generate E-mail check code
     */
    private function getEmailCheckCode()
    {
        // return md5(date('Y-m-d H:i:s'));
        return mt_rand(10000000, 99999999);
    }

    /**
     * Generate Password
     * @param type $l
     * @param type $c
     * @param type $n
     * @param type $s
     * @return boolean
     */
    public function generatePassword($l = 8, $c = 0, $n = 0, $s = 0)
    {
        // get count of all required minimum special chars
        $count = $c + $n + $s;
        $out   = "";
        // sanitize inputs; should be self-explanatory
        if (!is_int($l) || !is_int($c) || !is_int($n) || !is_int($s)) {
            trigger_error('Argument(s) not an integer', E_USER_WARNING);
            return false;
        } elseif ($l < 0 || $l > 20 || $c < 0 || $n < 0 || $s < 0) {
            trigger_error('Argument(s) out of range', E_USER_WARNING);
            return false;
        } elseif ($c > $l) {
            trigger_error('Number of password capitals required exceeds password length', E_USER_WARNING);
            return false;
        } elseif ($n > $l) {
            trigger_error('Number of password numerals exceeds password length', E_USER_WARNING);
            return false;
        } elseif ($s > $l) {
            trigger_error('Number of password capitals exceeds password length', E_USER_WARNING);
            return false;
        } elseif ($count > $l) {
            trigger_error('Number of password special characters exceeds specified password length', E_USER_WARNING);
            return false;
        }

        // all inputs clean, proceed to build password
        // change these strings if you want to include or exclude possible password characters
        $chars = "abcdefghijklmnopqrstuvwxyz";
        $caps  = strtoupper($chars);
        $nums  = "0123456789";
        $syms  = "!@#$%^&*()-+?";

        // build the base password of all lower-case letters
        for ($i = 0; $i < $l; $i++) {
            $out .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        // create arrays if special character(s) required
        if ($count) {
            // split base password to array; create special chars array
            $tmp1 = str_split($out);
            $tmp2 = array();

            // add required special character(s) to second array
            for ($i = 0; $i < $c; $i++) {
                array_push($tmp2, substr($caps, mt_rand(0, strlen($caps) - 1), 1));
            }
            for ($i = 0; $i < $n; $i++) {
                array_push($tmp2, substr($nums, mt_rand(0, strlen($nums) - 1), 1));
            }
            for ($i = 0; $i < $s; $i++) {
                array_push($tmp2, substr($syms, mt_rand(0, strlen($syms) - 1), 1));
            }

            // hack off a chunk of the base password array that's as big as the special chars array
            $tmp1 = array_slice($tmp1, 0, $l - $count);
            // merge special character(s) array with base password array
            $tmp1 = array_merge($tmp1, $tmp2);
            // mix the characters up
            shuffle($tmp1);
            // convert to string for output
            $out  = implode('', $tmp1);
        }

        return $out;
    }

    public function isExistEmail($email, $id = Null)
    {
        $adapter = $this->adapter;
        $select  = new Select();
        $select->from($this->table);
        $select->where(array('email' => $email));
        if (!empty($id)) {
            $select->where("`id` != {$id}");
        }
        $sql       = new Sql($adapter);
        $statement = $sql->getSqlStringForSqlObject($select);
        $resultSet = $adapter->query($statement, $adapter::QUERY_MODE_EXECUTE);
        $resultSet->buffer();
        return $resultSet;
    }

    public function changeEmial($email, $id)
    {
        $data = array(
            'username' => $email,
            'email'    => $email
        );
        if ($id > 0) {
            if ($this->getUsers($id)) {
                $rusult = $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Your id does not exist');
            }
        }

        return $rusult;
    }

    public function emailValidationCk($email)
    {
        if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

}

