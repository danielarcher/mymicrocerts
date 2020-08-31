<?php

namespace App\Logging;

use DateTimeZone;
use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param Logger $logger
     *
     * @return void
     */
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $formatter = new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context% %extra% \n",
                "Y-m-d H:i:s"
            );
            $formatter->ignoreEmptyContextAndExtra(true);
            $handler->setFormatter($formatter);

        }
        $loggerTimeZone = new DateTimeZone('America/New_York');
        /** @var \Monolog\Logger $monolog */
        $monolog = $logger->getLogger();
        $monolog->setTimezone($loggerTimeZone);

        $monolog->pushProcessor(new MemoryUsageProcessor());
        $monolog->pushProcessor(new UidProcessor(32));
        $monolog->pushProcessor(new PsrLogMessageProcessor());
        $monolog->pushProcessor(new WebProcessor());
        $monolog->useMicrosecondTimestamps(false);

    }
}
