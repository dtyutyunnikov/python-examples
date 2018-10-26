<?php

(new class {
    const TIME_INTERVAL = '10 minutes';
    const FILE_LOCATION = 'http://www.cbr.ru/scripts/XML_daily.asp';
//    const FILE_LOCATION = __DIR__ . '/XML_daily.xml';
    const CACHE_FILE    = __DIR__ . '/data.json';

    protected $charCodes = ['USD', 'EUR'];

    public function run()
    {
        $data = $this->loadData();
        $this->output($data);
    }

    private function loadData()
    {
        $cache = (file_exists(self::CACHE_FILE) ? json_decode(file_get_contents(self::CACHE_FILE), true) : [
            'lock'        => 0,
            'last_update' => '0000-00-00T00:00:00+00:00',
            'data'        => [],
        ]);

        $now    = new DateTime;
        $update = DateTime::createFromFormat(DATE_W3C, $cache['last_update'])->modify('+' . self::TIME_INTERVAL);
        return ($cache['lock'] || $update > $now ? $cache['data'] : $this->updateData($cache));
    }

    private function updateData(array $cache)
    {
        $cache['lock'] = 1;
        file_put_contents(self::CACHE_FILE, json_encode($cache));

        $raw = file_get_contents(self::FILE_LOCATION, false, stream_context_create(['http' => ['header' => 'Accept: application/xml']]));
        $xml = simplexml_load_string($raw);

        $result = ['Date' => (string) $xml->attributes()->Date];
        foreach ($xml->Valute as $row) {
            if (!in_array($row->CharCode, $this->charCodes)) {
                continue;
            }
            $result[(string) $row->CharCode] = (string) $row->Value;
        }

        $cache['lock']        = 0;
        $cache['last_update'] = (new DateTime)->format(DATE_W3C);
        $cache['data']        = $result;
        file_put_contents(self::CACHE_FILE, json_encode($cache));

        return $result;
    }

    private function output($data)
    {
        echo self::FILE_LOCATION . PHP_EOL;
        foreach ($data as $name => $value) {
            echo $name, ': ', $value, PHP_EOL;
        }
    }
})->run();
