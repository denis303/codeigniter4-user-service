<?php
/**
 * @author denis303 <mail@denis303.com>
 * @license MIT
 * @link http://denis303.com
 */
namespace denis303\codeigniter4;

class UserService extends BaseUserService
{

    const NOT_REMEMBER_SUFFIX = '_not_remember';

    protected $_appConfig;

    public function __construct(string $modelClass, object $session, object $appConfig)
    {
        parent::__construct($modelClass, $session);

        $this->_appConfig = $appConfig;
    }

    public function getId()
    {
        $id = parent::getId();

        if ($id)
        {
            $token = $this->_session->get(static::ID_SESSION . static::NOT_REMEMBER_SUFFIX);

            if ($token)
            {
                $cookieToken = $this->getNotRememberCookie();

                if ($cookieToken != $token)
                {
                    $this->logout();
                
                    return null;
                }
            }
        }

        return $id;
    }

    public function login($user, $rememberMe = true, &$error = null)
    {
        $return = parent::login($user, true, $error);

        if (!$rememberMe)
        {
            $token = $this->generateToken();

            $this->_session->set(static::ID_SESSION . static::NOT_REMEMBER_SUFFIX, $token);
        
            $this->setNotRememberCookie($token);
        }
        else
        {
            $this->_session->remove(static::ID_SESSION . static::NOT_REMEMBER_SUFFIX);

            $this->deleteNotRememberCookie();
        }

        return $return;
    }

    public function generateToken()
    {
        return md5(time() . rand(0, PHP_INT_MAX)); 
    }

    public function getNotRememberCookie()
    {
        helper('cookie');

        return get_cookie(static::ID_SESSION . static::NOT_REMEMBER_SUFFIX);
    }

    /**
     *  Set "not remember me" cookie
     *
     *  Not working in Chrome, where:
     *
     *  1. On Startup = Continue where you left off
     *  2. Continue running background apps when Google Chrome is closed = On
     *
     */
    public function setNotRememberCookie(string $value)
    {
        helper('cookie');

        set_cookie(
            static::ID_SESSION . static::NOT_REMEMBER_SUFFIX,
            $value,
            0,
            $this->_appConfig->cookieDomain,
            $this->_appConfig->cookiePath,
            $this->_appConfig->cookiePrefix,
            false, // only send over HTTPS
            false // hide from Javascript
        );
    }

    public function deleteNotRememberCookie()
    {
        helper('cookie');

        delete_cookie(
            static::ID_SESSION . static::NOT_REMEMBER_SUFFIX, 
            $this->_appConfig->cookieDomain, 
            $this->_appConfig->cookiePath, 
            $this->_appConfig->cookiePrefix
        );
    }

    public function logout()
    {
        parent::logout();

        $this->_session->remove(static::ID_SESSION . static::NOT_REMEMBER_SUFFIX);

        $this->deleteNotRememberCookie();
    }

}