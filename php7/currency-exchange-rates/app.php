<?php

(new class {
    const TIME_INTERVAL = 10; // Minutes
    const FILE_LOCATION = 'http://www.cbr.ru/scripts/XML_daily.asp';
//    const FILE_LOCATION = __DIR__ . '/XML_daily.xml';

    protected $charCodes = ['USD', 'EUR'];

    public function run()
    {
        $data = $this->loadData();
        $this->output($data);
    }

    public function loadData()
    {
        $raw = file_get_contents(self::FILE_LOCATION, false, stream_context_create(['http' => ['header' => 'Accept: application/xml']]));
        $xml = simplexml_load_string($raw);

        $result = ['Date' => (string) $xml->attributes()->Date];
        foreach ($xml->Valute as $row) {
            if (!in_array($row->CharCode, $this->charCodes)) {
                continue;
            }
            $result[(string) $row->CharCode] = (string) $row->Value;
        }

        return $result;
    }

    public function output($data)
    {
        echo self::FILE_LOCATION . PHP_EOL;
        foreach ($data as $name => $value) {
            echo $name, ': ', $value, PHP_EOL;
        }
    }
})->run();
