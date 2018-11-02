<?php

namespace Sandbox;

trait AppTrait
{
    protected $scriptName = 'default-name';

    abstract protected function init($args = []);

    abstract protected function main();

    /**
     * @param array $args
     */
    public function run(array $args = [])
    {
        $this->init($args);

        try {
            $this->main();
        } catch (Exception $e) {
            $this->log($e->getMessage(), LOG_ERR);
        }
    }

    /**
     * @param string $message
     * @param int $priority (@see LOG_* constants)
     */
    private function log($message, $priority = LOG_NOTICE)
    {
        openlog($this->scriptName, LOG_PID | LOG_PERROR, LOG_USER);
        syslog($priority, $message);
        closelog();
    }
}
