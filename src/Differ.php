<?php

namespace Differ\Differ;

use Differ\Parsers;
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
    $extensions = ['json', 'yml', 'yaml'];

    $file1Info = pathinfo($pathToFile1);
    $file2Info = pathinfo($pathToFile2);

    if (
        file_exists($pathToFile1) &&
        file_exists($pathToFile2) &&
        in_array($file1Info['extension'], $extensions) &&
        in_array($file2Info['extension'], $extensions)
    ) {
        $fileArray = function ($pathToFile, $fileInfo) {
            $contents = file_get_contents($pathToFile);
            if (in_array($fileInfo['extension'], ['json'])) {
                $parsedArray = Parsers\parseJson($contents);
            } elseif (in_array($fileInfo['extension'], ['yml', 'yaml'])) {
                $parsedArray = Parsers\parseYaml($contents);
            }

            $booledArray = [];
            foreach ($parsedArray as $key => $value) {
                if (is_bool($value)) {
                    $booledArray[$key] = $value ? 'true' : 'false';
                } else {
                    $booledArray[$key] = (string) $value;
                }
            }

            return $booledArray;
        };

        $firstFile = $fileArray($pathToFile1, $file1Info);
        $secondFile = $fileArray($pathToFile2, $file2Info);

        $keys = array_unique(array_merge(array_keys($firstFile), array_keys($secondFile)));
        $keys = Collection\sortBy($keys, fn($num) => $num);

        $mapped = array_map(function ($item) use ($firstFile, $secondFile) {
            $result = [];
            if (
                array_key_exists($item, $firstFile) && array_key_exists($item, $secondFile)
                && $firstFile[$item] === $secondFile[$item]
            ) {
                $result[] = "    {$item}: {$firstFile[$item]}";
            } else {
                if (array_key_exists($item, $firstFile)) {
                    $result[] = "  - {$item}: {$firstFile[$item]}";
                }
                if (array_key_exists($item, $secondFile)) {
                    $result[] = "  + {$item}: {$secondFile[$item]}";
                }
            }
            return $result;
        }, $keys);

        $mapped = Collection\flatten($mapped);

        return "{\n" . implode(" \n", $mapped) . "\n}\n";
    } else {
        return "Please check file URL or format \n";
    }
}
