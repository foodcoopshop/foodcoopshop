<?php

namespace App\Network;

use Cake\Http\Session;
use Cake\Utility\Hash;

class AppSession extends Session
{

    /**
     * quite tricky and hacky to get session into AppTable
     * {@inheritDoc}
     * @see \Cake\Http\Session::read()
     */
    public function read($name = null)
    {

        if (!isset($_SESSION)) {
            return null;
        }

        if ($name === null) {
            return isset($_SESSION) ? $_SESSION : [];
        }

        return Hash::get($_SESSION, $name);
    }
}
