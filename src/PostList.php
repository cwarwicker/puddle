<?php

namespace Puddle;

use Puddle\Pages\RecentPostsPage;
use Twig\Environment;

class PostList
{

    protected array $posts = [];
    protected Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Add a Post to the list
     * @param Post $post
     * @return void
     */
    public function add(Post $post): void {
        $this->posts[] = $post;
    }

    /**
     * Get the posts in the list
     * @return array
     */
    public function posts(): array {
        return $this->posts;
    }

    /**
     * Filter the posts by page. This is done by the start number and the number per page.
     * @param int $start
     * @return array
     */
    public function filterPosts(int $start): array {
        return array_slice(array_reverse($this->posts), $start, $this->config->posts_per_page);
    }

    public function getDisplay(Environment $twig, int $page = 1, string $title = ''): string {

        $count = count($this->posts);
        $totalPages = (int)(($count > 0) ? ceil($count / $this->config->posts_per_page) : 1);

        // If we ask for a page greater than we have, set to the last page.
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $start = ($page * $this->config->posts_per_page) - $this->config->posts_per_page;

        $posts = $this->filterPosts($start);
        $recent = new RecentPostsPage(config: $this->config);

        $data = [
            'url' => $this->config->url,
            'posts' => $posts,
            'recent_posts' => $recent->getSidebarPosts(),
            'pages' => $totalPages,
            'page' => $page,
            'title' => $title,
        ];

        return $twig->render('list.twig', $data);

    }

}