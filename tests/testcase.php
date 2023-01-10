<?php

defined('MOODLE_INTERNAL') || die();

abstract class local_behatsnapshots_testcase extends advanced_testcase {

    public function multiline(string $text): string {
        return implode("\n", array_slice(array_map('trim', explode("\n", $text)), 1));
    }

}
