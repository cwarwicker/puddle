<?php

use PHPUnit\Framework\TestCase;
use Puddle\Control;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

class PuddleTest extends TestCase
{

    private stdClass $config;
    private Control $control;

    protected function setUp(): void
    {
        error_reporting(E_ALL);
        $this->config = new stdClass();
        $this->config->metadata_path = tempnam(sys_get_temp_dir(), 'test_metadata_');
        $this->config->content_path = sys_get_temp_dir();
        $this->config->url = 'https://example.com';
        $this->config->tags = ['tag1', 'tag2'];
        $this->control = new Control($this->config);
    }

    /**
     * Test the loading of an invalid JSON file as the config
     * @return void
     */
    public function testLoadInvalidConfig(): void {

        // This should throw an error because the $config is not valid.
        $this->expectException(TypeError::class);

        // Try and load the file.
        $config = Control::loadJSON(__DIR__ . '/fixtures/invalid.json');
        new Control($config);

    }

    /**
     * Test the loading of a valid JSON file of posts
     * @return void
     */
    public function testLoadConfig(): void {
        $this->assertSame($this->config, $this->control->config());
    }

    public function testGetLatestPostIDInitiallyZero(): void {
        $this->assertEquals(0, $this->control->getLatestPostID());
    }

    public function testAppReturnsConsoleApplication(): void {
        $this->assertInstanceOf(Application::class, $this->control->app());
    }

    public function testPostsInitiallyEmpty(): void {
        $this->assertEmpty($this->control->posts());
    }

    public function testLoadReturnsFalseWhenMetadataDoesNotExist(): void {

        $config = $this->config;
        $config->metadata_path = '/fake/path';
        $this->control->setConfig($config);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to open metadata file');
        $this->control->load();

    }

    public function testLoadReturnsMetadata(): void {
        $config = $this->config;
        $config->metadata_path = __DIR__ . '/fixtures/metadata.json';
        $this->control->setConfig($config);
        $this->control->load();
        $this->assertCount(2, $this->control->posts());
    }

    public function testAddPostIncreasesPostCount(): void {
        $this->control->add('Test Post', ['tag1', 'tag2']);
        $this->assertCount(1, $this->control->posts());
        $this->control->add('Another Test Post', []);
        $this->assertCount(2, $this->control->posts());
    }

    public function testGetLatestPostIDAfterCreatingPost(): void {
        $this->control->add('Test Post', ['tag1', 'tag2']);
        $this->control->add('Test Post', ['tag1', 'tag2']);
        $this->control->add('Test Post', ['tag1', 'tag2']);
        $postID = $this->control->getLatestPostID();
        $this->assertEquals(3, $postID);
    }

    public function testAddPostCreatesMarkdownFile(): void {
        $this->control->add('Test Post', ['tag1', 'tag2']);
        $postID = $this->control->getLatestPostID();
        $this->assertFileExists($this->config->content_path . '/' . $postID . '.md');
    }

    public function testLoadAfterAddingPost(): void {
        $this->control->add('Test Post', ['tag1']);
        $this->assertTrue($this->control->load());
        $this->assertCount(1, $this->control->posts());
    }

    public function testRenderInvalidPost(): void {
        $this->assertSame('', $this->control->render(123));
    }

    public function testGetPostReturnsNullForNonExistentPost(): void {
        $post = $this->control->getPost(123);
        $this->assertNull($post);
    }

    public function testGetPostReturnsCorrectPost() {

        $this->control->add('Test Post', ['tag1', 'tag2']);
        $post = $this->control->getPost(1);
        $this->assertInstanceOf(stdClass::class, $post);
        $this->assertEquals(1, $post->id);
        $this->assertEquals("Test Post", $post->title);
        $this->assertEquals(["tag1", "tag2"], $post->tags);

    }

    public function testDeleteInvalidPost(): void {
        $this->assertNotTrue($this->control->delete(123));
    }

    public function testDeletePost(): void {
        $this->control->add('Test Post', ['tag1', 'tag2']);
        $this->assertCount(1, $this->control->posts());
        $this->assertTrue($this->control->delete(1));
        $this->assertCount(0, $this->control->posts());
    }

    public function findPostsByTag(): void {

        $this->control->add('Test Post', ['tag1', 'tag2']);
        $this->control->add('Test Post 2', ['tag2', 'tag3']);
        $this->control->add('Test Post 3', []);
        $this->control->add('Test Post 4', ['tag1']);

        $this->assertEquals(2, $this->control->getPostsByTag('tag1'));
        $this->assertEquals(2, $this->control->getPostsByTag('tag2'));
        $this->assertEquals(1, $this->control->getPostsByTag('tag3'));
        $this->assertEquals(0, $this->control->getPostsByTag('tag4'));


    }

    // TODO: Test the commands.

}