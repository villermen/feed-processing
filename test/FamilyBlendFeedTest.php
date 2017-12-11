<?php

use Villermen\FeedProcessing\FeedItem;
use Villermen\FeedProcessing\Feeds\FamilyBlendFeed;

class FamilyBlendFeedTest extends PHPUnit_Framework_TestCase
{
    /** @var FamilyBlendFeed */
    private $feed;

    public function setUp()
    {
        $this->feed = new FamilyBlendFeed("test/fixtures/familyblend.xml", 0);
    }

    public function testItems()
    {
        $itemCount = 0;
        foreach ($this->feed as $feedItem) {
            $itemCount++;

            if ($itemCount === 1) {
                self::assertEquals("42bst00818", $feedItem->getId());
                self::assertEquals("Blue Star Jeans Shirt lange mouw", $feedItem->getName());
                self::assertEquals("De opdruk heeft een vintage-look.", $feedItem->getDescription());
                self::assertEquals(11.98, $feedItem->getPrice());
                self::assertEquals(29.95, $feedItem->getPreviousPrice());
                self::assertEquals(130, strlen($feedItem->getUrl()));
                self::assertEquals(57, strlen($feedItem->getImageUrls()[0]));
                self::assertEquals(["shirt"], $feedItem->getCategoryPath());
                self::assertEquals("happybee", $feedItem->getVendor());
                self::assertEquals(FeedItem::GENDERTARGET_MALE, $feedItem->getGenderTarget());
                self::assertEquals(["legergroen"], $feedItem->getColors());
                self::assertEquals(["140", "116"], $feedItem->getSizes());
            }
        }

        self::assertEquals(5, $itemCount);
    }
}
