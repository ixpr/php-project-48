<?php

namespace Differ\Differ;

use Funct\Collection;

function process(object $data): string
{
    $vars = get_object_vars($data);

    $format = $vars['args']['--format'];
    $firstFileUrl = $vars['args']['<firstFile>'];
    $secondFileUrl = $vars['args']['<secondFile>'];

    return genDiff($firstFileUrl, $secondFileUrl);
}

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $firstFileJson = file_exists($pathToFile1) ? file_get_contents($pathToFile1) : null;
    $secondFileJson = file_exists($pathToFile2) ? file_get_contents($pathToFile2) : null;

    if ($firstFileJson && $secondFileJson) {
        $firstFile = parseJson($firstFileJson);
        $secondFile = parseJson($secondFileJson);

        $keys = array_unique(array_merge(array_keys($firstFile), array_keys($secondFile)));
        $keys = Collection\sortBy($keys, fn($num) => $num);

        $mapped = array_map(function ($item) use ($firstFile, $secondFile) {
            if (array_key_exists($item, $firstFile) && array_key_exists($item, $secondFile)) {
                if ($firstFile[$item] === $secondFile[$item]) {
                    return "    {$item}: {$firstFile[$item]}";
                } else {
                    return [
                        "  - {$item}: {$firstFile[$item]}",
                        "  + {$item}: {$secondFile[$item]}"
                    ];
                }
            } elseif (array_key_exists($item, $firstFile)) {
                return "  - {$item}: {$firstFile[$item]}";
            } elseif (array_key_exists($item, $secondFile)) {
                return "  + {$item}: {$secondFile[$item]}";
            }
        }, $keys);

        $mapped = Collection\flatten($mapped);

        return "{\n" . implode(" \n", $mapped) . "\n}\n";
    } else {
        return "Please check file URL \n";
    }
}

function parseJson(string $json): array
{
    $array = json_decode($json, true);

    $stringified = [];
    foreach ($array as $key => $value) {
        if (is_bool($value)) {
            $stringified[$key] = $value ? 'true' : 'false';
        } else {
            $stringified[$key] = (string) $value;
        }
    }

    return $stringified;
}
