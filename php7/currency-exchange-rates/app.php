<?php

/**
 * Arguments:
 * -r --refresh = Force cache update
 * -q --quiet   = Disable output
 *
 * Example:
 * > php app.php --refresh
 *
 * Example of output:
 * > http://www.cbr.ru/scripts/XML_daily.asp
 * > Date: 31.10.2018
 * > USD: 65,7742
 * > EUR: 74,7918
 */

(new class {
    const TIME_INTERVAL = '30 minutes';
    const DATA_SOURCE   = 'http://www.cbr.ru/scripts/XML_daily.asp';
    const CACHE_FILE    = __DIR__ . DIRECTORY_SEPARATOR . 'data.json';

    private $charCodes = ['USD', 'EUR'];

    /**
     * @param array $args
     */
    public function run(array $args)
    {
        try {
            $data = $this->loadData(isset($args['r']) || isset($args['refresh']));
            if (!isset($args['q']) && !isset($args['quiet'])) {
                $this->output($data);
            }
        } catch (Exception $e) {
            $this->log($e->getMessage(), LOG_ERR);
        }
    }

    /**
     * @param bool $refresh
     * @return array
     */
    private function loadData($refresh = false)
    {
        $cache = (file_exists(self::CACHE_FILE) ? json_decode(file_get_contents(self::CACHE_FILE), true) : [
            'lock'        => 0,
            'last_update' => '0000-00-00T00:00:00+00:00',
            'data'        => [],
        ]);

        $now    = new DateTime;
        $update = DateTime::createFromFormat(DATE_W3C, $cache['last_update'])->modify('+' . self::TIME_INTERVAL);
        return (!$refresh && ($cache['lock'] || $update > $now) ? $cache['data'] : $this->updateData($cache));
    }

    /**
     * @param array $cache
     * @throws Exception
     * @return array
     */
    private function updateData(array $cache)
    {
        if (!is_writable(self::CACHE_FILE)) {
            throw new Exception('File "' . self::CACHE_FILE . '" is not writable.');
        }
        $this->lockCacheFile($cache);

        $raw = file_get_contents(self::DATA_SOURCE, false, stream_context_create(['http' => ['header' => 'Accept: application/xml']]));
        if (empty($raw) || mb_strpos($raw, '<?xml') !== 0) {
            $this->unlockCacheFile($cache);
            throw new Exception('Incorrect data source response.');
        }
        $xml = simplexml_load_string($raw);

        $result = ['Date' => (string) $xml->attributes()->Date];
        foreach ($xml->Valute as $row) {
            if (!in_array($row->CharCode, $this->charCodes)) {
                continue;
            }
            $result[(string) $row->CharCode] = (string) $row->Value;
        }

        if (count($result) != 3) {
            $this->unlockCacheFile($cache);
            throw new Exception('Parser error. Perhaps the format of data was changed.');
        }

        $cache['lock']        = 0;
        $cache['last_update'] = (new DateTime)->format(DATE_W3C);
        $cache['data']        = $result;
        file_put_contents(self::CACHE_FILE, json_encode($cache));

        return $result;
    }

    /**
     * @param array $cache
     */
    private function lockCacheFile(array $cache)
    {
        $cache['lock'] = 1;
        file_put_contents(self::CACHE_FILE, json_encode($cache));
    }

    /**
     * @param array $cache
     */
    private function unlockCacheFile(array $cache)
    {
        $cache['lock'] = 0;
        file_put_contents(self::CACHE_FILE, json_encode($cache));
    }

    /**
     * @param string $message
     * @param int $priority (@see LOG_* constants)
     */
    private function log($message, $priority = LOG_NOTICE)
    {
        openlog("currency-exchange-rates", LOG_PID | LOG_PERROR, LOG_USER);
        syslog($priority, $message);
        closelog();
    }

    /**
     * @param array $data
     */
    private function output($data)
    {
        echo self::DATA_SOURCE . PHP_EOL;
        foreach ($data as $name => $value) {
            echo $name, ': ', $value, PHP_EOL;
        }
    }
})->run(getopt('rq', ['refresh', 'quiet']));
