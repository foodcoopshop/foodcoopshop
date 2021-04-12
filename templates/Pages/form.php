<?php

use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);

echo $this->Form->create($customer);
echo $this->Form->control('Customers.firstname');
echo $this->Form->submit();
echo $this->Form->end();

?>