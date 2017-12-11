<?php

namespace Villermen\FeedProcessing\Feeds;

use Villermen\FeedProcessing\FeedItem;
use Villermen\FeedProcessing\FeedProcessException;

abstract class Feed implements \Iterator
{
    /** @var string */
    protected $filePath;

    /** @var int */
    private $key = -1;

    /** @var FeedItem */
    private $item = null;

    /** @var callable */
    private $filter = null;

    /**
     * @param string $file
     * @param int $cacheTime The time to cache files for. Set to 0 to always obtain a current file.
     * @param null $cacheDirectory Directory to put cached files in, uses system temp directory if unspecified.
     * @throws FeedProcessException
     */
    public function __construct($file, $cacheTime = 3600, $cacheDirectory = null)
    {
        if ($cacheDirectory === null) {
            $cacheDirectory = sys_get_temp_dir();
        }

        $cacheDirectory = rtrim($cacheDirectory, "/") . "/";

        $cacheFilePath = $cacheDirectory . "FeedProcessorCache" . md5($file);

        $mTime = false;
        if (is_file($cacheFilePath)) {
            $mTime = filemtime($cacheFilePath);
        }

        if (!$mTime || $mTime + $cacheTime < time()) {
            // Get file and store in cache (streaming)
            $inFile = @fopen($file, "r");

            if (!$inFile) {
                throw new FeedProcessException("Could not open feed resource for reading: ".error_get_last()["message"]);
            }

            $outFile = @fopen($cacheFilePath, "w");

            if (!$outFile) {
                fclose($inFile);
                throw new FeedProcessException("Could not create cached feed file: ".error_get_last()["message"]);
            }

            $copyResult = @stream_copy_to_stream($inFile, $outFile);

            fclose($inFile);
            fclose($outFile);

            if ($copyResult === false) {
                throw new FeedProcessException("Could not copy feed to cache file: ".error_get_last()["message"]);
            }
        }

        $this->filePath = $cacheFilePath;
    }

    /**
     * Return the parsed feed item, or null when the end of the file has been reached.
     * @return FeedItem|null
     */
    public function current()
    {
        return $this->item;
    }

    /**
     * Parse the next item in the feed.
     *
     * @return void
     * @throws FeedProcessException When the filter function misbehaves.
     */
    public function next()
    {
        // Keep looking for new items until there are no more (null) or a valid
        // item that passes the optionally set filter function is found
        while (true) {
            $this->item = $this->getNextItem();

            if ($this->item === null) {
                break;
            }

            $this->key++;

            if ($this->filter === null) {
                // All good, we don't need to perform any filtering or mapping magic today
                break;
            }

            $this->item = call_user_func($this->filter, $this->item);

            if ($this->item) {
                if (!is_a($this->item, FeedItem::class)) {
                    throw new FeedProcessException("Object returned by filter is not a subclass of FeedItem. Return either a FeedItem or false. Object of class ".get_class($this->item)." returned.");
                }

                break;
            }

            // The item was filtered out, grab a new one
        }
    }

    /**
     * Returns the index of the current item.
     * @return int
     */
    public function key()
    {
        return $this->key;
    }

    public function valid()
    {
        return $this->item !== null;
    }

    public function rewind()
    {
        $this->reset();
        $this->next();
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Returns an array of properties for the current item in the feed, or null when there are no more items.
     * @return FeedItem|null
     */
    protected abstract function getNextItem();

    /**
     * Resets the FeedParser, starting it from the beginning.
     * @return void
     */
    protected abstract function reset();

    /**
     * Set a function that will be used to filter and map obtained feed items.
     * If the function returns false, the item will not be returned from next()*.
     * The supplied FeedItem can be transformed in any way, and should be returned from the function if valid.
     *
     * @param callable $filter The function or filter to be used. Has to accept a FeedItem and return a FeedItem or false.
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }
}
