<?php

namespace Villermen\FeedProcessing\Feeds;

use Villermen\DataHandling\DataHandling;
use Villermen\FeedProcessing\FeedItem;

class DaisyconFeed extends XmlFeed
{
    private $programCurrency = "EUR";
    private $programName = null;
    private $programCategory = null;

    public function __construct($file, $cacheTime = 3600, $cacheDirectory = null)
    {
        parent::__construct($file, $cacheTime, $cacheDirectory);

        $this->addTargetPath("datafeed/info");
        $this->addTargetPath("datafeed/programs/program/program_info");
        $this->addTargetPath("datafeed/programs/program/products/product");
    }

    protected function getNextItem()
    {
        $element = $this->getNextTargetElement();

        if ($element === null) {
            return null;
        }

        // Set properties that apply to all products
        if ($element->tagName == "info") {
            $this->programCategory = $element["category"]->text;

            $element = $this->getNextTargetElement();

            if ($element === null) {
                return null;
            }
        }

        if ($element->tagName == "program_info") {
            $this->programCurrency = $element["currency"]->text;
            $this->programName = $element["name"]->text;

            $element = $this->getNextTargetElement();

            if ($element === null) {
                return null;
            }
        }

        while ($element["update_info"]["status"]->text !== "active") {
            $element = $this->getNextTargetElement();

            if ($element === null) {
                return null;
            }
        }

        $feedItem = new FeedItem();

        $feedItem->setCurrency($this->programCurrency);
        $feedItem->setVendor($this->programName);
        $feedItem->setShop($this->programName);
        $feedItem->setId($element["update_info"]["daisycon_unique_id"]->text);
        $feedItem->setUrl($element["product_info"]["link"]->text);
        $feedItem->setBrand($element["product_info"]["brand"]->text);
        $feedItem->setBrandLogoUrl($element["product_info"]["brand_logo"]->text);
        $feedItem->setCondition($element["product_info"]["condition"]->text);
        $feedItem->setName($element["product_info"]["title"]->text);
        $feedItem->setDescription($element["product_info"]["description"]->text);
        $feedItem->setPrice($element["product_info"]["price"]->text);
        $feedItem->setPreviousPrice($element["product_info"]["price_old"]->text);
        $feedItem->setShippingPrice($element["product_info"]["price_shipping"]->text);
        $feedItem->setGenderTarget($element["product_info"]["gender_target"]->text);
        $feedItem->setEan($element["product_info"]["ean"]->text);

        $primaryColor = $element["product_info"]["color_primary"]->text;
        if ($primaryColor) {
            $feedItem->setColors([$primaryColor]);
        }

        foreach($element["product_info"]["images"]->get("image") as $image) {
            $feedItem->addImageUrl($image["location"]->text);
        }

        $feedItem->setDeliveryTime($element["product_info"]["delivery_time"]->text);
        if (!$feedItem->getDeliveryTime()) {
            $feedItem->setDeliveryTime($element["product_info"]["delivery_description"]->text);
        }

        $size = $element["product_info"]["size"]->text;
        if (!$size) {
            $size = $element["product_info"]["size_description"]->text;
        }

        if ($size) {
            $feedItem->addSize($size);
        }

        $feedItem->setCategoryPath(DataHandling::explode($element["product_info"]["category_path"]->text));
        if (!$feedItem->getCategoryPath()) {
            $feedItem->setCategoryPath([$element["product_info"]["category"]->text]);
        }
        if (!$feedItem->getCategoryPath()) {
            $feedItem->setcategoryPath([$this->programCategory]);
        }

        return $feedItem;
    }
}
