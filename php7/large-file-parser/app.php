<?php

(new class {
    const SCRIPT_NAME = 'large-file-parser';
    const DATA_SOURCE = __DIR__ . DIRECTORY_SEPARATOR . 'data.csv';
    const RESULT_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'data.json';

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

        (new SplFileObject(self::RESULT_FILE, 'w'))->fwrite(
            str_replace('},{', '},' . PHP_EOL . '{', json_encode($result))
        );

        $this->progress($i, true);
        echo count($result), PHP_EOL;
    }

    private function readFile()
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
