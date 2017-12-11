<?php

use Villermen\DataHandling\DataHandling;
use Villermen\FeedProcessing\FeedItem;
use Villermen\FeedProcessing\FeedProcessException;
use Villermen\FeedProcessing\Feeds\DaisyconFeed;

class DaisyconFeedTest extends PHPUnit_Framework_TestCase
{
    /** @var DaisyconFeed */
    private $feed;

    public function setUp()
    {
        $this->feed = new DaisyconFeed("test/fixtures/daisycon.xml", 0);
    }

    public function testFileCaching()
    {
        // Override mtime of feed
        $someTimeAgo = time() - 100;
        touch($this->feed->getFilePath(), $someTimeAgo);
        clearstatcache();

        // This must return the same file as it is within the cache limit
        $feed2 = new DaisyconFeed("test/fixtures/daisycon.xml");
        clearstatcache();
        $file2MTime = filemtime($feed2->getFilePath());

        self::assertEquals($someTimeAgo, $file2MTime);

        // Expire the cached file
        $aLongTimeAgo = time() - 10000;
        touch($feed2->getFilePath(), $aLongTimeAgo);
        clearstatcache();

        $feed3 = new DaisyconFeed("test/fixtures/daisycon.xml");
        clearstatcache();
        $file3MTime = filemtime($feed3->getFilePath());

        self::assertNotEquals($aLongTimeAgo, $file3MTime, "", 10);
    }

    public function testItems()
    {
        $itemCount = 0;
        foreach ($this->feed as $feedItem) {
            $itemCount++;

            if ($itemCount === 1) {
                self::assertEquals("Dierplagenshop", $feedItem->getVendor());
                self::assertEquals("Edialux", $feedItem->getBrand());
                self::assertEquals(5.35, $feedItem->getShippingPrice());
                self::assertEquals(5.42, $feedItem->getPrice());
                self::assertEquals(2, $feedItem->getDeliveryTime());
                self::assertEquals("Kunststof weringsstrips 100 mm", $feedItem->getName());
                self::assertEquals(311, strlen($feedItem->getDescription()));
                self::assertEquals("EUR", $feedItem->getCurrency());
                self::assertEquals(121, strlen($feedItem->getUrl()));
                self::assertEquals(84, strlen($feedItem->getImageUrls()[0]));
                self::assertEquals(["ongediertebestrijding"], $feedItem->getCategoryPath());
                self::assertEquals("Dierplagenshop", $feedItem->getShop());
            }
        }

        self::assertEquals(5, $itemCount);
    }

    public function testFilter()
    {
        $this->feed->setFilter(function(FeedItem $feedItem) {
            if (stripos($feedItem->getName(), "kattenweg") === false) {
                return false;
            }

            $feedItem->setPrice(88.88);

            return $feedItem;
        });

        $itemCount = 0;
        foreach($this->feed as $itemId => $feedItem) {
            $itemCount++;

            self::assertEquals(88.88, $feedItem->getPrice());
            self::assertEquals("Hot Exit 500 ml (honden en kattenweg)", $feedItem->getName());

            self::assertEquals(4, $itemId);
        }

        self::assertEquals(1, $itemCount);
    }

    public function testFilterWithMatchingAndMapping()
    {
        $filter = function(FeedItem $feedItem) {
            if ($feedItem->matchesKeywords("weringsstrip")) {
                $feedItem->setCategoryPath(DataHandling::explode("Category > Subcategory > Something else"));
            } elseif ($feedItem->matchesKeywords(["zilvervis", "2 stuks"])) {
                $feedItem->setCategoryPath(["somecategory"]);
            } else {
                return false;
            }

            return $feedItem;
        };

        $this->feed->setFilter($filter);

        $feedItems = iterator_to_array($this->feed);

        self::assertEquals(2, count($feedItems));
        self::assertEquals("Kunststof weringsstrips 100 mm", $feedItems[0]->getName());
        self::assertEquals(["category", "subcategory", "something else"], $feedItems[0]->getCategoryPath());
        self::assertEquals("Roxasect Zilvervisjes val (set 2 stuks)", $feedItems[1]->getName());
        self::assertEquals(["somecategory"], $feedItems[1]->getCategoryPath());
    }

    public function testGetData()
    {
        $this->feed->next();
        $feedItem = $this->feed->current();
        $data = $feedItem->getData();

        self::assertCount(14, $data);
        self::assertEquals(2, $data["deliveryTime"]);
    }

    public function testHttpError()
    {
        try {
            new DaisyconFeed("https://a8s9d7f9j34.8dk1pdc/shouldnotexist");
            self::fail();
        } catch (FeedProcessException $exception) { }
    }
}
