<?php

namespace LanguageDetection\Tests;

use LanguageDetection\Language;
use LanguageDetection\Tokenizer\TokenizerInterface;

/**
 * Class LanguageTest
 *
 * @copyright 2016-2017 Patrick Schur
 * @license https://opensource.org/licenses/mit-license.html MIT
 * @author Patrick Schur <patrick_schur@outlook.de>
 * @package LanguageDetection\Tests
 */
class LanguageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $lang
     * @param string $path
     * @dataProvider testFileProvider
     */
    public function testFile($lang, $path)
    {
        $l = new Language();
        $content = file_get_contents($path);
        $results = $l->detect($content)->close();
        $this->assertEquals(key($results), $lang);
    }

    public function testFileProvider()
    {
        $files = [];
        foreach (new \GlobIterator(__DIR__ . '/../resources/*/*.txt') as $txt) {
            $lang = $txt->getBasename('.txt');
            $files[$lang] = [$lang, $txt->getPathname()];
        }
        return $files;
    }

    public function testConstructor()
    {
        $l = new Language(['de', 'en', 'nl']);

        $array = $l->detect('Das ist ein Test')->close();

        $this->assertEquals(3, count($array));

        $this->assertArrayHasKey('de', $array);
        $this->assertArrayHasKey('en', $array);
        $this->assertArrayHasKey('nl', $array);
    }

    public function testTokenizer()
    {
        $stub = $this->createMock(Language::class);

        $stub->method('setTokenizer')->willReturn('');

        $mock = $this->getMockBuilder(TokenizerInterface::class)
            ->setMethods(array('tokenize'))
            ->getMock();
        $mock->method('tokenize')->will($this->returnCallback(function ($str) {
            return preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
        }));

        /** @var Language $stub */
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpParamsInspection */
        $this->assertEquals('', $stub->setTokenizer($mock));
    }

    /**
     * @param $expected
     * @param $sample
     * @dataProvider sampleProvider
     */
    public function testSamples($expected, $sample)
    {
        $l = new Language();

        $results = $l->detect($sample)->close();
        $this->assertEquals($expected, key($results));
    }

    /**
     * @return array
     */
    public function sampleProvider()
    {
        return [
            'de' => ['de', 'Ich wünsche dir noch einen schönen Tag'],
            'ja' => ['ja', '最近どうですか。'],
            'en' => ['en', 'This sentences should be too small to be recognized.'],
            'nl' => ['nl', 'Mag het een onsje meer zijn? '],
            'hi' => ['hi', 'मुझे हिंदी नहीं आती'],
            'et' => ['et', 'Tere tulemast tagasi! Nägemist!'],
            'pl' => ['pl', 'Wszystkiego najlepszego z okazji urodzin!'],
            'pl2' => ['pl', 'Czy mówi pan po polsku?'],
            'fr' => ['fr', 'Où sont les toilettes?']
        ];
    }
}
