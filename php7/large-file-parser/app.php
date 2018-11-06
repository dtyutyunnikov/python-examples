<?php

/**
 * 2692698 records / 1093 matches
 * Test 1 (main process): 18m42.376s
 * Test 2 (10 workers): 1m50.108s
 */

ini_set('memory_limit', '8M');

(new class {
    const SCRIPT_NAME = 'large-file-parser';
    const DATA_SOURCE = __DIR__ . DIRECTORY_SEPARATOR . 'data.csv';

    private $workers = null;

    public function run()
    {
        try {
            $this->main();
        } catch (Exception $e) {
            $this->log($e->getMessage(), LOG_ERR);
        }
    }

    private function main()
    {
        $this->startWorkers(20);
        
        $i = 1;
        foreach ($this->readSourceFile() as $user) {
            if ($this->checkSomeConditions($user)) {
                $this->delegate($user);
            }
            $this->progress($i++);
        }
        $this->progress($i, true);

        $this->stopWorkers();
    }

    private function readSourceFile()
    {
        $f = new SplFileObject(self::DATA_SOURCE);
        $f->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        while ($values = $f->fgetcsv()) {
            $user = (object) array_combine([
                'name', 'phone', 'birthday', 'street', 'city', 'state', 'zip'
            ], $values);
            yield $user;
        }
    }

    /**
     * @param stdClass $user
     * @return bool
     */
    private function checkSomeConditions(stdClass $user)
    {
        return ($user->state == 'NY'
            && DateTime::createFromFormat('m/d/Y', $user->birthday)->diff(new DateTime)->y > 97);
    }

    /**
     * @param stdClass $user
     */
    private function delegate(stdClass $user)
    {
        $worker = $this->workers->dequeue();
        fwrite($worker['pipes'][0], json_encode($user) . PHP_EOL);
        $this->workers->enqueue($worker);
    }

    /**
     * @param int $num
     */
    private function startWorkers($num = 3)
    {
        $this->workers = new SplQueue;
        for ($i = 0; $i < $num; $i++) {
            $process = proc_open('php worker.php', [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
            ], $pipes);
            if (is_resource($process)) {
                $this->workers->enqueue([
                    'resource' => $process,
                    'pipes'    => $pipes,
                ]);
            }
        }
    }

    private function stopWorkers()
    {
        foreach ($this->workers as $worker) {
            fwrite($worker['pipes'][0], '0');
        }
        foreach ($this->workers as $worker) {
            proc_close($worker['resource']);
        }
    }

    /**
     * @param string $message
     * @param int $priority (@see LOG_* constants)
     */
    private function log($message, $priority = LOG_NOTICE)
    {
        openlog(self::SCRIPT_NAME, LOG_PID | LOG_PERROR, LOG_USER);
        syslog($priority, $message);
        closelog();
    }

    /**
     * @param int $iteration
     * @param bool $end
     */
    private function progress($iteration, $end = false)
    {
        if (!$end && $iteration % 1000 !== 0) {
            return;
        }
        printf(
            "Progress: %d records (Memory usage: %.1fMB)\r" ,
            $iteration,
            (memory_get_usage() / (1024 * 1024))
        );
        if ($end) {
            echo PHP_EOL;
        }
    }
})->run();
