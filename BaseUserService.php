<?php

namespace denis303\codeigniter4;

use Exception;

class BaseUserService
{

    const ID_SESSION = 'user_id';

    protected $_modelClass;

    protected $_session;

    protected $_id;

    protected $_entity;    

    public function __construct(string $modelClass, object $session)
    {
        $this->_modelClass = $modelClass;

        $this->_session = $session;
    }

    public function login($user, bool $rememberMe = true, &$error = null)
    {
        if (!$user)
        {
            throw new Exception('User not defined.');
        }

        if (!$rememberMe)
        {
            throw new Exception('Not implemended.');
        }

        $model = new $this->_modelClass;

        $primaryKey = $model->primaryKey;

        if (is_array($user))
        {
            $id = $user[$primaryKey];
        }
        else
        {
            $id = $user->$primaryKey;
        }

        $this->_session->set(static::ID_SESSION, $id);

        $this->_id = $id;

        $this->_entity = $user;

        return true;
    }

    public function getId()
    {
        if (!$this->_id)
        {
            $this->_id = $this->_session->get(static::ID_SESSION);
        }

        return $this->_id;
    }

    public function isGuest() : bool
    {
        return $this->getUser() ? false : true;
    }

    public function getUser()
    {
        if (!$this->_entity)
        {
            $id = $this->getId();
            
            if ($id)
            {
                $model = new $this->_modelClass;

                $this->_entity = $model->find($id);

                if (!$this->_entity)
                {
                    $this->logout();
                }
            }
        }

        return $this->_entity;
    }    

    public function logout()
    {
        $this->_session->remove(static::ID_SESSION);
    
        $this->_id = null;

        $this->_entity = null;
    }    

}