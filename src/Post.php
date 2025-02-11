<?php

namespace Puddle;

use DateTime;
use InvalidArgumentException;
use stdClass;
use UnexpectedValueException;

class Post
{

    const DESCRIPTION_LENGTH = 100;

    protected int $id;
    protected string $title = '';
    protected array $tags = [];
    protected string $date = '';
    protected string $image = '';
    protected ?string $content = '';
    protected Config $config;

    public function __construct(stdClass $data, Config $config) {

        foreach ($data as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new UnexpectedValueException('(' . $property . ') is not a valid config.json property');
            }
            $this->{$property} = $value;
        }

        $this->config = $config;

    }

    public function id(): int {
        return $this->id;
    }

    public function title(): string {
        return $this->title;
    }

    public function tags(): array {
        return $this->tags;
    }

    public function date(): string {
        return $this->date;
    }

    public function image(): string {
        return $this->image;
    }

    public function url(): string {
        $convert = DateTime::createFromFormat('d-m-Y, H:i', $this->date);
        return $this->config->url . '/' . $this->id . '/' . $convert->format('Y/m/d') . '/' . $this->slug();
    }

    public function description(): string {
        $patterns = ['/\n/', '/  /', '/[^a-z0-9 \.]/i'];
        $replacements = [' ', ' ', ''];
        $content = $this->content();
        $content = trim(preg_replace($patterns, $replacements, $content));
        return (strlen($content) > static::DESCRIPTION_LENGTH) ? substr($content, 0, static::DESCRIPTION_LENGTH) . '...' : $content;
    }

    public function content(): string {
        $file = $this->config->content_path . '/' . $this->id . '.md';
        if (!strlen($this->content) && file_exists($file)) {
            $this->content = file_get_contents($file);
        }
        return $this->content;
    }

    /**
     * Convert the post title to a url slug
     * @return string
     */
    protected function slug(): string {
        $string = preg_replace('/[^a-z0-9\s]/', '', strtolower($this->title));
        return preg_replace('/\s+/', '_', trim($string));
    }

    /**
     * Try to load a given post
     * @param int $id
     * @param Config $config
     * @return Post
     */
    public static function load(int $id, Config $config): Post {

        $metadata = Metadata::load(config: $config);
        $postdata = $metadata->get($id);
        if (is_null($postdata)) {
            throw new InvalidArgumentException('Post (' . $id . ') does not exist');
        }

        return new Post(data: $postdata, config: $config);

    }

    public static function getByTag(string $tag, Config $config): array {

        $metadata = Metadata::load(config: $config);
        $postdata = $metadata->getByTag($tag);
        $return = [];
        foreach ($postdata as $post) {
            $return[] = new Post(data: $post, config: $config);
        }
        return $return;

    }

}