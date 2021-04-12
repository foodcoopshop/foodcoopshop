<?php

use Cake\TestSuite\TestCase;
use Cake\TestSuite\IntegrationTestTrait;

class FormProtectorTest extends TestCase
{

    use IntegrationTestTrait;

    public function testFormWithFormProtector()
    {
        $this->enableSecurityToken();
        $data = [];
        $data['Customers']['firstname'] = 'Mario';
        $this->post('/pages/form', $data);
        $this->assertFlashMessage('ok');
    }

}

?>

