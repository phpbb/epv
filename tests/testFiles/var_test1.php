<?php

namespace tests\testFiles;

class var_test1
{

    private $foo;
    private $action;
    private $cat_id;

    public function __construct()
    {
        $bar = 'test';
        $id = 'test';
        $this->foo->{$bar}($id);

        $this->foo->{$this->action}($this->cat_id);
    }
}
