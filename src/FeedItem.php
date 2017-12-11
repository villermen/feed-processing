<?php

namespace Villermen\FeedProcessing;

use Villermen\DataHandling\DataHandling;
use Villermen\DataHandling\DataHandlingException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Represents
 */
class FeedItem
{
    const ACCOMMODATION_HOTEL = "hotel";
    const ACCOMMODATION_APPARTMENT = "appartment";
    const ACCOMMODATIONS = [self::ACCOMMODATION_HOTEL, self::ACCOMMODATION_APPARTMENT];

    const GENDERTARGET_MALE = "male";
    const GENDERTARGET_FEMALE = "female";
    const GENDERTARGET_UNISEX = "unisex";
    const GENDERTARGETS = [self::GENDERTARGET_MALE, self::GENDERTARGET_FEMALE, self::GENDERTARGET_UNISEX];

    const CONDITION_NEW = "new";
    const CONDITION_USED = "used";
    const CONDITIONS = [self::CONDITION_NEW, self::CONDITION_USED];

    const TRANSPORT_PLANE = "plane";
    const TRANSPORT_BOAT = "boat";
    const TRANSPORT_OWN = "own";
    const TRANSPORTS = [self::TRANSPORT_PLANE, self::TRANSPORT_BOAT, self::TRANSPORT_OWN];

    const LOOKUP_FIELDS = [
        "name", "description", "currency", "categoryPath", "vendor", "brand", "condition", "genderTarget",
        "ageTarget", "colors", "sizes", "materials", "country", "region", "city", "origin", "transportType",
        "accomodation"
    ];

    // Common
    private $id;
    private $name;
    private $description;
    private $price;
    private $previousPrice;
    private $currency;
    private $url;
    private $imageUrls = [];
    private $categoryPath = [];
    private $rating;
    private $shop;

    // Product
    private $vendor;
    private $brand;
    private $brandLogoUrl;
    private $condition;
    private $ean;
    private $genderTarget;
    private $ageTarget;
    private $colors = [];
    private $sizes = [];
    private $materials = [];
    private $shippingPrice;
    private $deliveryTime;

