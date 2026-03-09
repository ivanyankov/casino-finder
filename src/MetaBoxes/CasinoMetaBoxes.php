<?php

declare(strict_types=1);

namespace CasinoFinder\MetaBoxes;

use Yankov\MetaFieldsBuilder\MetaBox\MetaBoxBuilder;
use Yankov\MetaFieldsBuilder\Fields\TextField;
use Yankov\MetaFieldsBuilder\Fields\TextareaField;
use Yankov\MetaFieldsBuilder\Fields\URLField;
use Yankov\MetaFieldsBuilder\Fields\NumberField;
use Yankov\MetaFieldsBuilder\Fields\SelectField;
use Yankov\MetaFieldsBuilder\Fields\CheckboxField;
use Yankov\MetaFieldsBuilder\Fields\CheckboxGroupField;

/**
 * Registers all meta boxes and fields for the Casino post type.
 *
 * Fields are grouped into three meta boxes:
 * - Card Details: data displayed on the casino result card
 * - Wizard Filters: checkboxes used by the step-by-step finder
 * - Bonus & Promo: bonus offer text and promo codes
 *
 * @since 1.0.0
 */
final class CasinoMetaBoxes
{
    /**
     * Register all casino meta boxes.
     *
     * @return void
     */
    public function register(): void
    {
        $this->cardDetails();
        $this->wizardFilters();
        $this->bonusAndPromo();
    }

    /**
     * Card details — data shown on the casino result card.
     *
     * @return void
     */
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

    /**
     * Wizard filters — checkboxes that power the step-by-step finder.
     *
     * @return void
     */
    private function wizardFilters(): void
    {
        MetaBoxBuilder::make(
            'casino_wizard_filters',
            __('Wizard Filters', 'casino-finder'),
            [
                new CheckboxGroupField('casino_type', __('Type of Casino', 'casino-finder'), [
                    'social'       => __('Social', 'casino-finder'),
                    'no_deposit'   => __('No Deposit', 'casino-finder'),
                    'sweepstakes'  => __('Sweepstakes', 'casino-finder'),
                    'fast_paying'  => __('Fast Paying', 'casino-finder'),
                    'online'       => __('Online Casino', 'casino-finder'),
                ]),

                new CheckboxGroupField('casino_games', __('Games', 'casino-finder'), [
                    'blackjack'   => __('Blackjack', 'casino-finder'),
                    'slots'       => __('Slots', 'casino-finder'),
                    'live_dealer' => __('Live Dealer', 'casino-finder'),
                ]),

                new CheckboxGroupField('casino_banking', __('Banking Methods', 'casino-finder'), [
                    'credit_card' => __('Credit Card', 'casino-finder'),
                    'venmo'       => __('Venmo', 'casino-finder'),
                    'paypal'      => __('PayPal', 'casino-finder'),
                ]),

                new SelectField('casino_payout_speed', __('Payout Speed', 'casino-finder'), [
                    '24_hours'     => __('24 Hours', 'casino-finder'),
                    '1_2_days'     => __('1-2 Days', 'casino-finder'),
                    'up_to_1_week' => __('Up To 1 Week', 'casino-finder'),
                ]),
            ],
            'casino'
        );
    }

    /**
     * Bonus and promotional information.
     *
     * @return void
     */
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
