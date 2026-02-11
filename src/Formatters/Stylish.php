<?php

namespace Differ\Formatters;

class Stylish
{
    public $diffArray;

    public function __construct(array $diffArray)
    {
        $this->diffArray = $diffArray;
    }

    public function format()
    {
        return "{\n" . $this->formatDiff($this->diffArray, '  ', '', '') . "}\n";
    }

    private function formatDiff(
        array $node,
        string $replacer,
        string $acc,
        string $subreplacer,
        bool $isFirstLvl = true
    ): string {
        return array_reduce(
            $node,
            function ($newAcc, $child) use ($node, $replacer, $subreplacer, $isFirstLvl) {
                if ($isFirstLvl) {
                    $replacerBlock = "{$replacer}";
                } else {
                    $replacerBlock = "{$replacer}{$subreplacer}";
                }
                $key = $this->formatKey($child);

                if (is_array($child['value'])) {
                    $newAcc .= "{$replacerBlock}{$subreplacer}{$key}: {\n";
                    return $this->formatDiff($child['value'], $replacer, $newAcc, $subreplacer . $replacer, false) .
                    "{$replacer}{$replacer}{$subreplacer}{$subreplacer}}\n";
                } else {
                    $value = $this->formatValue($child['value']);
                    $newAcc .= "{$replacerBlock}{$subreplacer}{$key}: {$value}\n";
                    return $newAcc;
                }
            },
            $acc
        );
    }

    private function formatKey(array $child): string
    {
        $name = $child['name'];

        if (array_key_exists('diff', $child)) {
            switch ($child['diff']) {
                case 'deleted':
                    $result = '- ' . $name;
                    break;

                case 'inserted':
                    $result = '+ ' . $name;
                    break;

                case 'shared':
                default:
                    $result = '  ' . $name;
                    break;
            }
        } else {
            $result = '  ' . $name;
        }

        return $result;
    }

    private function formatValue(mixed $value): string
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

        return $result;
    }
}
