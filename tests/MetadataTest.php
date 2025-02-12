<?php
use PHPUnit\Framework\TestCase;
use Puddle\Config;
use Puddle\Metadata;

require_once __DIR__ . '/../vendor/autoload.php';

class MetadataTest extends TestCase
{

    private Config $config;

    /**
     * Set up the tests data
     * @return void
     */
    public function setUp(): void {
        $path = __DIR__ . '/fixtures/config.json';
        $this->config = Config::load($path);
    }

    /**
     * Test loading an invalid metadata file
     * @covers Metadata::load
     * @return void
     */
    public function testLoadInvalidMetadata(): void {

        $data = new stdClass();
        $data->content_path = '/fake/path';
        $data->metadata_file = '/fake/path/metadata.json';
        $config = new Config(data: $data);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot write to metadata file');
        Metadata::load(config: $config);

    }

    /**
     * Test loading the metadata file
     * @covers Metadata::load
     * @return void
     */
    public function testLoadMetadata(): void {

        $this->expectNotToPerformAssertions();
        Metadata::load(config: $this->config);

    }

    /**
     * Test getting all the posts metadata
     * @covers Metadata::all
     * @return void
     */
    public function testMetadataAll(): void {

        $metadata = Metadata::load(config: $this->config);
        $this->assertCount(8, $metadata->all());

    }

    /**
     * Test getting a specific post's metadata
     * @covers Metadata::get
     * @return void
     */
    public function testMetadataGet(): void {

        $metadata = Metadata::load(config: $this->config);
        $post = $metadata->get(1);
        $this->assertEquals(1, $post->id);
        $this->assertEquals('Test Post 1', $post->title);

    }

    /**
     * Test getting posts by tag
     * @covers Metadata::getByTag
     * @return void
     */
    public function testMetadataGetByTag(): void {

        $metadata = Metadata::load(config: $this->config);
        $posts = $metadata->getByTag('tag-1');
        $this->assertCount(5, $posts);
        $posts = $metadata->getByTag('fake-tag');
        $this->assertCount(0, $posts);

    }

    /**
     * Test getting the latest post ID
     * @covers Metadata::getLatestID
     * @return void
     */
    public function testMetadataGetLatestID(): void {

        $metadata = Metadata::load(config: $this->config);
        $this->assertEquals(8, $metadata->getLatestID());

    }

    /**
     * Test adding a post to the metadata file
     * @covers Metadata::add
     * @return void
     */
    public function testMetadataAdd(): void {

        $metadata = new Metadata(data: [], config: $this->config);
        $this->assertCount(0, $metadata->all());

        $newpost = new stdClass();
        $newpost->title = 'Some title';
        $this->assertEquals(1, $metadata->add($newpost));
        $this->assertCount(1, $metadata->all());

    }

    /**
     * Test deleting a post from the metadata file
     * @covers Metadata::delete
     * @return void
     */
    public function testMetadataDelete(): void {

        $metadata = new Metadata(data: [], config: $this->config);
        $this->assertCount(0, $metadata->all());

        $newpost = new stdClass();
        $newpost->title = 'Some title';
        $this->assertEquals(1, $metadata->add($newpost));
        $this->assertCount(1, $metadata->all());

        $metadata->delete(1);
        $this->assertCount(0, $metadata->all());

    }

    /**
     * Test saving the metadata file
     * @return void
     */
    public function testMetadataSave(): void {

        $config = clone($this->config);
        $config->metadata_file = 'php://memory';
        $metadata = new Metadata(data: [], config: $config);

        $newpost = new stdClass();
        $newpost->title = 'Some title';
        $metadata->add($newpost);
        $this->assertTrue($metadata->save());

    }


}