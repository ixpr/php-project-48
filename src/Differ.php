<?php

namespace Differ\Differ;

use Differ\Parsers;
use Differ\Formatters;
use Funct\Collection;

const ERROR_MESSAGE = "Please check file URL or format \n";

function process(object $data): string
{
    $vars = get_object_vars($data);

    $formatName = $vars['args']['--format'];
    $firstFileUrl = $vars['args']['<firstFile>'];
    $secondFileUrl = $vars['args']['<secondFile>'];

    return genDiff($firstFileUrl, $secondFileUrl, $formatName);
}

function genDiff(string $pathToFile1, string $pathToFile2, string $formatName = 'stylish'): string
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

        $diffArr = createDiffArray($firstFile, $secondFile, $formatName == 'plain');

        editDiffArray($diffArr);
        sortDiffArray($diffArr);
        // print_r($diffArr);

        return Formatters\format($formatName, $diffArr);
    } else {
        return ERROR_MESSAGE;
    }
}

function createDiffArray(
    mixed $node,
    mixed $secondFile,
    bool $formatIsPlain = false,
    bool $isDiffTopLev = false
): mixed {
    if (!is_array($node)) {
        return $node;
    }

    return array_reduce(
        array_keys($node),
        function ($newAcc, $child) use ($node, $secondFile, $formatIsPlain, $isDiffTopLev) {
            $inBoth = 'shared';
            $inFirst = 'deleted';
            $inSecond = 'inserted';
            $updated = 'updated';

            if (is_array($secondFile)) {
                if (array_key_exists($child, $secondFile)) {
                    $diff = $inBoth;
                } else {
                    if (!$isDiffTopLev) {
                        $diff = $inFirst;
                        $isDiffTopLev = true;
                    } else {
                        $diff = $inBoth;
                    }
                }

                // adding items present only in second
                $secondKeys = array_keys($secondFile);
                $secondUnique = array_diff($secondKeys, array_keys($node));
                foreach ($secondUnique as $unique) {
                    if (array_search($unique, array_column($newAcc, 'name')) === false) {
                        $newAcc[] = [
                            'name' => $unique,
                            'diff' => $inSecond,
                            'value' => $secondFile[$unique]
                        ];
                    }
                }
            } else {
                $diff = $inBoth;
            }

            $processed = createDiffArray($node[$child], $secondFile[$child] ?? [], $formatIsPlain, $isDiffTopLev);

            // adding updated items
            if (
                is_array($node[$child]) && (
                    array_key_exists($child, $secondFile) &&
                    !is_array($secondFile[$child])
                )
                ||
                !is_array($node[$child]) && (
                    is_array($secondFile) &&
                    array_key_exists($child, $secondFile) &&
                    $secondFile[$child] !== $node[$child]
                )
            ) {
                if ($formatIsPlain) {
                    $newAcc[] = [
                        'name' => $child,
                        'diff' => $updated,
                        'value' => [
                            'old' => $node[$child],
                            'new' => $secondFile[$child]
                        ]
                    ];
                } else {
                    $newAcc[] = [
                        'name' => $child,
                        'diff' => $inFirst,
                        'value' => $node[$child]
                    ];
                    $newAcc[] = [
                        'name' => $child,
                        'diff' => $inSecond,
                        'value' => $secondFile[$child]
                    ];
                }
                $diff = $inFirst;
            }

            // adding all other items (shared or present only in first)
            if (array_search($child, array_column($newAcc, 'name')) === false) {
                if (
                    is_array($secondFile) &&
                    !array_key_exists($child, $secondFile) &&
                    !in_array($diff, [$inFirst, $inSecond])
                ) {
                    $newAcc[] = [
                        'name' => $child,
                        'value' => $processed
                    ];
                } else {
                    $newAcc[] = [
                        'name' => $child,
                        'diff' => $diff,
                        'value' => $processed
                    ];
                }
            };

            return $newAcc;
        },
        []
    );
}

function editDiffArray(array &$diffArray): void
{
    foreach ($diffArray as $key => &$subArray) {
        if (is_array($subArray) && array_key_exists('name', $subArray)) {
            if (array_key_exists('value', $subArray) && is_array($subArray['value'])) {
                if (
                    count($subArray['value']) === 1 &&
                    !is_array($subArray['value'][array_key_first($subArray['value'])])
                ) {
                    $subArray['value'][] = [
                        'name' => array_key_first($subArray['value']),
                        'value' => $subArray['value'][array_key_first($subArray['value'])]
                    ];
                    unset($subArray['value'][array_key_first($subArray['value'])]);
                }
                editDiffArray($subArray['value']);
            }
        } else {
            $diffArray[] = [
                'name' => $key,
                'value' => $subArray
            ];
            unset($diffArray[$key]);
        }
    }
}

function sortDiffArray(array &$diffArray): void
{
    array_multisort(array_column($diffArray, 'name'), SORT_ASC, $diffArray);

    foreach ($diffArray as &$subArray) {
        if (array_key_exists('value', $subArray) && is_array($subArray['value'])) {
            sortDiffArray($subArray['value']);
        }
    }
}
