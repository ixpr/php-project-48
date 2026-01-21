<?php

namespace Parse;

function process(object $data): void
{
    $vars = get_object_vars($data);

    $format = $vars['args']['--format'];
    $firstFileUrl = $vars['args']['<firstFile>'];
    $secondFileUrl = $vars['args']['<secondFile>'];

    $firstFile = file_exists($firstFileUrl) ? file_get_contents($firstFileUrl) : null;
    $secondFile = file_exists($secondFileUrl) ? file_get_contents($secondFileUrl) : null;

    if ($firstFile && $secondFile) {
        print_r(parseJson($firstFile));
        print_r(parseJson($secondFile));
    } else {
        echo "Please check file URL \n";
    }
}

function parseJson(string $json): array
{
    return json_decode($json, true);
}
