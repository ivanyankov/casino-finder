<?php

declare(strict_types=1);

namespace CasinoFinder\MetaBoxes;

use Yankov\MetaFieldsBuilder\MetaBox\MetaBoxBuilder;
use Yankov\MetaFieldsBuilder\Fields\TextField;
use Yankov\MetaFieldsBuilder\Fields\TextareaField;
use Yankov\MetaFieldsBuilder\Fields\URLField;
use Yankov\MetaFieldsBuilder\Fields\NumberField;
use Yankov\MetaFieldsBuilder\Fields\CheckboxField;

/**
 * Registers meta boxes for the Casino post type.
 *
 * Wizard filter categories (type, game, banking, payout speed) are handled
 * by taxonomies — only card display data and promo fields live here.
 *
 * @since 1.0.0
 */
final class CasinoMetaBoxes
{
    public function register(): void
    {
        $this->cardDetails();
        $this->bonusAndPromo();
    }

    private function cardDetails(): void
    {
        MetaBoxBuilder::make(
            'casino_card_details',
            __('Card Details', 'casino-finder'),
            [
                new NumberField('casino_rating', __('Rating', 'casino-finder'), min: 0, max: 5, step: 0.1),
                new URLField('casino_affiliate_url', __('Affiliate URL', 'casino-finder')),
                new NumberField('casino_slot_games', __('Slot Games Count', 'casino-finder'), min: 0, max: 10000, step: 1),
                new CheckboxField('casino_has_packages', __('Has Packages', 'casino-finder')),
                new CheckboxField('casino_has_vip', __('Has VIP Program', 'casino-finder')),
            ],
            'casino'
        );
    }

    private function bonusAndPromo(): void
    {
        MetaBoxBuilder::make(
            'casino_bonus_promo',
            __('Bonus & Promo', 'casino-finder'),
            [
                new TextareaField('casino_bonus_offer', __('Bonus Offer', 'casino-finder'), rows: 3),
                new TextField('casino_bonus_code', __('Bonus Code', 'casino-finder')),
            ],
            'casino'
        );
    }
}
