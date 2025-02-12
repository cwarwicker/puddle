<?php

namespace Puddle;

use InvalidArgumentException;
use RuntimeException;
use stdClass;

/**
 * Class for handling the metadata file which contains all the post information
 */
class Metadata
{

    /**
     * @var array Array of posts metadata
     */
    protected array $data;

    /**
     * @var Config Config object
     */
    protected Config $config;

    /**
     * @var int The latest post ID
     */
    protected int $latestID = 0;

    /**
     * Construct the Metadata object
     * @param array $data
     * @param Config $config
     */
    public function __construct(array $data, Config $config) {

        $this->config = $config;

        // Load all the post data.
        $this->data = $data;

        // Work out the latest ID.
        if (count($this->data) > 0) {
            $this->latestID = array_reduce($this->data, function ($carry, $item) {
                return ($carry === null || $item->id > $carry->id) ? $item : $carry;
            })->id;
        }

    }

    /**
     * Get the latest post ID
     * @return int
     */
    public function getLatestID(): int {
        return $this->latestID;
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
     * Add a new post to the array.
     * @param stdClass $post
     * @return int
     */
    public function add(stdClass $post): int {

        // Get the ID for the new post.
        $post->id = ++$this->latestID;

        // Append the post to the data array.
        $this->data[] = $post;

        // Update the latest post ID on the metadata object.
        $this->latestID = $post->id;

        return $this->latestID;

    }

    /**
     * Delete a post from the array
     * @param int $postID
     * @return true
     */
    public function delete(int $postID): true {

        // Filter out the post with this ID.
        $this->data = (array_values(array_filter($this->data, fn($obj) => $obj->id != $postID)));

        return true;

    }

    /**
     * Save the metadata file
     * @return bool
     */
    public function save(): bool {

        // Try and open the file for writing.
        $file = $this->config->metadata_file;

        // Try and open the file for writing.
        $fh = @fopen($file, 'w');
        if (!$fh) {
            throw new RuntimeException('Failed to open metadata file for writing');
        }

        // Write the JSON.
        $result = fwrite($fh, json_encode($this->data));
        fclose($fh);

        // See if it worked.
        return ($result !== false);

    }

    /**
     * Load the post metadata from the file
     * @param Config $config
     * @return Metadata
     */
    public static function load(Config $config): Metadata {

        $file = $config->metadata_file;

        // If the file doesn't exist, try to create it.
        if (!file_exists($file)) {
            $result = @file_put_contents($file, '[]');
            if (!$result) {
                throw new RuntimeException('Cannot write to metadata file');
            }
        }

        // If it still doesn't exist, then we cannot continue.
        if (!file_exists($file)) {
            throw new InvalidArgumentException('Metadata file (' . $file . ') does not exist');
        }

        // Try and parse the contents as JSON.
        $json = json_decode(file_get_contents($file));
        if ($json === false) {
            throw new InvalidArgumentException('Metadata file (' . $file . ') does not contain valid JSON');
        }

        return new Metadata(data: $json, config: $config);

    }
}