<?php

namespace testFiles;

class var_test3
{

    public function __construct()
    {
        $name = 'test';
        $result = $this->{"validate_$name"}();
    }
}
