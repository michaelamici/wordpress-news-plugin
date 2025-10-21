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

    public function test_block_manager_exists() {
        $this->assertTrue(class_exists("NewsPlugin\Blocks\BlockManager"));
    }

    public function test_plugin_constants() {
        $this->assertTrue(defined('NEWS_PLUGIN_VERSION'));
        $this->assertTrue(defined('NEWS_PLUGIN_FILE'));
        $this->assertTrue(defined('NEWS_PLUGIN_DIR'));
    }
}
