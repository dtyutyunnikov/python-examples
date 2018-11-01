<?php

(new class {
    const SCRIPT_NAME = 'large-file-parser';
    const DATA_SOURCE = __DIR__ . DIRECTORY_SEPARATOR . 'data.csv';
    const RESULT_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'data.json';

    public function run()
    {
        try {
            $this->analyze();
        } catch (Exception $e) {
            $this->log($e->getMessage(), LOG_ERR);
        }
    }

    private function analyze()
    {
        $result = [];

        $i = 1;
        foreach ($this->readFile() as $user) {
            if ($user->state == 'NY'
                && DateTime::createFromFormat('m/d/Y', $user->birthday)->diff(new DateTime)->y > 95
            ) {
                $result[] = $user;
            }
            $this->progress($i++);
        }

        if (!is_writable(self::RESULT_FILE)) {
            throw new Exception('File "' . self::RESULT_FILE . '" is not writable.');
        }
        file_put_contents(
            __DIR__ . '/data.json',
            str_replace('},{', '},' . PHP_EOL . '{', json_encode($result))
        );

        $this->progress($i, true);
        echo count($result), PHP_EOL;
    }

    private function readFile()
    {
        if (!file_exists(self::DATA_SOURCE)) {
            throw new Exception('File "' . self::DATA_SOURCE . '" doesn\'t exist.');
        }

        $f = fopen(self::DATA_SOURCE, 'r');
        while ($userValues = fgetcsv($f)) {
            $user = (object) array_combine([
                'name', 'phone', 'birthday', 'street', 'city', 'state', 'zip'
            ], $userValues);
            yield $user;
        }
        fclose($f);
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
