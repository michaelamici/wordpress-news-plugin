<?php
/**
 * Simple unit test without WordPress dependency
 */

class TestSimple extends PHPUnit\Framework\TestCase
{
    public function testBasicFunctionality()
    {
        $this->assertTrue(true);
    }
    
    public function testPluginConstants()
    {
        // Test if our plugin constants are defined
        $this->assertTrue(defined("NEWS_PLUGIN_VERSION"));
        $this->assertEquals("1.0.0", NEWS_PLUGIN_VERSION);
    }
}
