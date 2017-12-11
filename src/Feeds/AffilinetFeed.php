<?php

namespace Villermen\FeedProcessing\Feeds;

use Villermen\DataHandling\DataHandling;
use Villermen\FeedProcessing\FeedItem;

class AffilinetFeed extends XmlFeed
{
    public function __construct($file, $cacheTime = 3600, $cacheDirectory = null)
    {
        parent::__construct($file, $cacheTime, $cacheDirectory);

        $this->addTargetPath("Products/Product");
    }

    protected function getNextItem()
    {
        $element = $this->getNextTargetElement();

        if ($element === null) {
            return null;
        }

        $feedItem = new FeedItem();

        $feedItem->setId($element->attributes["ArticleNumber"]);

        $feedItem->setCategoryPath(DataHandling::explode($element["CategoryPath"]["ProductCategoryPath"]->text));
        $feedItem->setPrice($element["Price"]["Price"]->text);
        $feedItem->setPreviousPrice($element["Price"]["PriceOld"]->text);
        $feedItem->setUrl($element["Deeplinks"]["Product"]->text);
        $feedItem->setName($element["Details"]["Title"]->text);
        $feedItem->setDescription($element["Details"]["Description"]->text);
        $feedItem->setVendor($element["Details"]["Distributor"]->text);
        $feedItem->setBrand($element["Details"]["Brand"]->text);

        $feedItem->setImageUrls(array_map(function($imgElement) {
            return $imgElement["URL"]->text;
        }, $element["Images"]->get("Img")));

        $feedItem->setBrand($element["Details"]["Brand"]->text);

        // This is not supplied in any of the feeds I looked into, and is an assumption here
        $feedItem->setShippingPrice($element["Shipping"]->text);

        return $feedItem;
    }
}
