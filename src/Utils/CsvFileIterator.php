<?php

namespace Serato\SwsApp\Utils;

use Exception;

/**
 * Provides an iterable interface to a CSV file.
 *
 * @link http://php.net/manual/en/class.iterator.php
 */
class CsvFileIterator implements \Iterator
{
    private $file;
    private $key = 0;
    private $current;
    private $valid = false;

    /**
     * Constructs the class
     *
     * @param string $filePath  Path to CSV file
     */
    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("File '$filePath' not found");
        }
        $this->file = fopen($filePath, 'r');
        $this->readLine();
    }

    public function __destruct()
    {
        fclose($this->file);
    }

    #[\Override]
    public function rewind()
    {
        rewind($this->file);
        $this->readLine();
        $this->key = 0;
    }

    #[\Override]
    public function valid()
    {
        return $this->valid;
    }

    #[\Override]
    public function key()
    {
        return $this->key;
    }

    #[\Override]
    public function current()
    {
        return $this->current;
    }

    #[\Override]
    public function next()
    {
        $this->readLine();
        $this->key++;
    }

    private function readLine()
    {
        if (feof($this->file)) {
            $this->valid = false;
        } else {
            $this->valid = true;
            $this->current = fgetcsv($this->file);
        }
    }
}
