<?php

namespace Detain\MyAdminVpsIps\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for verifying the existence and structure of package files.
 */
class FileExistenceTest extends TestCase
{
    /**
     * @var string
     */
    private $packageRoot;

    protected function setUp(): void
    {
        $this->packageRoot = dirname(__DIR__);
    }

    /**
     * Tests that the composer.json file exists.
     */
    public function testComposerJsonExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/composer.json');
    }

    /**
     * Tests that composer.json contains valid JSON.
     */
    public function testComposerJsonIsValidJson(): void
    {
        $contents = file_get_contents($this->packageRoot . '/composer.json');
        $decoded = json_decode($contents, true);
        $this->assertNotNull($decoded, 'composer.json is not valid JSON');
    }

    /**
     * Tests that composer.json has the correct package name.
     */
    public function testComposerJsonHasCorrectName(): void
    {
        $composer = json_decode(file_get_contents($this->packageRoot . '/composer.json'), true);
        $this->assertSame('detain/myadmin-ips-vps-addon', $composer['name']);
    }

    /**
     * Tests that composer.json has PSR-4 autoload configuration.
     */
    public function testComposerJsonHasPsr4Autoload(): void
    {
        $composer = json_decode(file_get_contents($this->packageRoot . '/composer.json'), true);
        $this->assertArrayHasKey('autoload', $composer);
        $this->assertArrayHasKey('psr-4', $composer['autoload']);
        $this->assertArrayHasKey('Detain\\MyAdminVpsIps\\', $composer['autoload']['psr-4']);
        $this->assertSame('src/', $composer['autoload']['psr-4']['Detain\\MyAdminVpsIps\\']);
    }

    /**
     * Tests that the src directory exists.
     */
    public function testSrcDirectoryExists(): void
    {
        $this->assertDirectoryExists($this->packageRoot . '/src');
    }

    /**
     * Tests that Plugin.php exists in the src directory.
     */
    public function testPluginPhpExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/src/Plugin.php');
    }

    /**
     * Tests that vps_ips.php exists in the src directory.
     */
    public function testVpsIpsPhpExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/src/vps_ips.php');
    }

    /**
     * Tests that README.md exists.
     */
    public function testReadmeExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/README.md');
    }

    /**
     * Tests that the license in composer.json is LGPL-2.1-only.
     */
    public function testComposerJsonLicense(): void
    {
        $composer = json_decode(file_get_contents($this->packageRoot . '/composer.json'), true);
        $this->assertSame('LGPL-2.1-only', $composer['license']);
    }

    /**
     * Tests that composer.json has require-dev with phpunit.
     */
    public function testComposerJsonHasPhpunit(): void
    {
        $composer = json_decode(file_get_contents($this->packageRoot . '/composer.json'), true);
        $this->assertArrayHasKey('require-dev', $composer);
        $this->assertArrayHasKey('phpunit/phpunit', $composer['require-dev']);
    }

    /**
     * Tests that composer.json type is myadmin-plugin.
     */
    public function testComposerJsonType(): void
    {
        $composer = json_decode(file_get_contents($this->packageRoot . '/composer.json'), true);
        $this->assertSame('myadmin-plugin', $composer['type']);
    }

    /**
     * Tests that composer.json requires symfony/event-dispatcher.
     */
    public function testComposerJsonRequiresEventDispatcher(): void
    {
        $composer = json_decode(file_get_contents($this->packageRoot . '/composer.json'), true);
        $this->assertArrayHasKey('symfony/event-dispatcher', $composer['require']);
    }

    /**
     * Tests that composer.json has autoload-dev section.
     */
    public function testComposerJsonHasAutoloadDev(): void
    {
        $composer = json_decode(file_get_contents($this->packageRoot . '/composer.json'), true);
        $this->assertArrayHasKey('autoload-dev', $composer);
        $this->assertArrayHasKey('psr-4', $composer['autoload-dev']);
        $this->assertArrayHasKey('Detain\\MyAdminVpsIps\\Tests\\', $composer['autoload-dev']['psr-4']);
    }

    /**
     * Tests that phpunit.xml.dist exists.
     */
    public function testPhpunitXmlDistExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/phpunit.xml.dist');
    }
}
