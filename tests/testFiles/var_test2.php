<?php

namespace tests\testFiles;

class var_test2
{

    public function __construct()
    {
        $name = 'test';
        $result = $this->{'validate_' . $name}();
    }
}
