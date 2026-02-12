<?php

namespace Differ\Formatters;

function format(string $format, array $diffArray): string
{
    switch ($format) {
        case 'plain':
            $class = new Plain($diffArray);
            break;

        case 'json':
            $class = new Json($diffArray);
            break;

        case 'stylish':
        default:
            $class = new Stylish($diffArray);
            break;
    }

    return $class->format();
}
