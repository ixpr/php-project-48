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
        $pathToJsonFile1Simple = $this->getFixtureFullPath('testfile1-simple.json');
        $pathToJsonFile2Simple = $this->getFixtureFullPath('testfile2-simple.json');
        $pathToJsonFile3Simple = $this->getFixtureFullPath('testfile3-simple.json');
        $pathToJsonFile1Recursive = $this->getFixtureFullPath('testfile1-recursive.json');
        $pathToJsonFile2Recursive = $this->getFixtureFullPath('testfile2-recursive.json');
        $pathToJsonFile3Recursive = $this->getFixtureFullPath('testfile3-recursive.json');
        $pathToJsonNoFile = $this->getFixtureFullPath('nofile.json');
        $pathToYamlFile1Simple = $this->getFixtureFullPath('testfile1-simple.yml');
        $pathToYamlFile2Simple = $this->getFixtureFullPath('testfile2-simple.yml');
        $pathToYamlFile3Simple = $this->getFixtureFullPath('testfile3-simple.yml');
        $pathToYamlFile1Recursive = $this->getFixtureFullPath('testfile1-recursive.yml');
        $pathToYamlFile2Recursive = $this->getFixtureFullPath('testfile2-recursive.yml');
        $pathToYamlFile3Recursive = $this->getFixtureFullPath('testfile3-recursive.yml');
        $pathToYamlNoFile = $this->getFixtureFullPath('nofile.yml');
        $pathToExpected1 = $this->getFixtureFullPath('expected1.txt');
        $pathToExpected2 = $this->getFixtureFullPath('expected2.txt');
        $pathToExpected3 = $this->getFixtureFullPath('expected3.txt');
        $pathToExpected4 = $this->getFixtureFullPath('expected4.txt');
        $pathToExpected5 = $this->getFixtureFullPath('expected5.txt');
        $pathToExpected6 = $this->getFixtureFullPath('expected6.txt');
        $pathToExpected7 = $this->getFixtureFullPath('expected7.txt');
        $pathToExpected8 = $this->getFixtureFullPath('expected8.txt');
        $pathToExpected9 = $this->getFixtureFullPath('expected9.txt');

        // STYLISH format
        // json tests simple
        $json1 = genDiff($pathToJsonFile1Simple, $pathToJsonFile2Simple);
        $this->assertStringEqualsFile($pathToExpected1, $json1);

        $json2 = genDiff($pathToJsonFile1Simple, $pathToJsonFile3Simple);
        $this->assertStringEqualsFile($pathToExpected2, $json2);

        $json3 = genDiff($pathToJsonFile2Simple, $pathToJsonFile3Simple);
        $this->assertStringEqualsFile($pathToExpected3, $json3);

        // json tests error
        $json4 = genDiff($pathToJsonFile1Simple, $pathToJsonNoFile);
        $this->assertEquals(ERROR_MESSAGE, $json4);

        // json tests recursive
        $json5 = genDiff($pathToJsonFile1Recursive, $pathToJsonFile2Recursive);
        $this->assertStringEqualsFile($pathToExpected4, $json5);

        $json6 = genDiff($pathToJsonFile1Recursive, $pathToJsonFile3Recursive);
        $this->assertStringEqualsFile($pathToExpected5, $json6);

        $json7 = genDiff($pathToJsonFile2Recursive, $pathToJsonFile3Recursive);
        $this->assertStringEqualsFile($pathToExpected6, $json7);

        // yaml tests simple
        $yaml1 = genDiff($pathToYamlFile1Simple, $pathToYamlFile2Simple);
        $this->assertStringEqualsFile($pathToExpected1, $yaml1);

        $yaml2 = genDiff($pathToYamlFile1Simple, $pathToYamlFile3Simple);
        $this->assertStringEqualsFile($pathToExpected2, $yaml2);

        $yaml3 = genDiff($pathToYamlFile2Simple, $pathToYamlFile3Simple);
        $this->assertStringEqualsFile($pathToExpected3, $yaml3);

        // json tests error
        $yaml4 = genDiff($pathToYamlFile2Simple, $pathToYamlNoFile);
        $this->assertEquals(ERROR_MESSAGE, $yaml4);

        // yaml tests recursive
        $yaml5 = genDiff($pathToYamlFile1Recursive, $pathToYamlFile2Recursive);
        $this->assertStringEqualsFile($pathToExpected4, $yaml5);

        $yaml6 = genDiff($pathToYamlFile1Recursive, $pathToYamlFile3Recursive);
        $this->assertStringEqualsFile($pathToExpected5, $yaml6);

        $yaml7 = genDiff($pathToYamlFile2Recursive, $pathToYamlFile3Recursive);
        $this->assertStringEqualsFile($pathToExpected6, $yaml7);

        // PLAIN format
        // json tests recursive
        $json8 = genDiff($pathToJsonFile1Recursive, $pathToJsonFile2Recursive, 'plain');
        $this->assertStringEqualsFile($pathToExpected7, $json8);

        $json9 = genDiff($pathToJsonFile1Recursive, $pathToJsonFile3Recursive, 'plain');
        $this->assertStringEqualsFile($pathToExpected8, $json9);

        $json10 = genDiff($pathToJsonFile2Recursive, $pathToJsonFile3Recursive, 'plain');
        $this->assertStringEqualsFile($pathToExpected9, $json10);
    }

    public function testProcess()
    {
        $object = new stdClass();
        $object->args = [
            "--format" => "stylish",
            "<firstFile>" => $this->getFixtureFullPath('testfile1-simple.json'),
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
