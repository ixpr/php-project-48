<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parseJson(string $contents): array
{
    return json_decode($contents, true);
}

function parseYaml(string $contents): array
{
    return Yaml::parse($contents);
}
