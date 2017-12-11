<?php

namespace Villermen\FeedProcessing\Feeds;

use Villermen\DataHandling\DataHandling;
use Villermen\FeedProcessing\FeedItem;
use Villermen\FeedProcessing\FeedProcessException;

class TradeTrackerFeed extends XmlFeed
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
        $element = $this->getNextTargetElement();

        if ($element === null) {
            return null;
        }

        $feedItem = new FeedItem();
        $feedItem->setId($element->attributes["ID"]);
        $feedItem->setName($element["name"]->text);
        $feedItem->setDescription($element["description"]->text);
        $feedItem->setCurrency($element["price"]->attributes["currency"]);
        $feedItem->setPrice($element["price"]->text);
        $feedItem->setUrl($element["URL"]->text);

        $categories = $element["categories"]->get("category");
        foreach($categories as $category) {
            $feedItem->addCategory($category->text);
        }


        foreach($element["images"]->get("image") as $image) {
            $feedItem->addImageUrl($image->text);
        }

        foreach($element["properties"]->get("property") as $property) {
            if (!isset($property["value"])) {
                continue;
            }

            switch ($property->attributes["name"]) {
                case "country":
                    $feedItem->setCountry($property["value"]->text);
                    break;

                case "region":
                    $feedItem->setRegion($property["value"]->text);
                    break;

                case "city":
                    $feedItem->setCity($property["value"]->text);
                    break;

                case "stars":
                    $feedItem->setStars($property["value"]->text);
                    break;

                case "rating":
                    $feedItem->setRating($property["value"]->text);
                    break;

                case "accomodationType":
                    $feedItem->setAccomodation($property["value"]->text);
                    break;

                case "longitude":
                    $feedItem->setLongitude((float)$property["value"]->text);
                    break;

                case "latitude":
                    $feedItem->setLatitude((float)$property["value"]->text);
                    break;

                case "allInclusive":
                    $feedItem->setAllInclusive((bool)$property["value"]->text);
                    break;

                case "color":
                    $feedItem->setColors(DataHandling::explode($property["value"]->text));
                    break;

                case "fromPrice":
                    $feedItem->setPreviousPrice((float)$property["value"]->text);
                    break;

                case "brand":
                    $feedItem->setBrand($property["value"]->text);
                    break;

                case "categoryPath":
                    $feedItem->setCategoryPath(DataHandling::explode($property["value"]->text));
                    break;

                case "EAN":
                    $feedItem->setEan($property["value"]->text);
                    break;

                case "deliveryTime":
                    $feedItem->setDeliveryTime($property["value"]->text);
                    break;

                case "deliveryCosts":
                    $feedItem->setShippingPrice($property["value"]->text);
                    break;
            }
        }

        if (isset($element["variations"]["variation"])) {
            foreach ($element["variations"]["variation"]->get("property") as $property) {
                if (!isset($property["value"])) {
                    continue;
                }

                switch ($property->attributes["name"]) {
                    case "duration":
                        $feedItem->setDuration((int)$property["value"]->text);
                        break;

                    case "transportType":
                        $transportType = mb_strtolower($property["value"]->text);

                        if ($transportType === "flight") {
                            $transportType = FeedItem::TRANSPORT_PLANE;
                        } elseif ($transportType === "self") {
                            $transportType = FeedItem::TRANSPORT_OWN;
                        }

                        $feedItem->setTransportType($transportType);
                        break;
                }
            }
        }

        return $feedItem;
    }
}
