<?php


class UtilTask extends BaseTask
{
    /**
     * @var float
     */
    protected $timer;

    /**
     * Display time to compute increasing numbers of PBKDF2 iterations
     */
    public function benchmark_kdfAction()
    {
        $password = openssl_random_pseudo_bytes(15);
        $salt = openssl_random_pseudo_bytes(32);

        $iterations = 1000;

        while (true) {
            $this->startTimer();
            openssl_pbkdf2($password, $salt, 32, $iterations);
            $time = $this->getElapsed();
            printf("%d iterations in %.2fms\n", $iterations, $time);

            if ($time < 2) {
                $iterations *= 1.5;
            } else {
                break;
            }
        }

        echo "Put your chosen number of KDF iterations in your local config under 'encryption.kdf.iterations'\n";
    }

    protected function startTimer()
    {
        $this->timer = microtime(true);
    }

    protected function getElapsed()
    {
        return microtime(true) - $this->timer;
    }
}
