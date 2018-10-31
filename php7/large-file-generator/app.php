<?php

/**
 * Required:
 *   "fzaninotto/faker"
 *
 * Arguments:
 * --qty [int]   = Quantity of records
 */

use Faker\{Generator, Factory};

require '../vendor/autoload.php';

(new class {
    const SCRIPT_NAME      = 'large-file-generator';
    const RESULT_FILE      = __DIR__ . DIRECTORY_SEPARATOR . 'data.csv';
    const PROGRESSBAR_SIZE = 50;

    private $qty = 100;

    private $cache = [];

    /**
     * @param array $args
     */
    public function run(array $args)
    {
        $this->qty   = $args['qty'] ?? 100;
        $this->cache = [
            'progressbar_step' => ceil($this->qty / self::PROGRESSBAR_SIZE),
            'result_size'      => 0,
        ];

        try {
            $this->generateFile(Factory::create());
        } catch (Exception $e) {
            $this->log($e->getMessage(), LOG_ERR);
        }
    }

    /**
     * @param Generator $faker
     */
    private function generateFile(Generator $faker)
    {
        $f = fopen(self::RESULT_FILE, 'w');
        for ($i = 0; $i < $this->qty; $i++) {
            $line = '"' . join('","', [
                $faker->name,
                $faker->phoneNumber,
                $faker->dateTimeThisCentury->format('m/d/Y'),
                $faker->streetAddress,
                $faker->city,
                $faker->stateAbbr,
                $faker->postcode,
            ]) . '"' . PHP_EOL;
            fwrite($f, $line);
            $this->cache['result_size'] += mb_strlen($line);
            $this->progress($i);
        }
        fclose($f);
        $this->progress($this->qty, true);
    }

    /**
     * @param int $iteration
     * @param bool $end
     */
    private function progress($iteration, $end = false)
    {
        if (!$end && $iteration % $this->cache['progressbar_step'] != 0) {
            return;
        }

        $percent  = $iteration / $this->qty;
        $progress = array_fill(0, floor(self::PROGRESSBAR_SIZE * $percent), '=');
        if (count($progress) < self::PROGRESSBAR_SIZE) {
            $progress[] = '>';
        }
        $progress += array_fill(count($progress), self::PROGRESSBAR_SIZE - count($progress), '-');

        printf(
            "Progress: [%s] (%.1f%%) Result: %d records (%s)\r" ,
            join('', $progress),
            $percent * 100,
            $iteration,
            $this->formatBytes($this->cache['result_size'], 1)
        );
        if ($end) {
            echo PHP_EOL;
        }
    }

    /**
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $unit = ['B', 'KB', 'MB', 'GB'];
        $exp  = floor(log($bytes, 1000)) | 0;
        return round($bytes / pow(1000, $exp), $precision) . $unit[$exp];
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
})->run(getopt('', ['qty:']));
