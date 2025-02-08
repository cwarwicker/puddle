<?php

namespace Puddle\Commands;

use Exception;
use Puddle\Control;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class DeleteCommand extends Command
{

    protected Control $control;

    public function __construct(Control $control, ?string $name = null) {
        $this->control = $control;
        parent::__construct($name);
    }

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

        // Load the posts.
        $this->control->load();

        // Find the post to remove.
        $post = $this->control->getPost($postID);
        if (!$post) {
            throw new RuntimeException('Cannot find a post with ID: ' . $postID);
        }

        $result = $this->control->delete($postID);
        if ($result) {
            $output->writeln('Post (' . $postID . ') deleted');
            return Command::SUCCESS;
        } else {
            return Command::FAILURE;
        }

    }

}