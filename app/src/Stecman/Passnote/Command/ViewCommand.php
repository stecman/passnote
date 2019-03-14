<?php

namespace Stecman\Passnote\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ViewCommand extends Command
{
    /**
     * Logged in user
     * @var \User
     */
    protected $user;

    /**
     * Passphrase for the logged in user's account key
     * @var string
     */
    protected $accountKeyPassphrase;

    protected function configure()
    {
        $this
            ->setName('view')
            ->setDescription('Search and view objects')
            ->addArgument(
                'user',
                InputArgument::OPTIONAL,
                'Account name to push object to.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = trim($input->getArgument('user'));

        // Prompting for user if not provided
        if ($email === '') {
            $question = new Question('User: ');
            $email = $this->getQuestionHelper()->ask($input, $output, $question);
        }

        /** @var \User $user */
        $user = \User::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ]);

        if ($user) {
            $question = new Question('Password: ');
            $question->setHidden(true);
            $password = $this->getQuestionHelper()->ask($input, $output, $question);

            if (!$user->validatePassword( $password )) {
                die("Password incorrect\n");
            }

            $this->user = $user;
            $this->accountKeyPassphrase = $user->getAccountKeyPassphrase($password);
            unset($password);

            $this->startConsole($input, $output);
        } else {
            die("No user found for $email\n");
        }
    }

    protected function startConsole(InputInterface $input, OutputInterface $output)
    {
        while (true) {
            $question = new Question('Search: ');
            $search = $this->getQuestionHelper()->ask($input, $output, $question);

            if ($search === null) {
                exit(0);
            }

            $results = $this->searchRecords($search);

            if (!count($results)) {
                echo "No records found\n";
                continue;
            }

            echo "\nSelect a record to show (or enter nothing to search again):\n\n";

            foreach ($results as $index => $object) {
                echo sprintf("    [%d] %s\n", $index, $object->title);
            }

            $question = new Question("\nItem to display: ");
            $selection = $this->getQuestionHelper()->ask($input, $output, $question);

            if ($selection === null) {
                continue;
            }

            $this->showObject($results[(int) $selection]);
        }
    }

    /**
     * Search the title and description of objects, returning the first 10 matches
     */
    protected function searchRecords($search)
    {
        $search = trim($search);

        $query = \StoredObject::query()
            ->limit(10)
            ->orderBy('created DESC')
            ->where('user_id = :user_id: AND parent_id IS NULL', [
                'user_id' => $this->user->id
            ]);

        // Crude search for each term using LIKE
        foreach (explode(' ', $search) as $term) {
            $query->andWhere('title LIKE :term: OR description LIKE :term:', [
                'term' => "%$term%"
            ]);
        }

        $results = $query->execute();
        $array = [];

        foreach ($results as $result) {
            $array[] = $result;
        }

        return $array;
    }

    /**
     * Decrypt and show the contents of an object
     */
    protected function showObject(\StoredObject $object)
    {
        if ($object->getKeyId() === $this->user->accountKey_id) {
            $content = $object->getContent($this->accountKeyPassphrase);
            $checksumValid = $object->isChecksumValid($content, $this->accountKeyPassphrase);
        } else {
            echo "Sorry, non-account key objects not implemented here yet\n";
            die();
        }

        $format = <<<EOD
Title: %s
Description: %s
Last Modified: %s

---

%s
EOD;

        // Append warning message to output if checksum doesn't match
        if (!$checksumValid) {
            $format = "WARNING: The checksum of this record does not match the checksum of the decrypted content\n\n" . $format;
        }

        $this->display(
            sprintf(
                $format,
                $object->title,
                $object->description,
                $object->getDateCreated(),
                $content
            )
        );
    }

    /**
     * Display a string in the program 'less'
     */
    protected function display($string)
    {
        $spec = [
            ['pipe', 'r'],
            STDOUT,
            STDERR
        ];

        $process = proc_open('less', $spec, $pipes);
        fwrite($pipes[0], $string);
        fclose($pipes[0]);

        while (true) {
            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }

            usleep(500000);
        }

        proc_close($process);
    }

    /**
     * @return QuestionHelper
     */
    protected function getQuestionHelper()
    {
        return new QuestionHelper();
    }
}
