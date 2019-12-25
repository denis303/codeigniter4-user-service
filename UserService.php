<?php
/**
 * @author denis303 <mail@denis303.com>
 * @license MIT
 * @link http://denis303.com
 */
namespace denis303\codeigniter4;

use Denis303\CodeIgniter\NotRememberMe;

class UserService extends BaseUserService
{

    const NOT_REMEMBER_SUFFIX = '_not_remember';

    protected $_notRememberMe;

    public function __construct(string $modelClass, object $session)
    {
        parent::__construct($modelClass, $session);

        $this->_notRememberMe = new NotRememberMe(static::ID_SESSION . static::NOT_REMEMBER_SUFFIX);
    }

    public function getId()
    {
        $id = parent::getId();

        if ($id)
        {
            if (!$this->_notRememberMe->validateToken())
            {
                $this->logout();
            
                $this->_notRememberMe->deleteToken();

                return null;
            }
        }

        return $id;
    }

    public function login($user, $rememberMe = true, &$error = null)
    {
        $return = parent::login($user, true, $error);

        if (!$rememberMe)
        {        
            $this->_notRememberMe->createToken();
        }
        else
        {
            $this->_notRememberMe->deleteToken();
        }

        return $return;
    }
 
    public function logout()
    {
        parent::logout();

        $this->_notRememberMe->deleteToken();
    }

}