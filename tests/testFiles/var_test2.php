<?php

namespace tests\testFiles;

class var_test2
{

    public function __construct()
    {
        $name = 'test';
        $result1 = $this->{'validate1_' . $name}();
		$result2 = $this->{"validate2_$name"}();
		$result3 = $this->{$name === 'test' ? 'callBack1' : 'callBack2'}();

	}
}
