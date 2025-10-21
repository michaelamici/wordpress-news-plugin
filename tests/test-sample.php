<?php
/**
 * Sample test case
 */

class SampleTest extends WP_UnitTestCase {

    public function test_sample() {
        // Replace this with some actual testing code.
        $this->assertTrue(true);
    }

    public function test_plugin_loaded() {
        $this->assertTrue(class_exists("NewsPlugin\Core\Plugin"));
    }

    public function test_plugin_instance() {
        $plugin = \NewsPlugin\Core\Plugin::instance();
        $this->assertInstanceOf("NewsPlugin\Core\Plugin", $plugin);
    }
}
