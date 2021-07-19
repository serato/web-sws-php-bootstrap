<?php

namespace Serato\SwsApp\Utils;

use Monolog\Formatter\JsonFormatter;
use DateTime;

class MonologJsonFormatter extends JsonFormatter
{
    public const SIMPLE_DATE = "Y-m-d H:i:s";

    protected $dateFormat;
    /**
     * @param int $batchMode
     * @param bool $appendNewline
     */
    public function __construct(
        int $batchMode = parent::BATCH_MODE_JSON,
        bool $appendNewline = true,
        string $dateFormat = self::SIMPLE_DATE
    ) {
        $this->dateFormat = $dateFormat;
        parent::__construct($batchMode, $appendNewline);
    }

    /**
     * Formats the record and adds the Date to it
     */
    public function format(array $record): string
    {
        $dateTime = '';
        if ($record['datetime'] !== null && $record['datetime'] instanceof DateTime) {
            $dateTime = $record['datetime']->format($this->dateFormat);
        }
        return "[$dateTime] " . parent::format($record);
    }
}
