<?php

use Villermen\FeedProcessing\Feeds\AffilinetFeed;

class AffilinetFeedTest extends PHPUnit_Framework_TestCase
{
    /** @var AffilinetFeed */
    private $feed;

    public function setUp()
    {
        $this->feed = new AffilinetFeed("test/fixtures/affilinet.xml", 0);
    }

    public function testItems()
    {
        $itemCount = 0;
        foreach ($this->feed as $feedItem) {
            $itemCount++;

            if ($itemCount === 1) {
                self::assertEquals(42.50, $feedItem->getPrice());
                self::assertEquals("Radiatorbooster", $feedItem->getName());
                self::assertEquals("Ecosavers", $feedItem->getBrand());
                self::assertEquals(680, strlen($feedItem->getDescription()));
                self::assertEquals(141, strlen($feedItem->getUrl()));
                self::assertEquals(["https://www.bespaarbazaar.nl/images/Radiator Ventilator.jpg"], $feedItem->getImageUrls());
                self::assertEquals(["isolatiemateriaal"], $feedItem->getCategoryPath());
            }
        }

        self::assertEquals(5, $itemCount);
    }
}
