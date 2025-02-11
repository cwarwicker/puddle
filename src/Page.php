<?php

namespace Puddle;

use Puddle\Pages\PostPage;
use Puddle\Pages\RecentPostsPage;
use Puddle\Pages\TagPage;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

abstract class Page
{

    /**
     * @var Config Blog config
     */
    protected Config $config;

    /**
     * Construct the Page object
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Get the title heading for the page
     * @return string
     */
    public function title(): string {
        return '';
    }

    /**
     * Get the Config object which was loaded into the page
     * @return Config
     */
    public function config(): Config {
        return $this->config;
    }

    /**
     * Get the default url for the site
     * @return string
     */
    public function url(): string {
        return $this->config->url;
    }

    /**
     * Work out which page we want to display, based on url query string.
     * @return Page
     */
    public static function which(Config $config): Page {

        // If the "p1" field of the url is a digit, then we are loading a particular post.
        if (isset($_GET['p1']) && ctype_digit($_GET['p1'])) {
            return PostPage::load(postID: $_GET['p1'], config: $config);
        } else if (isset($_GET['p1']) && $_GET['p1'] === 'tag') {
            $tag = (isset($_GET['p2']) && strlen($_GET['p2'])) ? $_GET['p2'] : null;
            $page = (isset($_GET['p3']) && $_GET['p3'] === 'page' && isset($_GET['p4']) && ctype_digit($_GET['p4'])) ?
                $_GET['p4'] : 1;
            return TagPage::load(config: $config, tag: $tag, page: $page);
        } else {
            $page = (isset($_GET['p1']) && $_GET['p1'] === 'page' && isset($_GET['p2']) && ctype_digit($_GET['p2'])) ?
                $_GET['p2'] : 1;
            return RecentPostsPage::load(config: $config, page: $page);
        }

    }

    /**
     * Load the twig environment
     * @return Environment
     */
    protected function twig(): Environment {

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
     * Render the page HTML contents
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(): void {
        echo $this->getDisplay();
    }

    abstract public function metadata(): array;
    abstract public function getDisplay(): string;


}