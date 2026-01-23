<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;

use function Differ\Differ\genDiff;
use function Differ\Differ\process;

use const Differ\Differ\ERROR_MESSAGE;

class DifferTest extends TestCase
{
    public function testGenDiffer()
    {
        $pathToJsonFile1 = $this->getFixtureFullPath('testfile1.json');
        $pathToJsonFile2 = $this->getFixtureFullPath('testfile2.json');
        $pathToJsonFile3 = $this->getFixtureFullPath('testfile3.json');
        $pathToJsonNoFile = $this->getFixtureFullPath('nofile.json');
        $pathToYamlFile1 = $this->getFixtureFullPath('testfile1.yml');
        $pathToYamlFile2 = $this->getFixtureFullPath('testfile2.yml');
        $pathToYamlFile3 = $this->getFixtureFullPath('testfile3.yml');
        $pathToYamlNoFile = $this->getFixtureFullPath('nofile.yml');
        $pathToExpected1 = $this->getFixtureFullPath('expected1.txt');
        $pathToExpected2 = $this->getFixtureFullPath('expected2.txt');
        $pathToExpected3 = $this->getFixtureFullPath('expected3.txt');

        // json tests
        $json1 = genDiff($pathToJsonFile1, $pathToJsonFile2);
        $this->assertStringEqualsFile($pathToExpected1, $json1);

        $json2 = genDiff($pathToJsonFile1, $pathToJsonFile3);
        $this->assertStringEqualsFile($pathToExpected2, $json2);

        $json3 = genDiff($pathToJsonFile2, $pathToJsonFile3);
        $this->assertStringEqualsFile($pathToExpected3, $json3);

        $json4 = genDiff($pathToJsonFile1, $pathToJsonNoFile);
        $this->assertEquals(ERROR_MESSAGE, $json4);

        // yaml tests
        $yaml1 = genDiff($pathToYamlFile1, $pathToYamlFile2);
        $this->assertStringEqualsFile($pathToExpected1, $yaml1);

        $yaml2 = genDiff($pathToYamlFile1, $pathToYamlFile3);
        $this->assertStringEqualsFile($pathToExpected2, $yaml2);

        $yaml3 = genDiff($pathToYamlFile2, $pathToYamlFile3);
        $this->assertStringEqualsFile($pathToExpected3, $yaml3);

        $yaml4 = genDiff($pathToYamlFile2, $pathToYamlNoFile);
        $this->assertEquals(ERROR_MESSAGE, $yaml4);
    }

    public function testProcess()
    {
        $object = new stdClass();
        $object->args = [
            "--format" => "stylish",
            "<firstFile>" => $this->getFixtureFullPath('testfile1.json'),
            "<secondFile>" => $this->getFixtureFullPath('nofile.json')
        ];

        $actual = process($object);
        $this->assertEquals("Please check file URL or format \n", $actual);
    }

    public function getFixtureFullPath(string $fixtureName): string
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }
}
