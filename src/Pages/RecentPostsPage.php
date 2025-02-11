<?php

namespace Puddle\Pages;

use Puddle\Config;
use Puddle\Page;
use Puddle\Post;
use Puddle\PostList;

class RecentPostsPage extends Page
{

    /**
     * Load the RecentPostsPage
     * @param Config $config
     * @return RecentPostsPage
     */
    public static function load(Config $config): RecentPostsPage
    {
        return new RecentPostsPage(config: $config);
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

    public function getPosts(): array {
        return Post::getRecent(config: $this->config);
    }

    public function getDisplay(int $page = 1): string {

        $posts = $this->getPosts();
        $PostList = new PostList(config: $this->config);
        foreach ($posts as $post) {
            $PostList->add(post: $post);
        }

        return $PostList->getDisplay(twig: $this->twig(), page: $page);

    }
}
