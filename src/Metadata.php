<?php

namespace Puddle;

use InvalidArgumentException;
use stdClass;

class Metadata
{

    /**
     * @var array Array of posts metadata
     */
    protected array $data;

    /**
     * Construct the Metadata object
     * @param array $data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * Get all the post metadata
     * @return array
     */
    public function all(): array {
        return $this->data;
    }

    /**
     * Get the metadata of a specific post
     * @param int $postID
     * @return stdClass|null
     */
    public function get(int $postID): ?stdClass {

        $filter = array_filter($this->data, function($item) use ($postID) {
            return $item->id == $postID;
        });

        $post = ($filter) ? reset($filter) : null;
        return ($post) ?? null;

    }

    /**
     * Get the metadata of a specific post
     * @param string $tag
     * @return array
     */
    public function getByTag(string $tag): array {

        $filter = array_filter($this->data, function($item) use ($tag) {
            return in_array($tag, $item->tags);
        });

        $return = [];
        foreach ($filter as $post) {
            $return[] = $post;
        }

        return $return;

    }

    /**
     * Load the post metadata from the file
     * @param Config $config
     * @return Metadata
     */
    public static function load(Config $config): Metadata {

        $file = $config->content_path . DIRECTORY_SEPARATOR . $config->metadata_file;

        // Make sure the file exists.
        if (!file_exists($file)) {
            throw new InvalidArgumentException('Metadata file (' . $file . ') does not exist');
        }

        // Try and parse the contents as JSON.
        $json = json_decode(file_get_contents($file));
        if (!$json) {
            throw new InvalidArgumentException('Metadata file (' . $file . ') does not contain valid JSON');
        }

        return new Metadata($json);

    }
}