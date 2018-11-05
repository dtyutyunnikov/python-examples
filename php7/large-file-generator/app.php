<?php

/**
 * Required:
 *   "fzaninotto/faker"
 *
 * Arguments:
 * --qty [int]   = Quantity of records
 */

use Faker\Factory;
use Sandbox\AppTrait;

require '../vendor/autoload.php';

(new class {
    use AppTrait;

    const SCRIPT_NAME      = 'large-file-generator';
    const RESULT_FILE      = __DIR__ . DIRECTORY_SEPARATOR . 'data.csv';
    const PROGRESSBAR_SIZE = 50;

    private $qty   = 100;
    private $cache = [];

    /**
     * @param array $args
     */
    protected function init($args = [])
    {
        $this->qty   = $args['qty'] ?? 100;
        $this->cache = [
            'progressbar_step' => ceil($this->qty / self::PROGRESSBAR_SIZE),
            'result_size'      => 0,
        ];
    }

    protected function main()
    {
        if (!is_writable(self::RESULT_FILE)) {
            throw new Exception('File "' . self::RESULT_FILE . '" is not writable.');
        }

        $faker = Factory::create();

        $file = new SplFileObject(self::RESULT_FILE, 'w');
        for ($i = 0; $i < $this->qty; $i++) {
            $this->cache['result_size'] += $file->fputcsv([
                $faker->name,
                $faker->phoneNumber,
                $faker->dateTimeThisCentury->format('m/d/Y'),
                $faker->streetAddress,
                $faker->city,
                $faker->stateAbbr,
                $faker->postcode,
            ]);
            $this->progress($i);
        }
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
        $exp  = floor(log($bytes, 1024)) | 0;
        return round($bytes / pow(1024, $exp), $precision) . $unit[$exp];
    }
})->run(getopt('', ['qty:']));
