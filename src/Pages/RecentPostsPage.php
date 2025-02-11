<?php

namespace Puddle\Pages;

use Puddle\Config;
use Puddle\Page;
use Puddle\Post;
use Puddle\PostList;

class RecentPostsPage extends Page
{

    /**
     * @var int Which page number we are on
     */
    protected int $page = 1;

    /**
     * Construct the object
     * @param Config $config
     * @param int $page
     */
    public function __construct(Config $config, int $page = 1) {
        parent::__construct($config);
        $this->page = $page;
    }

    /**
     * Load the RecentPostsPage
     * @param Config $config
     * @param int $page
     * @return RecentPostsPage
     */
    public static function load(Config $config, int $page = 1): RecentPostsPage
    {
        return new RecentPostsPage(config: $config, page: $page);
    }

    /**
     * Get the HTML metadata tags for the page
     * @return array
     */
    public function metadata(): array {
        return [
            'og:title' => $this->config()->site_title,
            'og:description' => $this->config()->site_description,
            'og:url' => $this->config()->url,
            'og:type' => 'website',
        ];
    }

    /**
     * Get all posts. We will filter them later in PostList.
     * @return array
     */
    public function getPosts(): array {
        return Post::getRecent(config: $this->config);
    }

    /**
     * Get the recent posts for display in the sidebar
     * @return array
     */
    public function getSidebarPosts(): array {
        $posts = $this->getPosts();
        $PostList = new PostList(config: $this->config);
        foreach ($posts as $post) {
            $PostList->add(post: $post);
        }
        return $PostList->filterPosts(start: 0);
    }

    /**
     * Get the HTML content for rendering
     * @return string
     */
    public function getDisplay(): string {

        $posts = $this->getPosts();
        $PostList = new PostList(config: $this->config);
        foreach ($posts as $post) {
            $PostList->add(post: $post);
        }

        return $PostList->getDisplay(twig: $this->twig(), page: $this->page);

    }
}
