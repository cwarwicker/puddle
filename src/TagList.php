<?php

namespace Puddle;

use Puddle\Pages\TagPage;
use Twig\Environment;

class TagList
{

    protected array $tags = [];
    protected Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Load the tags from the metadata file of posts
     * @return TagList
     */
    public function load(): TagList {

        $metadata = Metadata::load(config: $this->config);
        foreach($metadata->all() as $post) {
            foreach ($post->tags as $tag) {
                $this->add($tag);
            }
        }

        return $this;

    }

    /**
     * Get all the tags and their counts, ordered by most common
     * @return array
     */
    public function all(): array {

        // Sort the tags.
        $this->sort();

        $return = [];
        foreach($this->tags as $tag => $count) {
            $return[] = ['tag' => $tag, 'count' => $count, 'url' => $this->url($tag)];
        }

        return $return;
    }

    /**
     * Count the number of times a tag has been used
     * @param string $tag
     * @return int
     */
    public function count(string $tag): int {
        return (array_key_exists($tag, $this->tags)) ? $this->tags[$tag] : 0;
    }

    /**
     * Sort tags into most common first
     * @return void
     */
    protected function sort(): void {
        arsort($this->tags);
    }

    /**
     * Increment the count of posts with this tag.
     * @param string $tag
     * @return void
     */
    public function add(string $tag) {

        if (!array_key_exists($tag, $this->tags)) {
            $this->tags[$tag] = 0;
        }

        $this->tags[$tag]++;

    }

    /**
     * Get the URL for a tag page.
     * This is duplicated from TagPage but
     * @param string $tag
     * @return string
     */
    public function url(string $tag): string {
        return TagPage::getURL($this->config, $tag);
    }

    public function getDisplay(Environment $twig): string {

        return 'hi';

    }

}
