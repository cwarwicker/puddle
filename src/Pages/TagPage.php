<?php

namespace Puddle\Pages;

use Puddle\Config;
use Puddle\Page;
use Puddle\Post;
use Puddle\PostList;

class TagPage extends Page
{

    /**
     * @var string The tag to search for
     */
    protected string $tag = '';

    /**
     * @var int Which page number we are on
     */
    protected int $page = 1;

    /**
     * Construct the TagPage object
     * @param string $tag
     * @param int $page
     * @param Config $config
     */
    public function __construct(string $tag, int $page, Config $config) {
        parent::__construct($config);
        $this->tag = $tag;
        $this->page = $page;
    }

    /**
     * Get the title heading for the page
     * @return string
     */
    public function title(): string {
        return 'Tagged with <strong>' . $this->tag . '</strong>';
    }

    /**
     * Get the tag we are searching for
     * @return string
     */
    public function getTag(): string {
        return $this->tag;
    }

    /**
     * Load the TagPage for the given tag name
     * @param Config $config
     * @param int $page
     * @return TagPage|RecentPostsPage
     */
    public static function load(Config $config, string $tag = null, int $page = 1): TagPage|RecentPostsPage {

        if ($tag) {
            return new TagPage(tag: $tag, page: $page, config: $config);
        } else {
            return new RecentPostsPage(config: $config);
        }

    }

    /**
     * Get the HTML metadata tags for the page
     * @return array
     */
    public function metadata(): array {
        return [
            'og:title' => $this->tag,
            'og:description' => 'All posts tagged with ' . $this->tag,
            'og:url' => $this->url(),
            'og:type' => 'article',
        ];
    }

    /**
     * Get the URL for searching by this tag
     * @return string
     */
    public function url(): string {
        return $this->config->url . '/tag/' . $this->tag;
    }

    /**
     * Get the posts tagged with this tag
     * @return array
     */
    public function getPosts(): array {
        return Post::getByTag(tag: $this->tag, config: $this->config);
    }

    /**
     * Get the HTML content of the TagPage
     * @return string
     */
    public function getDisplay(): string {

        // Get the posts with this tag.
        $posts = $this->getPosts();
        $PostList = new PostList(config: $this->config);
        foreach ($posts as $post) {
            $PostList->add(post: $post);
        }

        return $PostList->getDisplay(twig: $this->twig(), page: $this, pageNumber: $this->page);

    }

}