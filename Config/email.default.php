<?php

class EmailConfig
{

    public $default = array(
        'host' => 'mail.example.com',
        'port' => 20,
        'username' => 'username',
        'password' => 'password',
        'transport' => 'Smtp',
        'from' => array(
            'mail@example.com' => 'Demo Sender'
        )
    );
}
