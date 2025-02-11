<?php

namespace Puddle\Pages;

use Puddle\Config;
use Puddle\Page;

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

    public function metadata(): array
    {
        // TODO: Implement metadata() method.
    }

    public function getDisplay(): string
    {
        // TODO: Implement getDisplay() method.
    }
}
