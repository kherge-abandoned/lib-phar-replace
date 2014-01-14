<?php

namespace Phine\Phar\Replace\Tests;

use Phar;
use Phine\Phar\Builder;
use Phine\Phar\Replace\ReplaceObserver;
use Phine\Test\Property;
use Phine\Test\Temp;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Performs unit and functional testing on `ReplaceObserver`.
 *
 * @see Phine\Phar\Replace\ReplaceObserver
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ReplaceObserverTest extends TestCase
{
    /**
     * The test builder.
     *
     * @var Builder
     */
    private $builder;

    /**
     * The test archive file.
     *
     * @var string
     */
    private $file;

    /**
     * The test archive.
     *
     * @var Phar
     */
    private $phar;

    /**
     * The observer instance being tested
     *
     * @var ReplaceObserver
     */
    private $replace;

    /**
     * The temporary file manager.
     *
     * @var Temp
     */
    private $temp;

    /**
     * Make sure that we can replace file contents.
     */
    public function testAddFile()
    {
        $file = $this->temp->createDir() . DIRECTORY_SEPARATOR . 'test.json';

        // inject search strings and values
        Property::set($this->replace, 'search', array('$name'));
        Property::set($this->replace, 'values', array('world'));

        file_put_contents(
            $file,
            'Hello, $name!'
        );

        $this->builder->addFile($file, 'test.txt');

        $this->assertEquals(
            'Hello, world!',
            file_get_contents($this->phar['test.txt']),
            'The file contents should be replaced.'
        );
    }

    /**
     * Make sure that we can replace string contents.
     */
    public function testAddString()
    {
        // inject search strings and values
        Property::set($this->replace, 'search', array('$name'));
        Property::set($this->replace, 'values', array('world'));

        $this->builder->addFromString(
            'test.txt',
            'Hello, $name!'
        );

        $this->assertEquals(
            'Hello, world!',
            file_get_contents($this->phar['test.txt']),
            'The string contents should be replaced.'
        );
    }

    /**
     * Make sure that we can set the search strings and values.
     */
    public function testConstruct()
    {
        $observer = new ReplaceObserver(
            array(
                'a' => 'b',
                'c' => 'd',
            )
        );

        $this->assertEquals(
            array('a', 'c'),
            Property::get($observer, 'search'),
            'The search strings should be set.'
        );

        $this->assertEquals(
            array('b', 'd'),
            Property::get($observer, 'values'),
            'The search values should be set.'
        );
    }

    /**
     * Make sure that the temporary files are cleaned up.
     */
    public function testDestruct()
    {
        Property::set(
            $this->replace,
            'files',
            array(
                $file = $this->temp->createFile(),
            )
        );

        $this->replace->__destruct();

        $this->assertFileNotExists(
            $file,
            'The temporary file should have been deleted.'
        );
    }

    /**
     * Make sure that the temporary files are cleaned up.
     */
    public function testCleanUp()
    {
        Property::set(
            $this->replace,
            'files',
            array(
                $file = $this->temp->createFile(),
            )
        );

        $this->replace->cleanUp();

        $this->assertFileNotExists(
            $file,
            'The temporary file should have been deleted.'
        );
    }

    /**
     * Make sure that we can set and replace a search string and value.
     */
    public function testSetSearchValue()
    {
        $this->replace->setSearchValue('a', 'b');
        $this->replace->setSearchValue('c', 'd');

        $this->assertEquals(
            array('a', 'c'),
            Property::get($this->replace, 'search'),
            'The search strings should be set.'
        );

        $this->assertEquals(
            array('b', 'd'),
            Property::get($this->replace, 'values'),
            'The search values should be set.'
        );

        $this->replace->setSearchValue('a', 'e');
        $this->replace->setSearchValue('c', 'f');

        $this->assertEquals(
            array('a', 'c'),
            Property::get($this->replace, 'search'),
            'The search strings should be set.'
        );

        $this->assertEquals(
            array('e', 'f'),
            Property::get($this->replace, 'values'),
            'The search values should be set.'
        );

        $this->setExpectedException(
            'Phine\\Phar\\Replace\\Exception\\InvalidValueException',
            'The value "array" is not a scalar value.'
        );

        $this->replace->setSearchValue('test', array());
    }

    /**
     * Make sure that we can set multiple search values.
     */
    public function testSetSearchValues()
    {
        $this->replace->setSearchValues(
            array(
                'a' => 'b',
                'c' => 'd',
            )
        );

        $this->assertEquals(
            array('a', 'c'),
            Property::get($this->replace, 'search'),
            'The search strings should be set.'
        );

        $this->assertEquals(
            array('b', 'd'),
            Property::get($this->replace, 'values'),
            'The search values should be set.'
        );

        $this->replace->setSearchValues(
            array(
                'e' => 'f',
                'g' => 'h',
            )
        );

        $this->assertEquals(
            array('e', 'g'),
            Property::get($this->replace, 'search'),
            'The search strings should be set.'
        );

        $this->assertEquals(
            array('f', 'h'),
            Property::get($this->replace, 'values'),
            'The search values should be set.'
        );
    }

    /**
     * Make sure that an exception is thrown for unsupported subjects.
     */
    public function testUnsupportedSubject()
    {
        $this->setExpectedException(
            'Phine\\Phar\\Replace\\Exception\\InvalidSubjectException',
            'The subject "Phine\\Phar\\Subject\\Builder\\SetStub" is not supported.'
        );

        $this->replace->receiveUpdate(
            $this->builder->getSubject(Builder::SET_STUB)
        );
    }

    /**
     * Sets up a test phar and the observer.
     */
    protected function setUp()
    {
        $this->temp = new Temp();
        $this->file = $this->temp->createDir() .  '/test.phar';
        $this->phar = new Phar($this->file);
        $this->builder = new Builder($this->phar);
        $this->replace = new ReplaceObserver();

        $this->builder->observe(Builder::ADD_FILE, $this->replace);
        $this->builder->observe(Builder::ADD_STRING, $this->replace);
    }
}
