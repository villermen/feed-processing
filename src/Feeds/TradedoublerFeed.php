<?php

namespace Villermen\FeedProcessing\Feeds;

use Villermen\DataHandling\DataHandling;
use Villermen\FeedProcessing\FeedItem;
use Villermen\FeedProcessing\FeedProcessException;

class TradedoublerFeed extends XmlFeed
{
    public function __construct($file, $cacheTime = 3600, $cacheDirectory = null)
    {
        parent::__construct($file, $cacheTime, $cacheDirectory);

        $this->addTargetPath("products/product");
    }

    /**
     * Returns an array of properties for the current item in the feed, or null when there are no more items.
     * @return FeedItem|null
     * @throws FeedProcessException
     */
    protected function getNextItem()
    {
        $product = $this->getNextTargetElement();

        if ($product === null) {
            return null;
        }

        $feedItem = new FeedItem();
        $feedItem->setName($product["name"]->text);
        $feedItem->setUrl($product["productUrl"]->text);
        $feedItem->addImageUrl($product["imageUrl"]->text);
        $feedItem->setDescription($product["description"]->text);
        $feedItem->setPrice($product["price"]->text);
        $feedItem->setCurrency($product["currency"]->text);
        $feedItem->setId($product["TDProductId"]->text);
        $feedItem->setPreviousPrice($product["previousPrice"]->text);
        $feedItem->setShippingPrice($product["shippingCost"]->text);
        $feedItem->setDeliveryTime($product["deliveryTime"]->text);
        $feedItem->setSizes(DataHandling::explode($product["size"]->text));
        $feedItem->setBrand($product["brand"]->text);
        $feedItem->setEan($product["ean"]->text);
        $feedItem->setCategoryPath(DataHandling::explode($product["merchantCategoryName"]->text));

        return $feedItem;
    }
}
