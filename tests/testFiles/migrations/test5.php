<?php

namespace migrations;

class test5 {
    public static function depends_on() {
        return array(
            'phpbb\error',
        );
    }
}