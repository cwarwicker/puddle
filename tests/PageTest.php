<?php

use PHPUnit\Framework\TestCase;
use Puddle\Config;
use Puddle\Page;
use Puddle\Pages\PostPage;
use Puddle\Pages\RecentPostsPage;
use Puddle\Pages\TagPage;

require_once __DIR__ . '/../vendor/autoload.php';

class PageTest extends TestCase
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
     * Test that if the "p1" query string property is "tag" but the tag name is missing, we load the RecentPosts page.
     * @return void
     */
    public function testLoadTagPageWithMissingTag(): void {

        unset($_GET);
        $_GET['p1'] = 'tag';
        $page = Page::which(config: $this->config);
        $this->assertEquals('Puddle\\Pages\\RecentPostsPage', get_class($page));

    }

    /**
     * Test that if the "p1" query string property is "tag" and the "p2" contains a string, then we load the Tag page.
     * @return void
     */
    public function testLoadTagPage(): void {

        unset($_GET);
        $_GET['p1'] = 'tag';
        $_GET['p2'] = 'test';
        $page = Page::which(config: $this->config);
        $this->assertEquals('Puddle\\Pages\\TagPage', get_class($page));
        $this->assertEquals('test', $page->getTag());

    }

    /**
     * Test that if the query string is empty then we load the RecentPosts page.
     * @return void
     */
    public function testLoadRecentPostsPage(): void {

        unset($_GET);
        $page = Page::which(config: $this->config);
        $this->assertEquals('Puddle\\Pages\\RecentPostsPage', get_class($page));

    }

    /**
     * Test that the page has the correct Config loaded
     * @return void
     */
    public function testPageHasCorrectConfig(): void {

        unset($_GET);
        $page = Page::which(config: $this->config);
        $this->assertEquals($this->config, $page->config());

    }

    /**
     * Test loading the PostPage with an invalid post
     * @return void
     */
    public function testPostPageInvalidPost(): void {

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');
        PostPage::load(postID: 123, config: $this->config);

    }

    /**
     * Test loading the PostPage with a valid post
     * @return void
     */
    public function testPostPageValidPost(): void {

        $page = PostPage::load(postID: 1, config: $this->config);
        $content = file_get_contents($this->config->content_path . '/' . '1.md');
        $this->assertEquals(1, $page->post()->id());
        $this->assertEquals('Test Post 1', $page->post()->title());
        $this->assertEquals($content, $page->post()->content());

    }

    /**
     * Test that the correct metadata tags are returned by the PostPage.
     * @return void
     */
    public function testPostPageMetadataTags(): void {

        $page = PostPage::load(postID: 1, config: $this->config);
        $this->assertEquals('Test Post 1', $page->metadata()['og:title']);
        $this->assertEquals('/assets/images/post-1.jpg', $page->metadata()['og:image']);
        $this->assertEquals('https://mywebsite.com/blog/1/2025/02/09/test_post_1', $page->metadata()['og:url']);
        $this->assertEquals('article', $page->metadata()['og:type']);
        $this->assertEquals('Hello This is my first post. It goes on a bit as I just keep talking and I dont really know when to ...', $page->metadata()['og:description']);

    }

    /**
     * Test that the getDisplay function for PostPage returns the expected HTML content
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function testPostPageGetDisplay(): void {

        $page = PostPage::load(postID: 1, config: $this->config);
        $this->assertStringContainsString("<div class=\"blog-post-content\"><h2>Hello</h2>\n<p>This is my first post. It goes on a bit as I just keep talking and I dont really know when to stop.</p>\n<p>Maybe I'll do <em>another</em> paragraph!</p>\n</div>\n", $page->getDisplay());

    }

    /**
     * Test that the correct metadata tags are returned by the PostPage.
     * @return void
     */
    public function testTagPageMetadataTags(): void {

        unset($_GET);
        $page = TagPage::load(config: $this->config, tag: 'test');
        $this->assertEquals('test', $page->metadata()['og:title']);
        $this->assertEquals('All posts tagged with test', $page->metadata()['og:description']);
        $this->assertEquals('https://mywebsite.com/blog/tag/test', $page->metadata()['og:url']);
        $this->assertEquals('article', $page->metadata()['og:type']);

    }

    /**
     * Test that the correct posts are returned when searching by tag
     * @return void
     */
    public function testTagPagePosts(): void {

        unset($_GET);
        $page = TagPage::load(config: $this->config, tag: 'tag-1');
        $posts = $page->getPosts();
        $this->assertCount(5, $posts);
        $this->assertEquals('Test Post 1', $posts[0]->title());
        $this->assertEquals('Test Post 3', $posts[1]->title());
        $this->assertEquals('Test Post 6', $posts[2]->title());
        $this->assertEquals('Test Post 7', $posts[3]->title());
        $this->assertEquals('Test Post 8', $posts[4]->title());

    }

    /**
     * Test that the getDisplay function for TagPage returns the expected HTML content
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function testTagPageGetDisplay(): void {

        unset($_GET);
        $page = TagPage::load(config: $this->config, tag: 'tag-1');
        $content = $page->getDisplay();
        $this->assertStringContainsString('Test Post 8', $content);
        $this->assertStringContainsString('Test Post 7', $content);
        $this->assertStringContainsString('Test Post 6', $content);
        $page = TagPage::load(config: $this->config, tag: 'tag-1', page: 2);
        $content = $page->getDisplay();
        $this->assertStringContainsString('Test Post 3', $content);

    }

    /**
     * Test that if we try to load the tag page but forget the tag, it reverts to RecentPostsPage.
     * @return void
     */
    public function testMissingTagRevertsToRecentPostsPage(): void {

        $page = TagPage::load(config: $this->config);
        $this->assertEquals(RecentPostsPage::class, get_class($page));

    }

    /**
     * Test that the correct posts are returned when getting the most recent posts
     * @return void
     */
    public function testRecentPostsPagePosts(): void {

        $page = RecentPostsPage::load(config: $this->config);
        $posts = $page->getPosts();

        // The $page->getPosts() actually returns ALL the posts and they are filtered later by the PostList.
        $this->assertCount(8, $posts);

    }

    /**
     * Test that the getDisplay function for RecentPostsPage returns the expected HTML content
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function testRecentPostsPageGetDisplayPagination(): void {

        $page = RecentPostsPage::load(config: $this->config);
        $content = $page->getDisplay();
        $this->assertStringContainsString('Test Post 8', $content);
        $this->assertStringContainsString('Test Post 7', $content);
        $this->assertStringContainsString('Test Post 6', $content);
        $page = RecentPostsPage::load(config: $this->config, page: 2);
        $content = $page->getDisplay();
        $this->assertStringContainsString('Test Post 5', $content);
        $this->assertStringContainsString('Test Post 4', $content);
        $this->assertStringContainsString('Test Post 3', $content);
        $page = RecentPostsPage::load(config: $this->config, page: 3);
        $content = $page->getDisplay();
        $this->assertStringContainsString('Test Post 2', $content);
        $this->assertStringContainsString('Test Post 1', $content);

    }

    /**
     * Test that the correct metadata tags are returned by the RecentPostsPage.
     * @return void
     */
    public function testRecentPostsPageMetadataTags(): void {

        $page = RecentPostsPage::load(config: $this->config);
        $this->assertEquals('My blog', $page->metadata()['og:title']);
        $this->assertEquals('This is my blog', $page->metadata()['og:description']);
        $this->assertEquals('https://mywebsite.com/blog', $page->metadata()['og:url']);
        $this->assertEquals('website', $page->metadata()['og:type']);

    }


}