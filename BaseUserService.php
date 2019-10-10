<?php
/**
 * @author denis303 <mail@denis303.com>
 * @license MIT
 * @link http://denis303.com
 */
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
            throw new Exception('Not implemented.');
        }

        $model = new $this->_modelClass;

        $primaryKey = $model->primaryKey;

        if (!$primaryKey)
        {
            throw new Exception($this->_modelClass . '::$primaryKey is required.');
        }

        if (is_array($user))
        {
            $id = $user[$primaryKey];
        }
        else
        {
            $id = $user->$primaryKey;
        }

        if (!$id)
        {
            throw new Exception('User ID not defined.');
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

    public function getModelClass()
    {
        return $this->_modelClass;
    }

    public function getUser()
    {
        if (!$this->_entity)
        {
            $id = $this->getId();
            
            if ($id)
            {
                $this->_entity = $this->findUserById($id);

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

    public function findUserById($id)
    {
        $model = new $this->_modelClass;

        return $model->find($id);
    }

}