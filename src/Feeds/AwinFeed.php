<?php

namespace Villermen\FeedProcessing\Feeds;

use Villermen\DataHandling\DataHandling;
use Villermen\FeedProcessing\FeedItem;
use Villermen\FeedProcessing\FeedProcessException;

/**
 * Awin or Zanox (previous name).
 */
class AwinFeed extends XmlFeed
{
    public function __construct($file, $cacheTime = 3600, $cacheDirectory = null)
    {
        parent::__construct($file, $cacheTime, $cacheDirectory);

        $this->addTargetPath("m4n/data/record");
    }

    /**
     * Returns an array of properties for the current item in the feed, or null when there are no more items.
     * @return FeedItem|null
     * @throws FeedProcessException
     */
    protected function getNextItem()
    {
        $record = $this->getNextTargetElement();

        if ($record === null) {
            return null;
        }

        $feedItem = new FeedItem();
        $feedItem->setName($record["title"]->text);
        $feedItem->setId($record["offerid"]->text);
        $feedItem->setUrl($record["url"]->text);
        $feedItem->setDescription($record["description"]->text);
        $feedItem->setPrice($record["price"]->text);
        $feedItem->setCategoryPath(DataHandling::explode($record["category_path"]->text));

        if (isset($record["price_old"])) {
            $feedItem->setPreviousPrice($record["price_old"]->text);
        }

        if (isset($record["vendor"])) {
            $feedItem->setBrand($record["vendor"]->text);
        }

        if (isset($record["color"])) {
            $feedItem->setColors(DataHandling::explode($record["color"]->text));
        }

        if (isset($record["price_shipping"])) {
            $feedItem->setShippingPrice($record["price_shipping"]->text);
        }

        if (isset($record["material"])) {
            $feedItem->setMaterials(DataHandling::explode($record["material"]->text));
        }

        if (isset($record["size"])) {
            $feedItem->setSizes(DataHandling::explode($record["size"]->text));
        }

        if (isset($record["ean"])) {
            $feedItem->setEan($record["ean"]->text);
        }

        if (isset($record["largeimage"])) {
            $feedItem->addImageUrl($record["largeimage"]->text);
        } else {
            $feedItem->addImageUrl($record["image"]->text);
        }

        if (isset($record["image2"])) {
            $feedItem->addImageUrl($record["image2"]->text);
        }

        if (isset($record["age"])) {
            $feedItem->setAgeTarget($record["age"]->text);
        }

        if (isset($record["currency"])) {
            $feedItem->setCurrency($record["currency"]->text);
        }

        if (isset($record["timetoship"])) {
            $feedItem->setDeliveryTime($record["timetoship"]->text);
        }

        if (isset($record["publisher"])) {
            $feedItem->setVendor($record["publisher"]->text);
        }

        if (isset($record["gender"])) {
            $feedItem->setGenderTarget($record["gender"]->text);
        }

        return $feedItem;
    }
}
