<?php

namespace Villermen\FeedProcessing;

class FeedProcessor
{
    const FEED_TYPES = [
        "Daisycon" => Feeds\DaisyconFeed::class,
        "TradeTracker" => Feeds\TradeTrackerFeed::class,
        "FamilyBlend" => Feeds\FamilyBlendFeed::class,
        "Affilinet" => Feeds\AffilinetFeed::class,
        "Awin" => Feeds\AwinFeed::class,
        "Tradedoubler" => Feeds\TradedoublerFeed::class,
    ];
}
