#!/usr/bin/php
<?php

(function () {
    while (true) {
        $line = fgets(STDIN);
        if ($line === '0') {
            break;
        }
        if (empty($line)) {
            continue;
        }
        $user = json_decode($line);
        // do some resource-intensive actions
        sleep(1);
    }
})();
