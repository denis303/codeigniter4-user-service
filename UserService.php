<?php

namespace denis303\codeigniter4;

use Exception;
use CodeIgniter\Model;

class UserService
{

    const SESSION_INDEX = 'user_id';

    protected $_modelClass;

    protected $_session;

    protected $_id;

    protected $_entity;

    protected $_model;

    public function __construct(object $session, string $modelClass)
    {
        $this->_session = $session;

        $this->_modelClass = $modelClass;
    }

    public function getModel()
    {
        if (!$this->_model)
        {
            $modelClass = $this->_modelClass;

            $this->_model = new $modelClass;
        }

        return $this->_model;
    }

    public function getEntity()
    {
        if (!$this->_entity)
        {
            $id = $this->getId();
            
            if ($id)
            {
                $model = $this->getModel();

                $this->_entity = $model->find($id);

                if (!$this->_entity)
                {
                    $this->logout();
                }
            }
        }

        return $this->_entity;
    }

    public function getId()
    {
        if (!$this->_id)
        {
            $this->_id = $this->_session->get(static::SESSION_INDEX);
        }

        return $this->_id;
    }

    public function isGuest() : bool
    {
        return $this->getEntity() ? false : true;
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login($user, $rememberMe = false, & $error = null)
    {
        if (!$user)
        {
            throw new Exception('User not found.');
        }

        $model = $this->getModel();

        $primaryKey = $model->primaryKey;

        $returnType = $model->returnType;

        if ($returnType == 'array')
        {
            $id = $user[$primaryKey];
        }
        else
        {
            $id = $user->$primaryKey;
        }

        $this->_session->set(static::SESSION_INDEX, $id);

        $this->_id = $id;

        $this->_entity = $user;

        return true;
    }

    public function logout()
    {
        $this->_session->remove(static::SESSION_INDEX);
    
        $this->_id = null;

        $this->_entity = null;
    }

}