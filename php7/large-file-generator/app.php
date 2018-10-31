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
    const PROGRESSBAR_SIZE = 50;
    const RESULT_FILE      = __DIR__ . DIRECTORY_SEPARATOR . 'data.csv';

    private $qty = 100;

    /**
     * @param array $args
     */
    public function run(array $args)
    {
        $this->qty = $args['qty'] ?? 100;

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
            if ($i % ceil($this->qty / self::PROGRESSBAR_SIZE) == 0) {
                $this->output($i);
            }
            $line = [
                $faker->name,
                $faker->phoneNumber,
                $faker->dateTimeThisCentury->format('m/d/Y'),
                $faker->streetAddress,
                $faker->city,
                $faker->stateAbbr,
                $faker->postcode,
            ];
            fwrite($f, '"' . join('","', $line) . '"' . PHP_EOL);
        }
        fclose($f);
        $this->output($this->qty, true);
    }

    /**
     * @param int $iteration
     * @param bool $end
     */
    private function output($iteration, $end = false)
    {
        $percent  = $iteration / $this->qty;
        $progress = array_fill(0, floor(self::PROGRESSBAR_SIZE * $percent), '=');
        if (count($progress) < self::PROGRESSBAR_SIZE) {
            $progress[] = '>';
        }
        $progress += array_fill(count($progress), self::PROGRESSBAR_SIZE - count($progress), '-');

        printf("Progress: [%s] (%.1f%%)\r" , join('', $progress), $percent  * 100);
        if ($end) {
            echo PHP_EOL;
        }
    }

    /**
     * @param string $message
     * @param int $priority (@see LOG_* constants)
     */
    private function log($message, $priority = LOG_NOTICE)
    {
        openlog('large-file-generator', LOG_PID | LOG_PERROR, LOG_USER);
        syslog($priority, $message);
        closelog();
    }
})->run(getopt('', ['qty:']));
