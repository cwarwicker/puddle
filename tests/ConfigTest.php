<?php

use PHPUnit\Framework\TestCase;
use Puddle\Config;

require_once __DIR__ . '/../vendor/autoload.php';

class ConfigTest extends TestCase
{

    /**
     * Test passing an invalid file path when loading Config object
     * @return void
     */
    public function testLoadInvalidPath(): void {

        $path = '/fake/path/to/file.json';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');
        Config::load($path);

    }

    /**
     * Test passing an invalid file when loading Config object
     * @return void
     */
    public function testLoadInvalidJSONFile(): void {

        $path = __DIR__ . '/fixtures/invalid.json';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not contain valid JSON');
        Config::load($path);

    }

    /**
     * Test passing a JSON file with unsupported properties when loading the Config object
     * @return void
     */
    public function testLoadInvalidDataFile(): void {

        $path = __DIR__ . '/fixtures/invalidconfig.json';
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('is not a valid config.json property');
        Config::load($path);

    }

    /**
     * Test passing a valid file when loading Config object
     * @return void
     */
    public function testLoadConfigFile(): void {

        $path = __DIR__ . '/fixtures/config.json';
        $Config = Config::load($path);
        $this->assertEquals('/app/blog/tests/fixtures', $Config->content_path);
        $this->assertEquals('metadata.json', $Config->metadata_file);
        $this->assertEquals('https://mywebsite.com/blog', $Config->url);
        $this->assertEquals(3, $Config->posts_per_page);
        $this->assertEquals(['tag-1', 'tag-2'], $Config->tags);

    }

}