<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parseJson(string|false $contents): array
{
    return json_decode($contents, true);
}

function parseYaml(string|false $contents): array
{
    return Yaml::parse($contents);
}
