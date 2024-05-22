<?php

class DuoUsers
{
    // array of users ['aurora-user-public-id' => 'duo-user-name']
    protected static $users = [

    ];

    public static function getUser($userPublicId) 
    {
        if (isset(self::$users[$userPublicId])) {
            return self::$users[$userPublicId];
        } else return null;
    }
}