    // Trip
    private $country;
    private $region;
    private $city;
    private $stars;
    private $longitude;
    private $latitude;
    private $allInclusive;
    private $duration;
    private $people;
    private $origin;
    private $transportType;
    private $accomodation;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id Must be unique per product within a feed.
     * @return FeedItem
     */
    public function setId($id)
    {
        $this->id = DataHandling::sanitizeString($id);
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return FeedItem
     */
    public function setName($name)
    {
        $this->name = DataHandling::sanitizeString($name);
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return FeedItem
     */
    public function setDescription($description)
    {
        $this->description = DataHandling::sanitizeText($description);
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return FeedItem
     */
    public function setPrice($price)
    {
        $this->price = DataHandling::sanitizeNumber($price);
        return $this;
    }

    /**
     * @return float
     */
    public function getPreviousPrice()
    {
        return $this->previousPrice;
    }

    /**
     * @param float $previousPrice
     * @return FeedItem
     */
    public function setPreviousPrice($previousPrice)
    {
        $this->previousPrice = DataHandling::sanitizeNumber($previousPrice);
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Uppercase three-letter currency identifier.
     *
     * @param string $currency
     * @return FeedItem
     * @throws FeedProcessException When currency is not a three-character currency code.
     */
    public function setCurrency($currency)
    {
        $currency = DataHandling::sanitizeString(strtoupper($currency));

        if ($currency !== null && strlen($currency ) !== 3) {
            throw new FeedProcessException("Currency must be a three-character currency code, \"{$currency}\" given.");
        }

        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return FeedItem
     */
    public function setUrl($url)
    {
        $this->url = DataHandling::sanitizeUrl($url);
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getImageUrls()
    {
        return $this->imageUrls;
    }

    /**
     * Primary image first.
     *
     * @param \string[] $imageUrls
     * @return FeedItem
     */
    public function setImageUrls($imageUrls)
    {
        $this->imageUrls = [];

        foreach($imageUrls as $imageUrl) {
            $this->addImageUrl($imageUrl);
        }

        return $this;
    }

    /**
     * Primary image first.
     *
     * @param string $imageUrl
     * @return FeedItem
     */
    public function addImageUrl($imageUrl)
    {
        $imageUrl = DataHandling::sanitizeUrl($imageUrl);

        if ($imageUrl) {
            $this->imageUrls[] = $imageUrl;
        }

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getCategoryPath()
    {
        return $this->categoryPath;
    }

    public function getCategoryPathAsString()
    {
        return DataHandling::implode($this->categoryPath);
    }

    /**
     * Highest level category first.
     *
     * @param string[] $categoryPath
     * @return FeedItem
     */
    public function setCategoryPath($categoryPath)
    {
        $this->categoryPath = [];

        foreach($categoryPath as $category) {
            $this->addCategory($category);
        }

        return $this;
    }

    /**
     * Highest level category first.
     *
     * @param string $category
     * @return FeedItem
     */
    public function addCategory($category)
    {
        $category = DataHandling::sanitizeString(mb_strtolower($category));

        if ($category) {
            $this->categoryPath[] = $category;
        }

        return $this;
    }

    /**
     * @return float|int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param float $rating
     * @return FeedItem
     * @throws DataHandlingException
     */
    public function setRating($rating)
    {
        $rating = DataHandling::sanitizeNumber($rating);

        if ($rating !== null) {
            DataHandling::validateInRange($rating, 1, 10, "rating");
        }

        $this->rating = $rating;

        return $this;
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param string $vendor
     * @return FeedItem
     */
    public function setVendor($vendor)
    {
        $this->vendor = DataHandling::sanitizeString($vendor);
        return $this;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     * @return FeedItem
     */
    public function setBrand($brand)
    {
        $this->brand = DataHandling::sanitizeString($brand);
        return $this;
    }

    /**
     * @return string
     */
    public function getBrandLogoUrl()
    {
        return $this->brandLogoUrl;
    }

    /**
     * @param string $brandLogoUrl
     * @return FeedItem
     */
    public function setBrandLogoUrl($brandLogoUrl)
    {
        $this->brandLogoUrl = DataHandling::sanitizeUrl($brandLogoUrl);
        return $this;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * FeedItem::CONDITION_*
     *
     * @param string $condition
     * @return FeedItem
     * @throws DataHandlingException
     */
    public function setCondition($condition)
    {
        $condition = DataHandling::sanitizeString(mb_strtolower($condition));

        if ($condition != null) {
            DataHandling::validateInArray($condition, self::CONDITIONS, "condition");
        }

        $this->condition = $condition;

        return $this;
    }

    /**
     * @return int
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param int $ean
     * @return FeedItem
     */
    public function setEan($ean)
    {
        $this->ean = DataHandling::sanitizeDigits($ean);

        return $this;
    }

    /**
     * @return string
     */
    public function getGenderTarget()
    {
        return $this->genderTarget;
    }

    /**
     * FeedItem::GENDERTARGET_*
     *
     * @param string $genderTarget
     * @return FeedItem
     */
    public function setGenderTarget($genderTarget)
    {
        $genderTarget = DataHandling::sanitizeAlphanumeric($genderTarget);

        if (!$genderTarget) {
            $this->genderTarget = null;
            return $this;
        }

        if (DataHandling::startsWith($genderTarget, "mei", "vrouw", "dam", "girl", "f")) {
            $this->genderTarget = FeedItem::GENDERTARGET_FEMALE;
        } elseif (DataHandling::startsWith($genderTarget, "jongen", "man", "her", "boy", "m")) {
            $this->genderTarget = FeedItem::GENDERTARGET_MALE;
        } else {
            $this->genderTarget = FeedItem::GENDERTARGET_UNISEX;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAgeTarget()
    {
        return $this->ageTarget;
    }

    /**
     * @param string $ageTarget
     * @return FeedItem
     */
    public function setAgeTarget($ageTarget)
    {
        $this->ageTarget = DataHandling::sanitizeString($ageTarget);
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Use most commonly used colors for easier matching.
     *
     * @param string[] $colors
     * @return FeedItem
     */
    public function setColors($colors)
    {
        $this->colors = [];

        foreach($colors as $color) {
            $this->addColor($color);
        }

        return $this;
    }

    /**
     * Use most commonly used colors for easier matching.
     *
     * @param string $color
     * @return FeedItem
     */
    public function addColor($color)
    {
        $color = DataHandling::sanitizeString(mb_strtolower($color));

        if ($color !== null) {
            $this->colors[] = $color;
        }

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getSizes()
    {
        return $this->sizes;
    }

    /**
     * Whatever size format is appropriate: letters, numbers, whatever.
     *
     * @param string[] $sizes
     * @return FeedItem
     */
    public function setSizes($sizes)
    {
        $this->sizes = [];

        foreach($sizes as $size) {
            $this->addSize($size);
        }

        return $this;
    }

    /**
     * Whatever size format is appropriate: letters, numbers, whatever.
     *
     * @param string $size
     * @return FeedItem
     */
    public function addSize($size)
    {
        $size = DataHandling::sanitizeString(mb_strtolower($size));

        if ($size !== null) {
            $this->sizes[] = $size;
        }

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getMaterials()
    {
        return $this->materials;
    }

    /**
     * Use most commonly used materials for easier matching.
     *
     * @param string[] $materials
     * @return FeedItem
     */
    public function setMaterials($materials)
    {
        $this->materials = [];

        foreach($materials as $material) {
            $this->addMaterial($material);
        }

        return $this;
    }

    /**
     * Use most commonly used materials for easier matching.
     *
     * @param string $material
     * @return FeedItem
     */
    public function addMaterial($material)
    {
        $material = DataHandling::sanitizeString(mb_strtolower($material));

        if ($material !== null) {
            $this->materials[] = $material;
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getShippingPrice()
    {
        return $this->shippingPrice;
    }

    /**
     * @param float $shippingPrice
     * @return FeedItem
     */
    public function setShippingPrice($shippingPrice)
    {
        $this->shippingPrice = DataHandling::sanitizeNumber($shippingPrice);
        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryTime()
    {
        return $this->deliveryTime;
    }

    /**
     * Delivery time in (work)days.
     *
     * @param int $deliveryTime
     * @return FeedItem
     */
    public function setDeliveryTime($deliveryTime)
    {
        $this->deliveryTime = DataHandling::sanitizeDigits($deliveryTime);

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return FeedItem
     */
    public function setCountry($country)
    {
        $this->country = DataHandling::sanitizeString($country);

        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return FeedItem
     */
    public function setRegion($region)
    {
        $this->region = DataHandling::sanitizeString($region);

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return FeedItem
     */
    public function setCity($city)
    {
        $this->city = DataHandling::sanitizeString($city);

        return $this;
    }

    /**
     * @return float
     */
    public function getStars()
    {
        return $this->stars;
    }

    /**
     * @param float $stars
     * @return FeedItem
     */
    public function setStars($stars)
    {
        $stars = DataHandling::sanitizeNumber($stars);

        if ($stars !== null) {
            DataHandling::validateInRange($stars, 0, 5, "stars");
        }

        $this->stars = $stars;

        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     * @return FeedItem
     */
    public function setLongitude($longitude)
    {
        $this->longitude = DataHandling::sanitizeNumber($longitude);

        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     * @return FeedItem
     */
    public function setLatitude($latitude)
    {
        $this->latitude = DataHandling::sanitizeNumber($latitude);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllInclusive()
    {
        return $this->allInclusive;
    }

    /**
     * @param bool $allInclusive
     * @return FeedItem
     */
    public function setAllInclusive($allInclusive)
    {
        $this->allInclusive = DataHandling::sanitizeBoolean($allInclusive);

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Amount of days.
     *
     * @param int $duration
     * @return FeedItem
     */
    public function setDuration($duration)
    {
        $this->duration = DataHandling::sanitizeDigits($duration);

        return $this;
    }

    /**
     * @return int
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * @param int $people
     * @return FeedItem
     */
    public function setPeople($people)
    {
        $this->people = DataHandling::sanitizeDigits($people);

        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return FeedItem
     */
    public function setOrigin($origin)
    {
        $this->origin = DataHandling::sanitizeString($origin);

        return $this;
    }

    /**
     * @return string
     */
    public function getTransportType()
    {
        return $this->transportType;
    }

    /**
     * @param string $transportType
     * @return FeedItem
     */
    public function setTransportType($transportType)
    {
        $transportType = DataHandling::sanitizeString(mb_strtolower($transportType));

        if ($transportType != null) {
            DataHandling::validateInArray($transportType, self::TRANSPORTS, "transportType");
        }

        $this->transportType = $transportType;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccomodation()
    {
        return $this->accomodation;
    }

    /**
     * FeedItem::ACCOMODATION_*
     *
     * @param string $accomodation
     * @return FeedItem
     */
    public function setAccomodation($accomodation)
    {
        $accomodation = DataHandling::sanitizeString(mb_strtolower($accomodation));

        if ($accomodation != null) {
            DataHandling::validateInArray($accomodation, self::ACCOMMODATIONS, "accomodation");
        }

        $this->accomodation = $accomodation;

        return $this;
    }

    /**
     * @return string
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param string $shop
     * @return FeedItem
     */
    public function setShop($shop)
    {
        $this->shop = DataHandling::sanitizeString($shop);

        return $this;
    }

    /**
     * Returns a string containing values of all properties relevant to matching against keywords.
     * Property values are separated by spaces and lowercased for convenience.
     * @return string
     */
    public function getLookupString()
    {
        $result = "";
        foreach($this->getData() as $key => $value) {
            if (!in_array($key, self::LOOKUP_FIELDS)) {
                continue;
            }

            if (is_array($value)) {
                $value = implode(" ", $value);
            }

            if ($value) {
                $result .= $value . " ";
            }
        }

        return mb_strtolower($result);
    }

    /**
     * Returns an array of keys and values with the non-default data of this FeedItem.
     * @return string[]
     */
    public function getData()
    {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);

        $data = [];
        foreach($properties as $property) {
            $name = $property->getName();
            $value = $this->$name;

            if ($value) {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    /**
     * Returns true if all keywords occur in the lookup string.
     * @param string|string[] $keyword1OrKeywordArray
     * @param string $keyword2,... Additional keywords if the first argument is a string
     * @return bool
     */
    public function matchesKeywords($keyword1OrKeywordArray, $keyword2 = null)
    {
        if (is_array($keyword1OrKeywordArray)) {
            $keywords = $keyword1OrKeywordArray;
        } else {
            $keywords = func_get_args();
        }

        $lookupString = $this->getLookupString();

        foreach($keywords as $keyword) {
            if (stripos($lookupString, $keyword) === false) {
                return false;
            }
        }

        return true;
    }
}
