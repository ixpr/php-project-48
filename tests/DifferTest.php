<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;

use function Differ\Differ\genDiff;
use function Differ\Differ\process;

class DifferTest extends TestCase
{
    public function testGenDifferJson()
    {
        $pathToFile1 = $this->getFixtureFullPath('testfile1.json');
        $pathToFile2 = $this->getFixtureFullPath('testfile2.json');
        $pathToFile3 = $this->getFixtureFullPath('testfile3.json');
        $pathToExpected1 = $this->getFixtureFullPath('expected1.txt');
        $pathToExpected2 = $this->getFixtureFullPath('expected2.txt');
        $pathToExpected3 = $this->getFixtureFullPath('expected3.txt');
        $pathToNoFile = $this->getFixtureFullPath('nofile.json');

        $actual1 = genDiff($pathToFile1, $pathToFile2);
        $this->assertStringEqualsFile($pathToExpected1, $actual1);

        $actual2 = genDiff($pathToFile1, $pathToFile3);
        $this->assertStringEqualsFile($pathToExpected2, $actual2);

        $actual3 = genDiff($pathToFile2, $pathToFile3);
        $this->assertStringEqualsFile($pathToExpected3, $actual3);

        $actual4 = genDiff($pathToFile1, $pathToNoFile);
        $this->assertEquals("Please check file URL or format \n", $actual4);
    }

    public function testGenDifferYaml()
    {
        $pathToFile1 = $this->getFixtureFullPath('testfile1.yml');
        $pathToFile2 = $this->getFixtureFullPath('testfile2.yml');
        $pathToFile3 = $this->getFixtureFullPath('testfile3.yml');
        $pathToExpected1 = $this->getFixtureFullPath('expected1.txt');
        $pathToExpected2 = $this->getFixtureFullPath('expected2.txt');
        $pathToExpected3 = $this->getFixtureFullPath('expected3.txt');
        $pathToNoFile = $this->getFixtureFullPath('nofile.yml');

        $actual1 = genDiff($pathToFile1, $pathToFile2);
        $this->assertStringEqualsFile($pathToExpected1, $actual1);

        $actual2 = genDiff($pathToFile1, $pathToFile3);
        $this->assertStringEqualsFile($pathToExpected2, $actual2);

        $actual3 = genDiff($pathToFile2, $pathToFile3);
        $this->assertStringEqualsFile($pathToExpected3, $actual3);

        $actual4 = genDiff($pathToFile1, $pathToNoFile);
        $this->assertEquals("Please check file URL or format \n", $actual4);
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
