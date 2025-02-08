<?php

namespace Puddle\Commands;

use Exception;
use Puddle\Control;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AddCommand extends Command
{

    protected Control $control;

    public function __construct(Control $control, ?string $name = null)
    {
        $this->control = $control;
        parent::__construct($name);
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
        $tagOptions = array_combine($this->control->config()->tags, $this->control->config()->tags);

        $helper = $this->getHelper('question');

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

        // Allow empty input (user presses Enter)
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
        $answers['title'] = $helper->ask($input, $output, $questions['title']);
        $answers['tags'] = $helper->ask($input, $output, $questions['tags']);
        $answers['image'] = $helper->ask($input, $output, $questions['image']);

        // Add the post.
        $result = $this->control->add(title: $answers['title'], tags: $answers['tags'], image: $answers['image']);
        if ($result) {
            $output->writeln("Post created.");
            $output->writeln("Edit the following file to add your mark-down content: {$this->control->config()->content_path}/{$this->control->getLatestPostID()}.md");
        }

        return ($result) ? Command::SUCCESS : Command::FAILURE;

    }

}