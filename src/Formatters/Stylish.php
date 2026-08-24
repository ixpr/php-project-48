<?php

namespace Differ\Formatters;

class Stylish
{
    public array $diffArray;

    public function __construct(array $diffArray)
    {
        $this->diffArray = $diffArray;
    }

    public function format(): string
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
            function ($newAcc, $child) use ($replacer, $subreplacer, $isFirstLvl) {
                if ($isFirstLvl) {
                    $replacerBlock = "{$replacer}";
                } else {
                    $replacerBlock = "{$replacer}{$subreplacer}";
                }
                $key = $this->formatKey($child);

                if (is_array($child['value'])) {
                    if (isset($child['diff']) && $child['diff'] === 'updated') {
                        $keyOld = $this->formatKeyUpdated($child, false);
                        $valueOld = $this->formatValueUpdated($child, false);
                        $keyNew = $this->formatKeyUpdated($child, true);
                        $valueNew = $this->formatValueUpdated($child, true);

                        if (is_array($valueOld)) {
                            $newAcc .= "{$replacerBlock}{$subreplacer}{$keyOld}: {\n";

                            $oldArrayLineValue = $this->formatDiff(
                                $valueOld,
                                $replacer,
                                $newAcc,
                                $subreplacer . $replacer,
                                false
                            ) . "{$replacer}{$replacer}{$subreplacer}{$subreplacer}}\n";

                            if (is_array($valueNew)) {
                                $newArrayLineTitle = "{$replacerBlock}{$subreplacer}{$keyNew}: {\n";

                                $newArrayLineValue = $this->formatDiff(
                                    $valueNew,
                                    $replacer,
                                    $newAcc,
                                    $subreplacer . $replacer,
                                    false
                                ) . "{$replacer}{$replacer}{$subreplacer}{$subreplacer}}\n";

                                return $oldArrayLineValue . $newArrayLineTitle . $newArrayLineValue;
                            } else {
                                $newLineSimple = "{$replacerBlock}{$subreplacer}{$keyNew}: {$valueNew}\n";
                                return $oldArrayLineValue . $newLineSimple;
                            }
                        } else {
                            $newAcc .= "{$replacerBlock}{$subreplacer}{$keyOld}: {$valueOld}\n";
                        }

                        if (is_array($valueNew)) {
                            $newAcc .= "{$replacerBlock}{$subreplacer}{$keyNew}: {\n";

                            $newArrayLineValue = $this->formatDiff(
                                $valueNew,
                                $replacer,
                                $newAcc,
                                $subreplacer . $replacer,
                                false
                            ) . "{$replacer}{$replacer}{$subreplacer}{$subreplacer}}\n";

                            return $newArrayLineValue;
                        } else {
                            $newAcc .= "{$replacerBlock}{$subreplacer}{$keyNew}: {$valueNew}\n";
                        }

                        return $newAcc;
                    } else {
                        $newAcc .= "{$replacerBlock}{$subreplacer}{$key}: {\n";
                        return $this->formatDiff(
                            $child['value'],
                            $replacer,
                            $newAcc,
                            $subreplacer . $replacer,
                            false
                        ) .
                        "{$replacer}{$replacer}{$subreplacer}{$subreplacer}}\n";
                    }
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

    private function formatKeyUpdated(array $child, bool $new): string
    {
        $name = $child['name'];

        if ($new) {
            $result = '+ ' . $name;
        } else {
            $result = '- ' . $name;
        }

        return $result;
    }

    private function formatValue(mixed $value): string|array
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

    private function formatValueUpdated(array $child, bool $new): string|array
    {
        if ($new) {
            $value = $child['value'][0]['value'];
        } else {
            $value = $child['value'][1]['value'];
        }

        return $this->formatValue($value);
    }
}
