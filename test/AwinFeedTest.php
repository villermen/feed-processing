<?php

use Villermen\FeedProcessing\FeedItem;
use Villermen\FeedProcessing\Feeds\AwinFeed;

class AwinFeedTest extends PHPUnit_Framework_TestCase
{
    /** @var AwinFeed */
    private $feed;

    public function setUp()
    {
        $this->feed = new AwinFeed("test/fixtures/awin.xml", 0);
    }

    public function testItems()
    {
        $itemCount = 0;
        foreach ($this->feed as $feedItem) {
            $itemCount++;

            if ($itemCount === 1) {
                self::assertEquals("347318", $feedItem->getId());
                self::assertEquals("LASCANA Beugelbikini met sierrand", $feedItem->getName());
                self::assertStringStartsWith("In een modieuze look met chique", $feedItem->getDescription());
                self::assertEquals(65.99, $feedItem->getPrice());
                self::assertEquals(5.50, $feedItem->getShippingPrice());
                self::assertStringStartsWith("http://ad.zanox.com/ppc/?1220", $feedItem->getUrl());
                self::assertStringStartsWith("https://images.heine.de/asset/heine/", $feedItem->getImageUrls()[0]);
                self::assertEquals(["dames", "beugelbikini’s"], $feedItem->getCategoryPath());
                self::assertEquals("Lascana", $feedItem->getBrand());
                self::assertEquals(FeedItem::GENDERTARGET_FEMALE, $feedItem->getGenderTarget());
                self::assertEquals(["bruin"], $feedItem->getColors());
                self::assertEquals(["null"], $feedItem->getSizes());
            }
        }

        self::assertEquals(5, $itemCount);
    }
}
