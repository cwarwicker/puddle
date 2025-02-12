<?php

namespace Puddle\Commands;

use Exception;
use InvalidArgumentException;
use Puddle\Config;
use Puddle\Post;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends Command
{

    /**
     * @var Config Config object
     */
    protected Config $config;

    /**
     * Construct the object
     * @param string $name
     * @param Config $config
     */
    public function __construct(string $name, Config $config) {
        parent::__construct(name: $name);
        $this->config = $config;
    }

    /**
     * Add the arguments required for the command.
     * @return void
     */
    protected function configure() {
        $this->addArgument('id', InputArgument::REQUIRED, 'ID of the post to delete');
    }

    /**
     * Add a blog post.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $postID = $input->getArgument('id');

        try {

            $post = Post::load($postID, $this->config);
            $result = $post->delete();
            if ($result) {
                $output->writeln('Post (' . $postID . ') deleted');
                return Command::SUCCESS;
            } else {
                return Command::FAILURE;
            }

        } catch (InvalidArgumentException $e) {
            throw new RuntimeException('No such post (' . $postID . ')');
        }

    }

}