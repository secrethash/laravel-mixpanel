<?php

namespace GemGem\Modules\Mixpanel\Enums;

use Corals\Modules\Sales\Concerns\EnumToArray;

enum TrackingEvents: string
{
    use EnumToArray;

    case MakeOffer = 'Make an Offer';
    case AddToCart = 'Add to Cart';
    case ViewItem = 'View Item';
    case ViewCart = 'View Cart';
    case Purchase = 'Purchase';
    case AddListing = 'Add Listing';
}
