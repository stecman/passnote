<?php

namespace Stecman\Passnote\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use User;

class AddCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('add')
            ->setDescription('Add an object')
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'Account name to push object to.'
            )
            ->addOption(
                'title',
                't',
                InputOption::VALUE_REQUIRED,
                'Plain-text title of object.',
                ''
            )
            ->addOption(
                'description',
                'd',
                InputOption::VALUE_REQUIRED,
                'Plain-text description of object',
                ''
            )
            ->addOption(
                'stdin',
                null,
                InputOption::VALUE_NONE,
                'Force reading from stdin instead of guessing input mode.'
            )
            ->addOption(
                'edit',
                'e',
                InputOption::VALUE_NONE,
                'Always open editor, even if content is piped to stdin.'
            )
            ->addOption(
                'markdown',
                'm',
                InputOption::VALUE_NONE,
                'Set object format to markdown instead of plain text.'
            );


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('no-interaction')) {
            $input->setInteractive(true);
        }

        $user = $this->findUser($input->getArgument('user'));

		// Read any content from stdin (blocking)
		$content = null;
		if (!posix_isatty(STDIN)) {
			$content = stream_get_contents(STDIN);
		}

        // Fall back to spawning an editor if STDIN was empty
        if (empty($content) || $input->getOption('edit')) {
            // Bail out if stdin-only was specified
            if ($input->getOption('stdin')) {
                throw new \RuntimeException('No content seen on STDIN with --stdin flag present. Aborting.');
            }

            $content = $this->spawnEditor(
                $this->formatForEditing(
                    $input->getOption('title', ''),
                    $input->getOption('description', ''),
                    $content
                )
            );
        }

        if ($input->getOption('stdin') || $input->getOption('no-interaction')) {
            // Parse without interaction. Failures will lose the document
            $parsed = $this->parseContent($content);
        } else {
            // Parse with interaction. User can retry on failure
            $parsed = $this->parseContentWithRetry($content, $input, $output);
        }

        $parsed['title'] = isset($parsed['title']) ? $parsed['title'] : $input->getOption('title');
        $parsed['description'] = isset($parsed['description']) ? $parsed['description'] : $input->getOption('description');

        if (empty($parsed['title'])) {
            throw new \RuntimeException('Title empty. Aborting.');
        }

        if (empty($parsed['body'])) {
            throw new \RuntimeException('Content empty. Aborting.');
        }


        $object = new \StoredObject();
        $object->key_id = $user->accountKey_id;

        $object->user = $user;
        $object->title = $parsed['title'];
        $object->description = $parsed['description'];
        $object->setContent( $parsed['body'] );

        // Set format to Markdown if specified
        if ($input->getOption('markdown')) {
            $object->setFormat('markdown');
        }

        $object->save();

        $output->writeln("<info>Saved as object #{$object->id}</info>");
    }

    /**
     * Parse and prompt errors until the content parses ok or the user aborts
     * This is to avoid losing a document because of a typo in interactive mode
     */
    protected function parseContentWithRetry($content, InputInterface $input, OutputInterface $output)
    {
        do {
            try {
                return $this->parseContent($content);

            } catch (\Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");
                $output->writeln('Returning to editor in 2 seconds... Ctrl-C to abort.');

                sleep(2);

                $content = $this->spawnEditor($content);
            }
        } while (true);
    }

    protected function parseContent($content)
    {
        // Clean up leading/trailing whitespace
        trim($content);

        // Split into YAML header and body content
        $pattern = <<<'REGEX'
            /^(?:
                ---\n
                (?P<header>(?:\s|\n|.)*?)\n
                ---
            )?
            (?P<body>(?:.|\n|\s)*$)
            /x
REGEX;

        // Build structured data from text blob
        if (!preg_match($pattern, $content, $matches)) {
            throw new \RuntimeException('Failed to match document sections. Aborting.');
        }

        if (empty($matches['header'])) {
            $data = [];
        } else {
            $data = yaml_parse($matches['header']);
        }

        $data['body'] = trim($matches['body']);

        return $data;
    }

    protected function formatForEditing($title, $description, $content = '')
    {
        $header = yaml_emit([
            'title' => $title,
            'description' => $description,
        ], YAML_UTF8_ENCODING);

        // Use a uniform marker for the start and end of the YAML section
        $header = preg_replace('/...\n$/', '---', $header);

        return <<<"EOD"
$header

$content
EOD;
    }

    /**
     * Start the user's default editor with the given document contents
     *
     * The temporary file exists as a file in memory, which should be relatively safe.
     * An encrypted swap partition is recommended to prevent content leaking onto disk in clear-text.
     *
     * @param string $documentContents
     * @return string - content after editing
     */
    protected function spawnEditor($documentContents)
    {
        // Create a file in memory that can be modified by any editor
        $tmpFile = tempnam('/dev/shm', 'passnote-');
        file_put_contents($tmpFile, $documentContents);

        // Push user to editor
        $descriptors = [
            ['file', '/dev/tty', 'r'],
            ['file', '/dev/tty', 'w'],
            ['file', '/dev/tty', 'w']
        ];

        $command = implode(' ', [
            escapeshellarg(getenv('EDITOR')),
            escapeshellarg($tmpFile),
        ]);

        $process = proc_open($command, $descriptors, $pipes);

        // Wait until finished
        do {
            usleep(250000 /* 250ms */);
            $status = proc_get_status($process);
        } while ($status['running']);

        // Grab modified content and clean up
        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        // Bail out if the editor failed to exit cleanly
        if ($status['exitcode'] !== 0) {
            throw new \RuntimeException('Editor exited with a non-zero status. Aborting.');
        }

        return $content;
    }

    /**
     * Find a User record by email address or throw
     * @return User
     */
    protected function findUser($email)
    {
        $user = User::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ]);

        if (!$user) {
            throw new \RuntimeException("No such user '$email'");
        }

        return $user;
    }
}
