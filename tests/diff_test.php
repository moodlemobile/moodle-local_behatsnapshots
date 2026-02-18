<?php

use local_behatsnapshots\diff;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/testcase.php');

final class diff_test extends local_behatsnapshots_testcase {

    public function test_diffs_html() {
        $this->assertNull(diff::html('<div>Hello</div>', '<div>Hello</div>'));
        $this->assertNull(diff::html('<div class="foo bar">Hello</div>', '<div class="bar foo">Hello</div>'));
        $this->assertEquals(
            $this->multiline("
                #1 (line 2)
                < <div>There</div>
                ---
                > <div>Here</div>
            "),
            diff::html("<div>Hello</div>\n<div>There</div>", "<div>Hello</div>\n<div>Here</div>")
        );
    }

    public function test_diffs_text() {
        $this->assertNull(diff::text('foo bar', 'foo bar'));
        $this->assertEquals(
            "< foo bar\n---\n> foo baz",
            diff::text('foo bar', 'foo baz')
        );
    }

    public function test_diffs_text_using_regexps() {
        $this->assertNull(diff::text('foo ba[[[rz]]]', 'foo baz'));
        $this->assertNull(diff::text('foo ba[[[rz]]]', 'foo bar'));
        $this->assertNull(diff::text('f[[o+]] [[(ba[rz]\s?)+]]', 'foo bar baz'));
        $this->assertNull(diff::text('<div>[[.*]]</div>', '<div>foobar</div>'));
        $this->assertEquals(
            "< foo ba[[[rz]]]\n---\n> foo bar baz",
            diff::text('foo ba[[[rz]]]', 'foo bar baz')
        );
        $this->assertEquals(
            "< fo[o] ba[[[rz]]]\n---\n> foo bar",
            diff::text('fo[o] ba[[[rz]]]', 'foo bar')
        );
    }

}
