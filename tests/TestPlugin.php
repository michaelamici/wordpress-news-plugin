<?php
/**
 * Test suite for NewsPlugin\Core\Plugin class
 * TDD tests covering singleton pattern, constants, and core functionality
 */

use NewsPlugin\Core\Plugin;

class TestPlugin extends PHPUnit\Framework\TestCase
{
    private Plugin $plugin;
    
    protected function setUp(): void
    {
        // Reset singleton for each test
        $reflection = new \ReflectionClass(Plugin::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        
        $this->plugin = Plugin::instance();
    }
    
    protected function tearDown(): void
    {
        // Clean up singleton
        $reflection = new \ReflectionClass(Plugin::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }

    public function testSingletonPattern()
    {
        $instance1 = Plugin::instance();
        $instance2 = Plugin::instance();
        
        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(Plugin::class, $instance1);
    }

    public function testPluginConstants()
    {
        $this->assertEquals('1.0.0', Plugin::VERSION);
        $this->assertEquals('news', Plugin::SLUG);
        $this->assertEquals(NEWS_PLUGIN_FILE, Plugin::FILE);
        $this->assertEquals(NEWS_PLUGIN_DIR, Plugin::DIR);
        $this->assertEquals(NEWS_PLUGIN_URL, Plugin::URL);
        $this->assertEquals(NEWS_PLUGIN_BASENAME, Plugin::BASENAME);
    }

    public function testComponentGettersReturnNullWhenNotInitialized()
    {
        // All component getters should return null until components are initialized
        $this->assertNull($this->plugin->getAdmin());
        $this->assertNull($this->plugin->getFrontend());
        $this->assertNull($this->plugin->getBlockManager());
        $this->assertNull($this->plugin->getWidgetManager());
        $this->assertNull($this->plugin->getRestApi());
    }

    public function testSingletonPreventsCloning()
    {
        $this->expectException(\Error::class);
        clone $this->plugin;
    }

    public function testSingletonPreventsUnserialization()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot unserialize singleton');
        
        $serialized = serialize($this->plugin);
        unserialize($serialized);
    }

    public function testPluginFileStructure()
    {
        // Test that plugin file path is correctly set
        $this->assertStringEndsWith('news.php', Plugin::FILE);
        $this->assertStringEndsWith('news.php', Plugin::BASENAME);
        $this->assertIsString(Plugin::DIR);
        $this->assertIsString(Plugin::URL);
    }

    public function testPluginVersionConsistency()
    {
        // Version should be consistent across constants and class
        $this->assertEquals(NEWS_PLUGIN_VERSION, Plugin::VERSION);
        $this->assertEquals('1.0.0', Plugin::VERSION);
    }

    public function testPluginSlugConsistency()
    {
        // Slug should be consistent
        $this->assertEquals(NEWS_PLUGIN_SLUG, Plugin::SLUG);
        $this->assertEquals('news', Plugin::SLUG);
    }
}
