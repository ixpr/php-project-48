<?php

namespace Differ\Formatters;

class Json
{
    public $diffArray;

    public function __construct(array $diffArray)
    {
        $this->diffArray = $diffArray;
    }

    public function format()
    {
        return json_encode($this->diffArray) . "\n";
    }
}
