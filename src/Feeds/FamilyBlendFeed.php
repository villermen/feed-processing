<?php

namespace Villermen\FeedProcessing\Feeds;

use Villermen\DataHandling\DataHandling;
use Villermen\FeedProcessing\FeedItem;
use Villermen\FeedProcessing\FeedProcessException;

/**
 * FamilyBlend or Affiliate4you (previous name).
 * Is trying to phase out into Affilinet system because they like complicating things.
 */
class FamilyBlendFeed extends XmlFeed
{
    private $feedVendor = null;

    public function __construct($file, $cacheTime = 3600, $cacheDirectory = null)
    {
        parent::__construct($file, $cacheTime, $cacheDirectory);

        $this->addTargetPath("datafeeds/datafeed/advertiser_site");
        $this->addTargetPath("datafeeds/datafeed/offers/offer");
    }

    /**
     * Returns an array of properties for the current item in the feed, or null when there are no more items.
     * @return FeedItem|null
     * @throws FeedProcessException
     */
    protected function getNextItem()
    {
        $element = $this->getNextTargetElement();

        if ($element === null) {
            return null;
        }

        if ($element->tagName === "advertiser_site") {
            $this->feedVendor = $element->text;

            $element = $this->getNextTargetElement();

            if ($element === null) {
                return null;
            }
        }

        $feedItem = new FeedItem();
        $feedItem->setVendor($this->feedVendor);
        $feedItem->setId($element["product_id"]->text);
        $feedItem->setName($element["name"]->text);
        $feedItem->setDescription($element["description"]->text);
        $feedItem->setPrice($element["price"]->text);
        $feedItem->addImageUrl($element["image"]->text);
        $feedItem->setUrl($element["url"]->text);

        if (isset($element["price_from"])) {
            $feedItem->setPreviousPrice($element["price_from"]->text);
        }

        if (isset($element["brand"])) {
            $feedItem->setBrand($element["brand"]->text);
        }

        if (isset($element["size"])) {
            $feedItem->setSizes(array_filter(explode(";", mb_strtolower($element["size"]->text))));
        }

        if (isset($element["color"])) {
            $color = $element["color"]->text;
            if ($color) {
                $feedItem->setColors([$color]);
            }
        }

        if (isset($element["gender"])) {
            $feedItem->setGenderTarget($element["gender"]->text);
        }

        if (isset($element["category_path"])) {
            $feedItem->setCategoryPath(DataHandling::explode($element["category_path"]->text));
        } elseif (isset($element["category"])) {
            $feedItem->setCategoryPath([$element["category"]->text]);

            if (isset($element["subcategory"])) {
                $feedItem->addCategory($element["subcategory"]->text);

                if (isset($element["subsubcategory"])) {
                    $feedItem->addCategory($element["subsubcategory"]->text);
                }
            }
        }

        return $feedItem;
    }
}
