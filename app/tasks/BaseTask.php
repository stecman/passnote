<?php


abstract class BaseTask extends \Phalcon\CLI\Task
{
    /**
     * Prompt user for input through STDIN
     */
    protected function promptInput($prompt, $hideInput = false)
    {
        fwrite(STDIN, $prompt);
        $options = $hideInput ? '-s' : '';
        $value = trim(`bash -c 'read $options uservalue && echo \$uservalue'`);

        if ($hideInput) {
            fwrite(STDIN, "\n");
        }

        return $value !== '' ? $value : null;
    }

    protected function dumpMessages(array $messages)
    {
        $out = '';

        /** @var \Phalcon\Mvc\Model\Message $message */
        foreach ($messages as $message) {
            $className = get_class($message->getModel());

            $out .= <<<"EOD"

Error: {$message->getMessage()}
Type: {$message->getType()}
Model: {$className}
Field: {$message->getField()}

EOD;
        }

        return $out;

    }

    /**
     * @param $email
     * @return User
     */
    protected function getUserByEmail($email)
    {
        $user = User::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ]);

        return $user;
    }
}
