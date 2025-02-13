<?php

namespace Puddle;

use UnexpectedValueException;
use InvalidArgumentException;
use stdClass;

class Config
{

    // Supported properties for the Config object.
    public string $content_path = '';
    public string $metadata_file = '';
    public array $tags = [];
    public string $url = '';
    public int $posts_per_page = 0;
    public string $site_title = '';
    public string $site_description = '';
    public string $site_url = '';

    /**
     * Construct the Config object with the data supplied.
     * @param stdClass $data
     */
    public function __construct(stdClass $data) {

        foreach ($data as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new UnexpectedValueException('(' . $property . ') is not a valid config.json property');
            }
            $this->{$property} = $value;
        }

    }

    /**
     * Load the given JSON file into the Config object
     * @param string $file
     * @return Config
     */
    public static function load(string $file): Config {

        // Make sure the file exists.
        if (!file_exists($file)) {
            throw new InvalidArgumentException('Config file (' . $file . ') does not exist');
        }

        // Try and parse the contents as JSON.
        $json = json_decode(file_get_contents($file));
        if (!$json) {
            throw new InvalidArgumentException('Config file (' . $file . ') does not contain valid JSON');
        }

        return new Config(data: $json);

    }

}