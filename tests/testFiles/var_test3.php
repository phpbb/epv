<?php

namespace testFiles;

class var_test3
{

    public function __construct()
    {
        $name = 'test';
        $result1 = $this->{"validate_$name"}();
		$result2 = $this->{$name === 'test' ? 'callBack1' : 'callBack2'}();
    }
}
