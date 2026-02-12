<?php

namespace Differ\Formatters;

class Plain
{
    public array $diffArray;

    public function __construct(array $diffArray)
    {
        $this->diffArray = $diffArray;
    }

    public function format(): string
    {
        return $this->formatDiff($this->diffArray, '', []);
    }

    private function formatDiff(array $node, string $acc, array $legacy): string
    {
        return array_reduce(
            $node,
            function ($newAcc, $child) use ($legacy) {
                if (is_array($child['value'])) {
                    $legacy[] = $child['name'];
                    if (
                        array_key_exists('diff', $child) &&
                        in_array($child['diff'], ['inserted', 'deleted', 'updated'], false)
                    ) {
                        $newAcc .= $this->formatKey(implode('.', $legacy), $child) . $this->formatValue($child) . "\n";
                        return $newAcc;
                    } else {
                        return $this->formatDiff($child['value'], $newAcc, $legacy);
                    }
                } else {
                    if (
                        array_key_exists('diff', $child) &&
                        in_array($child['diff'], ['inserted', 'deleted'], false)
                    ) {
                        $legacy[] = $child['name'];
                        $newAcc .= $this->formatKey(implode('.', $legacy), $child) . $this->formatValue($child) . "\n";
                    }
                    return $newAcc;
                }
            },
            $acc
        );
    }

    private function formatKey(string $name, array $child): string
    {
        $status = '';

        if (array_key_exists('diff', $child)) {
            switch ($child['diff']) {
                case 'deleted':
                    $status = 'removed';
                    break;

                case 'inserted':
                    $status = 'added';
                    break;

                case 'updated':
                    $status = 'updated';
                    break;
            }
        }

        return "Property '{$name}' was {$status}";
    }

    private function formatValue(array $child): string
    {
        $value = $child['value'];
        $diff = $child['diff'] ?? null;

        if (in_array($diff, ['deleted'], false)) {
            return '';
        }

        if ($diff === 'updated') {
            $old = $this->formatType($child['value'][1]['value']);
            $new = $this->formatType($child['value'][0]['value']);
            return ". From {$old} to {$new}";
        }

        $result = $this->formatType($value);

        return " with value: {$result}";
    }

    private function formatType(mixed $value): string
    {
        $type = gettype($value);
        switch ($type) {
            case 'array':
                $result = "[complex value]";
                break;

            case 'boolean':
                $result = $value === true ? "true" : "false";
                break;

            case 'NULL':
                $result = "null";
                break;

            case 'string':
                $result = "'{$value}'";
                break;

            case 'integer':
            default:
                $result = $value;
                break;
        }

        return $result;
    }
}
