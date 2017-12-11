<?php

use Villermen\FeedProcessing\FeedItem;
use Villermen\FeedProcessing\Feeds\TradeTrackerFeed;

class TradeTrackerFeedTest extends PHPUnit_Framework_TestCase
{
    /** @var TradeTrackerFeed */
    private $feed;

    public function setUp()
    {
        $this->feed = new TradeTrackerFeed("test/fixtures/tradetracker.xml", 0);
    }

    public function testItems()
    {
        $itemCount = 0;
        foreach ($this->feed as $feedItem) {
            $itemCount++;

            if ($itemCount === 1) {
                self::assertEquals(55685, $feedItem->getId());
                self::assertEquals("Platanista", $feedItem->getName());
                self::assertEquals(479, strlen($feedItem->getDescription()));
                self::assertEquals(529, $feedItem->getPrice());
                self::assertEquals("EUR", $feedItem->getCurrency());
                self::assertEquals(145, strlen($feedItem->getUrl()));
                self::assertEquals(5, count($feedItem->getImageUrls()));
                self::assertEquals(["zonvakantie", "vakantie"], $feedItem->getCategoryPath());
                self::assertEquals(9.2, $feedItem->getRating());
                self::assertEquals("Griekenland", $feedItem->getCountry());
                self::assertEquals("Kos", $feedItem->getRegion());
                self::assertEquals("Kos-stad/Psalidi", $feedItem->getCity());
                self::assertEquals(4.5, $feedItem->getStars());
                self::assertEquals(27, round($feedItem->getLongitude()));
                self::assertEquals(37, round($feedItem->getLatitude()));
                self::assertEquals(true, $feedItem->isAllInclusive());
                self::assertEquals(8, $feedItem->getDuration());
                self::assertEquals(FeedItem::TRANSPORT_PLANE, $feedItem->getTransportType());
            }
        }

        self::assertEquals(5, $itemCount);
    }
}
