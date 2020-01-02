<?php

namespace Folklore\EloquentJson;

class ValidationException extends \Exception
{
    protected $schemaErrors;

    public function __construct($schemaErrors, $namespace = null)
    {
        $this->schemaErrors = $schemaErrors;
        $this->namespace = $namespace;

        parent::__construct(
            'Error(s) while validating the schema:' .
                PHP_EOL .
                $this->getDetailedMessage($schemaErrors)
        );
    }

    public function schemaErrors()
    {
        return $this->schemaErrors;
    }

    protected function getDetailedMessage($schemaErrors)
    {
        $lines = [];
        $namespace = !is_null($this->namespace) ? $this->namespace . '.' : '';
        foreach ($schemaErrors as $key => $value) {
            $messages = (array) $value;
            foreach ($messages as $message) {
                $lines[] = sprintf('[%s%s]: %s', $namespace, $key, $message);
            }
        }
        return implode(PHP_EOL, $lines);
    }
}
