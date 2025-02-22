<?php

namespace Puddle\Pages;

use Puddle\Config;
use Puddle\Page;
use Puddle\Post;
use Puddle\TagList;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PostPage extends Page
{

    /**
     * @var Post $post The Post object
     */
    protected Post $post;

    /**
     * Construct the PostPage object
     * @param int $postID
     * @param Config $config
     */
    public function __construct(int $postID, Config $config) {
        parent::__construct($config);
        $this->post = Post::load($postID, $config);
    }

    /**
     * Get the post
     * @return Post
     */
    public function post(): Post {
        return $this->post;
    }

    /**
     * Get the title heading for the page
     * @return string
     */
    public function title(): string {
        return $this->post->title();
    }

    /**
     * Load the PostPage for the given post ID
     * @param int $postID
     * @param Config $config
     * @return PostPage
     */
    public static function load(int $postID, Config $config): PostPage {
        return new PostPage(postID: $postID, config: $config);
    }

    /**
     * Get the HTML metadata tags for the page
     * @return array
     */
    public function metadata(): array {
        return [
            'og:title' => $this->post->title(),
            'title' => $this->post->title(),
            'og:description' => $this->post->description(),
            'description' => $this->post->description(),
            'og:image' => $this->post->image(),
            'og:url' => $this->post->url(),
            'og:type' => 'article',
        ];
    }

    /**
     * Get the page HTML contents
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getDisplay(): string {

        $recent = new RecentPostsPage(config: $this->config);
        $tags = new TagList(config: $this->config);
        $data = [
            'url' => $this->config->url,
            'post' => $this->post,
            'recent_posts' => $recent->getSidebarPosts(),
            'tag_list' => $tags->load()->all(),
        ];

        return $this->twig()->render('post.twig', $data);

    }

}