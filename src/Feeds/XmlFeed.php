<?php

namespace Villermen\FeedProcessing\Feeds;

use Villermen\DataHandling\DataHandling;
use Villermen\FeedProcessing\ActuallySimpleXmlElement;
use Villermen\FeedProcessing\FeedProcessException;
use XMLReader;

abstract class XmlFeed extends Feed
{
    /** @var \XMLReader */
    private $reader;

    private $currentPath;

    private $targetPaths = [];

    private $debug = false;

    public function __construct($file, $cacheTime = 3600, $cacheDirectory = null)
    {
        parent::__construct($file, $cacheTime, $cacheDirectory);

        $this->reset();
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     * @return XmlFeed
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Returns a path string that makes up the path to the current element within the file.
     *
     * @return string
     */
    protected function getCurrentPath()
    {
        return $this->currentPath;
    }

    /**
     * @param string $pathString Path to the target element. E.g.: "feed/products/product"
     */
    protected function addTargetPath($pathString)
    {
        $this->targetPaths[] = trim($pathString, "/ ");
    }

    /**
     * Returns the next node that matches any of the configured target paths.
     * Be wary that if you specify a target that might have passed another value you want, you will not be able to obtain it anymore.
     * In that case it is better to obtain their parent as a node (if that is small enough).
     *
     * @return ActuallySimpleXmlElement|null
     *
     * @throws FeedProcessException When no XML target paths are defined.
     */
    public function getNextTargetElement()
    {
        if (count($this->targetPaths) === 0) {
            throw new FeedProcessException("No XML target paths defined, so processing will do nothing.");
        }

        if ($this->moveToNextTargetElement()) {
            if ($this->getDebug()) {
                echo "Returning element at ".$this->currentPath.".\n";
            }

            return ActuallySimpleXmlElement::fromDomNode($this->reader->expand());
        } else {
            return null;
        }
    }

    /**
     * Reads to the next target element, skipping nodes that could never result in reaching any of the targets.
     * @return bool Whether we the reader was moved to the next target element before the end of the file was reached.
     */
    private function moveToNextTargetElement()
    {
        while (true) {
            // Skip if we are on a target or not on the right track for any of the targets
            $dive = false;
            foreach ($this->targetPaths as $targetPath) {
                if ($this->currentPath === $targetPath) {
                    break;
                }

                if ($this->currentPath === "" ||
                    $this->currentPath === $targetPath ||
                    DataHandling::startsWith($targetPath, $this->currentPath . "/")) {
                    if ($this->getDebug()) {
                        echo "\"" . $this->currentPath . "\" matches \"" . $targetPath . "\": dive.\n";
                    }

                    $dive = true;
                    break;
                }
            }

            if ($dive) {
                if (!$this->reader->read()) {
                    return false;
                }
            } else {
                if (!$this->reader->next()) {
                    return false;
                }

                if ($this->getDebug()) {
                    echo "\"" . $this->currentPath . "\" matches no targets or is a target: skipped to " . $this->reader->name . " (type " . $this->reader->nodeType . ").\n";
                }

                // Pop current path
                $this->currentPath = substr($this->currentPath, 0, strrpos($this->currentPath, "/"));
            }

            // Evaluate current node
            switch ($this->reader->nodeType) {
                case XMLReader::ELEMENT:
                    // Push current path
                    $this->currentPath = ltrim($this->currentPath . "/" . $this->reader->name, "/");

                    // Return if target
                    foreach($this->targetPaths as $targetPath) {
                        if ($this->currentPath === $targetPath) {
                            return true;
                        }
                    }
                    break;

                case XMLReader::END_ELEMENT:
                    // Pop current path
                    $this->currentPath = substr($this->currentPath, 0, strrpos($this->currentPath, "/"));
                    break;
            }
        }
    }

    public function __destruct()
    {
        if ($this->reader) {
            $this->reader->close();
        }
    }

    protected function reset()
    {
        $this->reader = new \XMLReader();
        $this->reader->open($this->filePath, "UTF-8");
        $this->currentPath = "";
    }
}
