<?php

namespace AstralWeb\LibUSPS\Web\Service;

class Client
{
    protected static $_clientsRepo = [];

    protected $_userID;

    /**
     * @param int $userId
     * @return self
     */
    public function setUserID($userId)
    {
        $this->_userID = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserID()
    {
        return $this->_userID;
    }

    /**
     * @param string $key
     * @return \AstralWeb\LibUSPS\Web\Service\Client | null
     */
    public static function getInstanceByKey($key)
    {
        if (isset(static::$_clientsRepo[$key])) {

            return static::$_clientsRepo[$key];
        }

        return null;
    }

    /**
     * @param string $key
     * @param \AstralWeb\LibUSPS\Web\Service\Client $client
     * @return \AstralWeb\LibUSPS\Web\Service\Client
     */
    public static function setInstanceToKey($key, \AstralWeb\LibUSPS\Web\Service\Client $client)
    { 
        static::$_clientsRepo[$key] = $client;

        return static::$_clientsRepo[$key];
    }
}
