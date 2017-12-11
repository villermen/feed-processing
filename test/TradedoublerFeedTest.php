<?php

use Villermen\FeedProcessing\Feeds\TradedoublerFeed;

class TradedoublerFeedTest extends PHPUnit_Framework_TestCase
{
    /** @var TradedoublerFeed */
    private $feed;

    public function setUp()
    {
        $this->feed = new TradedoublerFeed("test/fixtures/tradedoubler.xml", 0);
    }

    public function testItems()
    {
        $itemCount = 0;
        foreach ($this->feed as $feedItem) {
            $itemCount++;

            if ($itemCount === 1) {
                self::assertEquals("2378358229", $feedItem->getId());
                self::assertEquals("kokerrok", $feedItem->getName());
                self::assertStringStartsWith("Een dames rok van Expre", $feedItem->getDescription());
                self::assertEquals(69.95, $feedItem->getPrice());
                self::assertEquals(0, $feedItem->getShippingPrice());
                self::assertStringStartsWith("http://pdt.tradedoubler.com/click?a(23", $feedItem->getUrl());
                self::assertStringStartsWith("https://assets.wehkamp.com", $feedItem->getImageUrls()[0]);
                self::assertEquals(["damesmode", "dames rokken", "dames kokerrokken"], $feedItem->getCategoryPath());
                self::assertEquals("Expresso", $feedItem->getBrand());
                self::assertEquals(["40"], $feedItem->getSizes());
                self::assertEquals("EUR", $feedItem->getCurrency());
                self::assertEquals(8718264641076, $feedItem->getEan());
            }
        }

        self::assertEquals(5, $itemCount);
    }
}
