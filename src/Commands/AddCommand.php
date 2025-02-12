<?php

namespace Puddle\Commands;

use Exception;
use Puddle\Config;
use Puddle\Post;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AddCommand extends Command
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
     * Add a blog post.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $questions = [];
        $answers = [];
        $helper = $this->getHelper('question');

        // Get the possible tags we can choose from.
        $tagOptions = array_combine($this->config->tags, $this->config->tags);

        // Build the questions.
        $questions['title'] = new Question("Title of the blog post: ");
        $questions['title']->setValidator(function ($answer) {
            if (empty(trim($answer))) {
                throw new RuntimeException("Title cannot be empty. Please enter a valid title.");
            }
            return $answer;
        });

        $questions['tags'] = new ChoiceQuestion("Tags: ", $tagOptions);
        $questions['tags']->setMultiselect(true);

        // Allow empty input (user presses Enter).
        $questions['tags']->setValidator(function ($selected) use ($tagOptions) {

            // If we don't want any tags, that's fine.
            if (empty($selected)) {
                return [];
            }

            // Otherwise, loop through and make sure they are valid.
            $tags = explode(',', $selected);
            foreach ($tags as $tag) {
                if (!in_array($tag, $tagOptions)) {
                    throw new RuntimeException("Invalid tag. Please choose from the list provided.");
                }
            }

            return $tags;

        });

        $questions['image'] = new Question("Post image URL: ");

        // Get the answers.
        $answers['title'] = (string)$helper->ask($input, $output, $questions['title']);
        $answers['tags'] = $helper->ask($input, $output, $questions['tags']);
        $answers['image'] = (string)$helper->ask($input, $output, $questions['image']);

        // Add the post.
        $result = Post::add(config: $this->config, title: $answers['title'], tags: $answers['tags'], image: $answers['image']);
        if ($result !== false) {
            $output->writeln("Post created.");
            $output->writeln("Edit the following file to add your mark-down content: {$this->config->content_path}/{$result}.md");
        }

        return ($result) ? Command::SUCCESS : Command::FAILURE;

    }

}