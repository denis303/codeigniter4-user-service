<?php

namespace denis303\codeigniter4;

class UserService extends BaseUserService
{

    const NOT_REMEMBER_SESSION = 'user_not_remember';

    const NOT_REMEMBER_COOKIE = 'user_not_remember';

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
            $token = $this->_session->get(static::NOT_REMEMBER_SESSION);

            if ($token)
            {
                $cookieToken = $this->getNotRememberCookie();

                if ($cookieToken != $token)
                {
                    $this->logout();
                
                    $id = $this->_id;
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

            $this->_session->set(static::NOT_REMEMBER_SESSION, $token);
        
            $this->setNotRememberCookie($token);
        }
        else
        {
            $this->_session->remove(static::NOT_REMEMBER_SESSION);

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

        return get_cookie(static::NOT_REMEMBER_COOKIE);
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
        /*

        CodeIgniter 4 rc.1 does not send cookies where controller response is redirect?

        ToDo: This code is valid, but not working, check it later.

        helper('cookie');

        set_cookie(
            static::NOT_REMEMBER_COOKIE,
            $value,
            0,
            $this->_appConfig->cookieDomain,
            $this->_appConfig->cookiePath,
            $this->_appConfig->cookiePrefix,
            false, // only send over HTTPS
            false // hide from Javascript
        );

        */
        
        setcookie(
            $this->_appConfig->cookiePrefix . static::NOT_REMEMBER_COOKIE,
            $value,
            0,
            $this->_appConfig->cookiePath,
            $this->_appConfig->cookieDomain,
            false, // secure
            false // httponly
        );
    }

    public function deleteNotRememberCookie()
    {
        helper('cookie');

        delete_cookie(
            static::NOT_REMEMBER_COOKIE, 
            $this->_appConfig->cookieDomain, 
            $this->_appConfig->cookiePath, 
            $this->_appConfig->cookiePrefix
        );
    }

    public function logout()
    {
        parent::logout();

        $this->_session->remove(static::NOT_REMEMBER_SESSION);

        $this->deleteNotRememberCookie();
    }

}