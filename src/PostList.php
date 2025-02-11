<?php

namespace Puddle;

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
     * Filter the posts by page. This is done by the start number and the number per page.
     * @param int $start
     * @return array
     */
    protected function filterPosts(int $start): array {
        return array_slice(array_reverse($this->posts), $start, $this->config->posts_per_page);
    }

    public function getDisplay(Environment $twig, int $page = 1): string {

        $count = count($this->posts);
        $start = ($page * $this->config->posts_per_page) - $this->config->posts_per_page;
        $posts = $this->filterPosts($start);

        $data = [
            'url' => $this->config->url,
            'posts' => $posts,
//            'recent_posts' => $this->getMostRecentPosts(),
            'pages' => ($count > 0) ? ceil($count / $this->config->posts_per_page) : 1,
            'page' => $page,
        ];

        return $twig->render('list.twig', $data);

    }

}