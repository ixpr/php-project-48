<?php

namespace Differ\Differ;

use Differ\Parsers;
use Funct\Collection;

const ERROR_MESSAGE = "Please check file URL or format \n";

function process(object $data): string
{
    $vars = get_object_vars($data);

    $format = $vars['args']['--format'];
    $firstFileUrl = $vars['args']['<firstFile>'];
    $secondFileUrl = $vars['args']['<secondFile>'];

    return genDiff($firstFileUrl, $secondFileUrl, $format);
}

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
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
                    $booledArray[$key] = $value;
                }
            }

            return $booledArray;
        };

        $firstFile = $fileArray($pathToFile1, $file1Info);
        $secondFile = $fileArray($pathToFile2, $file2Info);

        $diffArr = processDiff($firstFile, $secondFile);
        sortArray($diffArr);

        return "{\n" . formatDiff($diffArr, '  ', '', '', $format) . "}\n";
    } else {
        return ERROR_MESSAGE;
    }
}

function isAssoc(array $arr): bool
{
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function sortArray(array &$arr): void
{
    if (isAssoc($arr)) {
        ksort($arr);
    }
    foreach ($arr as &$a) {
        if (is_array($a)) {
            sortArray($a);
        }
    }
}

function processDiff(
    mixed $node,
    mixed $secondFile,
    bool $isDiffTopLev = false
): mixed {
    if (!is_array($node)) {
        return $node;
    }

    return array_reduce(array_keys($node), function ($newAcc, $child) use ($node, $secondFile, $isDiffTopLev) {
        $inBoth = '|0';
        $inFirst = '|1';
        $inSecond = '|2';

        if (is_array($secondFile)) {
            if (array_key_exists($child, $secondFile)) {
                $prefix = $inBoth;
            } else {
                if (!$isDiffTopLev) {
                    $prefix = $inFirst;
                    $isDiffTopLev = true;
                } else {
                    $prefix = $inBoth;
                }
            }

            $secondKeys = array_keys($secondFile);
            $secondUnique = array_diff($secondKeys, array_keys($node));
            foreach ($secondUnique as $unique) {
                $newAcc[$unique . $inSecond] = $secondFile[$unique];
            }
        } else {
            $prefix = $inBoth;
        }

        $processed = processDiff($node[$child], $secondFile[$child] ?? [], $isDiffTopLev);

        if (is_array($node[$child])) {
            if (
                array_key_exists($child, $secondFile) &&
                !is_array($secondFile[$child])
            ) {
                $newAcc[$child . $inSecond] = $secondFile[$child];
                $prefix = $inFirst;
            }
        } else {
            if (
                is_array($secondFile) &&
                array_key_exists($child, $secondFile) &&
                $secondFile[$child] !== $node[$child]
            ) {
                $prefix = $inFirst;
                $newAcc[$child . $inSecond] = $secondFile[$child];
            }
        }

        $newAcc[$child . $prefix] = $processed;

        return $newAcc;
    }, []);
}

function formatDiff(
    array $node,
    string $replacer,
    string $acc,
    string $subreplacer,
    string $format,
    bool $isFirstLvl = true
): string {
    return array_reduce(
        array_keys($node),
        function ($newAcc, $child) use ($node, $replacer, $subreplacer, $format, $isFirstLvl) {
            if ($isFirstLvl) {
                $replacerBlock = "{$replacer}";
            } else {
                $replacerBlock = "{$replacer}{$subreplacer}";
            }
            if (!is_array($node[$child])) {
                $formattedKey = formatValue($child, true);
                $formattedValue = formatValue($node[$child]);

                $newAcc .= "{$replacerBlock}{$subreplacer}{$formattedKey}: {$formattedValue}\n";
                return $newAcc;
            } else {
                $formattedKey = formatValue($child, true);
                $newAcc .= "{$replacerBlock}{$subreplacer}{$formattedKey}: {\n";
                return formatDiff($node[$child], $replacer, $newAcc, $subreplacer . $replacer, $format, false) .
                "{$replacer}{$replacer}{$subreplacer}{$subreplacer}}\n";
            }
        },
        $acc
    );
}

function formatValue(mixed $value, bool $isKey = false): string
{
    $type = gettype($value);
    switch ($type) {
        case 'boolean':
            $result = $value === true ? "true" : "false";
            break;

        case 'NULL':
            $result = 'null';
            break;

        case 'string':
        case 'integer':
        default:
            $result = $value;
            break;
    }

    if ($isKey) {
        if (str_contains($result, '|1')) {
            $result = str_replace('|1', '', $result);
            $result = '- ' . $result;
        } elseif (str_contains($result, '|2')) {
            $result = str_replace('|2', '', $result);
            $result = '+ ' . $result;
        } else {
            $result = str_replace('|0', '', $result);
            $result = '  ' . $result;
        }
    }

    return $result;
}
