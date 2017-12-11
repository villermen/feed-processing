<?php

use Villermen\FeedProcessing\FeedProcessor;
use Villermen\FeedProcessing\Feeds\Feed;

class FeedProcessorTest extends PHPUnit_Framework_TestCase
{
    public function testFeedtypes()
    {
        self::assertEquals(6, count(FeedProcessor::FEED_TYPES));

        foreach(FeedProcessor::FEED_TYPES as $feedType) {
            self::assertTrue(is_subclass_of($feedType, Feed::class));
        }
    }
}
