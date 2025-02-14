<?php
use PHPUnit\Framework\TestCase;
use Puddle\Config;
use Puddle\TagList;

require_once __DIR__ . '/../vendor/autoload.php';

class TagListTest extends TestCase
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
     * Test adding tags to the list
     * @return void
     */
    public function testAddTag(): void {

        $TagList = new TagList($this->config);
        $this->assertCount(0, $TagList->all());
        $TagList->add('test-1');
        $TagList->add('test-2');
        $TagList->add('test-2');
        $this->assertCount(2, $TagList->all());

    }

    /**
     * Test the counting of instances of given tags
     * @return void
     */
    public function testCountTags(): void {

        $TagList = new TagList($this->config);
        $TagList->add('test-1');
        $TagList->add('test-2');
        $TagList->add('test-2');
        $this->assertEquals(1, $TagList->count('test-1'));
        $this->assertEquals(2, $TagList->count('test-2'));

    }

    /**
     * Test sorting the tags into most common first
     * @return void
     */
    public function testSortTags(): void {

        $TagList = new TagList($this->config);
        $TagList->add('test-1');
        $TagList->add('test-2');
        $TagList->add('test-2');
        $TagList->add('test-3');
        $TagList->add('test-3');
        $TagList->add('test-3');
        $tags = $TagList->all();
        $this->assertEquals('test-3', $tags[0]['tag']);
        $this->assertEquals('test-2', $tags[1]['tag']);
        $this->assertEquals('test-1', $tags[2]['tag']);
    }

    /**
     * Test the tag page url is generated correctly
     * @return void
     */
    public function testTagUrl(): void {

        $TagList = new TagList($this->config);
        $this->assertEquals($this->config->url . '/tag/test', $TagList->url('test'));

    }

    /**
     * Test loading the tags from the metadata file of posts
     * @return void
     */
    public function testLoadTags(): void {

        $TagList = new TagList($this->config);
        $TagList->load();
        $tags = $TagList->all();
        $this->assertCount(2, $tags);
        $this->assertEquals('tag-1', $tags[0]['tag']);
        $this->assertEquals(5, $tags[0]['count']);
        $this->assertEquals('tag-2', $tags[1]['tag']);
        $this->assertEquals(3, $tags[1]['count']);

    }

}