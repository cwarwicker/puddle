<?php

namespace Puddle;

use DateTime;
use Exception;
use stdClass;
use Symfony\Component\Console\Application;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class Control
{

    /**
     * @var Application The console application
     */
    private Application $app;

    /**
     * @var array Array of posts loaded from JSON file.
     */
    private array $posts = [];

    /**
     * @var stdClass Object of config values
     */
    private stdClass $config;

    /**
     * @var int ID of the latest created post
     */
    private int $latestPostID = 0;

    /**
     * Construct the Control object
     * @param stdClass $config
     */
    public function __construct(stdClass $config) {

        // Apply the supplied config object.
        $this->setConfig($config);

        // Add the console application.
        $this->app = new Application();

    }

    /**
     * Set the config
     * @param stdClass $config
     * @return void
     */
    public function setConfig(stdClass $config): void {
        $this->config = $config;
    }

    /**
     * Get the latest post ID. This is used after createPost to return the ID of the new post.
     * @return int
     */
    public function getLatestPostID(): int {
        return $this->latestPostID;
    }

    /**
     * Get the config values
     * @return stdClass
     */
    public function config(): stdClass {
        return $this->config;
    }

    /**
     * Get the console application
     * @return Application
     */
    public function app(): Application {
        return $this->app;
    }

    /**
     * Get the posts array
     * @return array
     */
    public function posts(): array {
        return $this->posts;
    }

    /**
     * Load the console commands
     * @param array $commands
     * @return void
     */
    public function loadCommands(array $commands): void {
        $this->app->addCommands($commands);
    }

    /**
     * Run the console
     * @return void
     * @throws Exception
     */
    public function run(): void {
        $this->app->run();
    }

    /**
     * Try to load the post metadata from the given metadata file
     * @return bool
     * @throws Exception
     */
    public function load(): bool {

        // If it doesn't exist, create it.
        if (!file_exists($this->config->metadata_path)) {
            $this->saveMetadata();
        }

        $content = static::loadJSON($this->config->metadata_path);
        if (is_array($content)) {

            // Load the post metadata.
            $this->posts = $content;

            // Get the latest post ID.
            if (count($this->posts) === 0) {
                $this->latestPostID = 0;
            } else {
                $this->latestPostID = array_reduce($this->posts, function ($carry, $item) {
                    return ($carry === null || $item->id > $carry->id) ? $item : $carry;
                })->id;
            }

            return true;

        }

        return false;

    }

    /**
     * Add a post to system
     * @param string $title
     * @param array $tags
     * @return bool
     * @throws Exception
     */
    public function add(string $title, array $tags, string $image = null): bool {

        $post = new stdClass();
        $post->id = ++$this->latestPostID;
        $post->title = $title;
        $post->tags = $tags;
        $post->date = date('d-m-Y, H:i');
        $post->image = $image;

        $this->posts[] = $post;

        return $this->saveMetadata() && $this->createPost($post);

    }

    /**
     * Create the post file
     * @param stdClass $post
     * @return bool
     */
    private function createPost(stdClass $post): bool {

        // Try and open the file for writing to create a blank file.
        $filename = $this->config->content_path . '/' . $post->id . '.md';
        $fh = fopen($filename, 'w');
        if (!$fh) {
            return false;
        }

        fclose($fh);

        $this->latestPostID = $post->id;

        return true;

    }

    /**
     * Save the posts metadata to the posts JSON file
     * @return bool
     * @throws Exception
     */
    private function saveMetadata(): bool {

        // Try and open the file for writing.
        $fh = @fopen($this->config->metadata_path, 'w');
        if (!$fh) {
            throw new Exception('Failed to open metadata file for writing');
        }

        // Write the JSON.
        $result = fwrite($fh, json_encode($this->posts));
        fclose($fh);

        // See if it worked.
        return ($result !== false);

    }

    /**
     * Delete a given post
     * @param int $postID
     * @return bool
     * @throws Exception
     */
    public function delete(int $postID): bool {

        // Make sure the post exists.
        $post = $this->getPost($postID);
        if (!$post) {
            return false;
        }

        // Remove the post from the array.
        $filtered = (array_values(array_filter($this->posts, fn($obj) => $obj->id != $postID)));
        $this->posts = $filtered;

        $result = $this->saveMetadata();

        // And remove the content file.
        $result = $result && unlink($this->config->content_path . '/' . $postID . '.md');

        return $result;

    }

    /**
     * Get a post by its ID
     * @param int $postID
     * @return stdClass|null
     */
    public function getPost(int $postID): stdClass|null {

        $filter = array_filter($this->posts, function($item) use ($postID) {
            return $item->id == $postID;
        });

        $post = ($filter) ? reset($filter) : null;
        if (!is_null($post)) {
            $this->applyPostData($post);
        }
        return $post;

    }

    /**
     * Get the url for the post
     * @param stdClass $post
     * @return string
     */
    private function getPostURL(stdClass $post): string {
        $convert = DateTime::createFromFormat('d-m-Y, H:i', $post->date);
        return $this->config->url . '/' . $post->id . '/' . $convert->format('Y/m/d') . '/' . $this->slug($post->title);
    }

    /**
     * Convert title to url slug
     * @param string $title
     * @return string
     */
    private function slug(string $title): string {
        $string = strtolower($title);
        $string = preg_replace('/[^a-z0-9\s]/', '', $string);
        return preg_replace('/\s+/', '_', trim($string));
    }

    /**
     * Get most recent posts
     * @param int $start
     * @return array
     */
    private function getMostRecentPosts(int $start = 0): array {

        $posts = array_slice(array_reverse($this->posts), $start, $this->config->posts_per_page);
        foreach ($posts as $post) {
            $this->applyPostData($post);
        }

        return $posts;

    }

    public function getList(int $page = 1, array $posts = []): array {

        if (empty($posts)) {
            $count = count($this->posts);
            $start = ($page * $this->config->posts_per_page) - $this->config->posts_per_page;
            $posts = $this->getMostRecentPosts($start);
            foreach ($posts as $post) {
                $this->applyPostData($post);
            }
        } else {
            $count = count($posts);
        }

        $twig = $this->twig();
        $data = [
            'url' => $this->config->url,
            'posts' => $posts,
            'recent_posts' => $this->getMostRecentPosts(),
            'pages' => ($count > 0) ? ceil($count / $this->config->posts_per_page) : 1,
            'page' => $page,
        ];

        return ['content' => $twig->render('list.twig', $data), 'meta' => []];

    }

    private function applyPostData(stdClass &$post): void {
        $post->content = file_get_contents($this->config->content_path . '/' . $post->id . '.md');
        $post->url = $this->getPostURL($post);
    }

    /**
     * Load twig environment
     * @return Environment
     */
    private function twig(): Environment {

        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        $twig = new Environment($loader);
        $twig->addExtension(new MarkdownExtension());
        $twig->addRuntimeLoader(new class implements RuntimeLoaderInterface {
            public function load($class) {
                if (MarkdownRuntime::class === $class) {
                    return new MarkdownRuntime(new DefaultMarkdown());
                }
            }
        });

        return $twig;

    }

    /**
     * Get posts by their tag
     * @param string $tag
     * @return array
     */
    public function getPostsByTag(string $tag): array {

        $filter = array_filter($this->posts, function($item) use ($tag) {
            return in_array($tag, $item->tags);
        });

        $return = [];
        foreach ($filter as $post) {
            $this->applyPostData($post);
            $return[] = $post;
        }

        return $return;

    }

    /**
     * Return the HTML content to render a given post
     * @param int $postID
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getDisplay(int $postID): array {

        // Get the post.
        $post = $this->getPost($postID);
        if (is_null($post)) {
            return '';
        }

        $twig = $this->twig();
        $data = [
            'url' => $this->config->url,
            'post' => $post,
            'recent_posts' => $this->getMostRecentPosts(),
        ];

        return ['content' => $twig->render('post.twig', $data), 'meta' => $this->getPostMetaTags($post)];

    }

    private function getPostMetaTags(stdClass $post): array {
        return [
            'title' => $post->title,
            'og:title' => $post->title,
            'og:description' => substr($post->content, 0, 50),
            'og:image' => $post->image ?? '',
            'og:url' => $post->url,
            'og:type' => 'article',
        ];
    }

    /**
     * Load data from a given JSON file.
     * @param string $file
     * @return mixed
     */
    public static function loadJSON(string $file): mixed {
        return json_decode(file_get_contents($file));
    }

}