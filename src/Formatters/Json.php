<?php

namespace Differ\Formatters;

class Json
{
    public array $diffArray;

    public function __construct(array $diffArray)
    {
        $this->diffArray = $diffArray;
    }

    public function format(): string
    {
        return json_encode($this->diffArray) . "\n";
    }
}
