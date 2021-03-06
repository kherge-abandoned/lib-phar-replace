<?php

namespace Phine\Phar\Replace;

use Phine\Exception\Exception;
use Phine\Observer\ObserverInterface;
use Phine\Observer\SubjectInterface;
use Phine\Phar\Exception\FileException;
use Phine\Phar\Replace\Exception\InvalidSubjectException;
use Phine\Phar\Replace\Exception\InvalidValueException;
use Phine\Phar\Subject\AbstractSubject;
use Phine\Phar\Subject\Arguments;
use Phine\Phar\Subject\Builder\AddFile;
use Phine\Phar\Subject\Builder\AddString;

/**
 * Replaces all occurrences of a search string with a replacement value.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ReplaceObserver implements ObserverInterface
{
    /**
     * The temporary files.
     *
     * @var array
     */
    private $files = array();

    /**
     * The list of search strings.
     *
     * @var array
     */
    private $search = array();

    /**
     * The list of values.
     *
     * @var array
     */
    private $values = array();

    /**
     * Sets the search strings and their values.
     *
     * @param array $replace The search strings and values.
     */
    public function __construct(array $replace = array())
    {
        $this->setSearchValues($replace);
    }

    /**
     * Cleans up the temporary files.
     */
    public function __destruct()
    {
        $this->cleanUp();
    }

    /**
     * Cleans up the temporary files.
     */
    public function cleanUp()
    {
        foreach ($this->files as $file) {
            unlink($file);
        }

        $this->files = array();
    }

    /**
     * {@inheritDoc}
     */
    public function receiveUpdate(SubjectInterface $subject)
    {
        /** @var AbstractSubject $subject */
        $arguments = $subject->getArguments();

        if ($subject instanceof AddFile) {
            $this->addFile($arguments);
        } elseif ($subject instanceof AddString) {
            $this->addString($arguments);
        } else {
            throw InvalidSubjectException::createUsingFormat(
                'The subject "%s" is not supported.',
                get_class($subject)
            );
        }
    }

    /**
     * Sets a search string and its replacement value.
     *
     * @param string $search A search string.
     * @param mixed  $value  A replacement value.
     *
     * @throws Exception
     * @throws InvalidValueException If the value is not a scalar value.
     */
    public function setSearchValue($search, $value)
    {
        if (!is_scalar($value)) {
            throw InvalidValueException::createUsingFormat(
                'The value "%s" is not a scalar value.',
                gettype($value)
            );
        }

        if (false === ($index = array_search($search, $this->search))) {
            $this->search[] = $search;
            $this->values[] = $value;
        } else {
            $this->values[$index] = $value;
        }
    }

    /**
     * Replaces the search strings and their values.
     *
     * @param array $replace The placeholders and values.
     */
    public function setSearchValues(array $replace = array())
    {
        $this->search = array();
        $this->values = array();

        foreach ($replace as $search => $value) {
            $this->setSearchValue($search, $value);
        }
    }

    /**
     * Replaces the placeholders in the file with their values.
     *
     * @param Arguments $arguments The subject arguments.
     *
     * @throws Exception
     * @throws FileException If the temporary file could not be written.
     */
    private function addFile(Arguments $arguments)
    {
        $file = @tempnam(sys_get_temp_dir(), 'compact');

        if (false === $file) {
            // @codeCoverageIgnoreStart
            throw FileException::createUsingLastError();
        }
        // @codeCoverageIgnoreEnd

        if (false === ($contents = @file_get_contents($arguments['file']))) {
            // @codeCoverageIgnoreStart
            throw FileException::createUsingLastError();
        }
        // @codeCoverageIgnoreEnd

        $contents = str_replace($this->search, $this->values, $contents);

        if (false === @file_put_contents($file, $contents)) {
            // @codeCoverageIgnoreStart
            throw FileException::createUsingLastError();
        }
        // @codeCoverageIgnoreEnd

        $arguments['file'] = $file;
    }

    /**
     * Replaces the placeholders in the string with their values.
     *
     * @param Arguments $arguments The subject arguments.
     */
    private function addString(Arguments $arguments)
    {
        $arguments['contents'] = str_replace(
            $this->search,
            $this->values,
            $arguments['contents']
        );
    }
}
