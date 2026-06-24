<?php

namespace App\Logging\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class MaskSensitiveDataProcessor implements ProcessorInterface
{
    /**
     * List of sensitive keys that must be masked.
     */
    protected array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'otp',
        'token',
        'access_token',
        'refresh_token',
        'client_secret',
        'secret',
        'cvv',
        'pin',
        'card_number',
        'key',
        'private_key',
        'authorization',
    ];

    /**
     * Process the log record.
     *
     * @param  LogRecord  $record
     * @return LogRecord
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;
        if (!empty($context)) {
            $record = $record->with(context: $this->maskArray($context));
        }

        $extra = $record->extra;
        if (!empty($extra)) {
            $record = $record->with(extra: $this->maskArray($extra));
        }

        return $record;
    }

    /**
     * Recursively mask sensitive keys in an array.
     *
     * @param  array  $data
     * @return array
     */
    protected function maskArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->maskArray($value);
            } elseif (in_array(strtolower($key), $this->sensitiveKeys)) {
                $data[$key] = '[MASKED]';
            }
        }

        return $data;
    }
}
