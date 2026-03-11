<?php

declare(strict_types=1);

namespace CasinoFinder\Shortcodes;

use CasinoFinder\Support\TemplateRenderer;

/**
 * Renders the Casino Finder wizard via [casino_finder] shortcode.
 *
 * Assets are registered on wp_enqueue_scripts but only enqueued when the
 * shortcode actually renders. Wizard options are pulled from taxonomy terms
 * so admins can manage choices without touching code.
 *
 * @since 1.0.0
 */
final class CasinoFinderShortcode
{
    private const SHORTCODE_TAG = 'casino_finder';
    private const SCRIPT_HANDLE = 'casino-finder-wizard';
    private const STYLE_HANDLE  = 'casino-finder-wizard';

    private const WIZARD_TAXONOMIES = [
        'casino_type'         => ['label' => 'Type of casino',  'question' => 'What type of casino are you looking for?'],
        'casino_game'         => ['label' => 'Game',            'question' => 'What is your favourite type of game?'],
        'casino_banking'      => ['label' => 'Banking',         'question' => 'Which payment method do you prefer?'],
        'casino_payout_speed' => ['label' => 'Payouts',         'question' => 'How fast do you want to receive your winnings?'],
    ];

    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
        add_shortcode(self::SHORTCODE_TAG, [$this, 'render']);
    }

    public function registerAssets(): void
    {
        wp_register_style(
            self::STYLE_HANDLE,
            CASINO_FINDER_PLUGIN_URL . 'assets/css/casino-finder.css',
            [],
            CASINO_FINDER_VERSION
        );

        wp_register_script(
            self::SCRIPT_HANDLE,
            CASINO_FINDER_PLUGIN_URL . 'assets/js/casino-finder.js',
            [],
            CASINO_FINDER_VERSION,
            true
        );
    }

    /**
     * Shortcode callback — enqueues assets only here, passes config via wp_localize_script.
     */
    public function render($atts): string
    {
        $atts = shortcode_atts([
            'title'     => __('Casino Finder', 'casino-finder'),
            'per_page'  => 10,
            'load_more' => 'yes',
        ], $atts, self::SHORTCODE_TAG);

        $perPage = (int) $atts['per_page'] ?: 10;
        $perPage = max(1, min(50, $perPage));
        $loadMoreRaw     = strtolower((string) $atts['load_more']);
        $enableLoadMore  = in_array($loadMoreRaw, ['1', 'true', 'yes'], true);

        wp_enqueue_style(self::STYLE_HANDLE);
        wp_enqueue_script(self::SCRIPT_HANDLE);

        wp_localize_script(self::SCRIPT_HANDLE, 'casinoFinderWizard', [
            'restUrl' => rest_url('casino-finder/v1/search'),
            'nonce'   => wp_create_nonce('wp_rest'),
            'perPage' => $perPage,
            'enableLoadMore' => $enableLoadMore,
            'i18n'    => [
                'startOver'    => __('Start Over', 'casino-finder'),
                'back'         => __('Back', 'casino-finder'),
                'loading'      => __('Finding your perfect casino...', 'casino-finder'),
                'chooseOption' => __('Choose from the following options:', 'casino-finder'),
                'best'         => __('Best', 'casino-finder'),
                'noResults'    => __('No casinos matched your criteria.', 'casino-finder'),
                'error'        => __('Something went wrong. Please try again.', 'casino-finder'),
                'claimOffer'   => __('CLAIM OFFER', 'casino-finder'),
                'noCodeNeeded' => __('NO CODE NEEDED', 'casino-finder'),
                'bestFor'      => __('This casino is best for:', 'casino-finder'),
                'slotGames'    => __('Slot Games', 'casino-finder'),
                'packages'     => __('Packages', 'casino-finder'),
                'vip'          => __('VIP', 'casino-finder'),
                'yes'          => __('YES', 'casino-finder'),
                'no'           => __('NO', 'casino-finder'),
                'rating'       => __('Rating', 'casino-finder'),
                'playAt'       => __('Play at', 'casino-finder'),
                'loadMore'     => __('Load more results', 'casino-finder'),
            ],
        ]);

        return $this->buildMarkup($atts);
    }

    /**
     * Build wizard configuration from taxonomy terms. Each taxonomy = one step,
     * its terms = selectable options. Includes term images when available.
     */
    private function buildWizardConfig(): array
    {
        $steps = [];

        foreach (self::WIZARD_TAXONOMIES as $taxonomy => $config) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);

            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }

            $options = [];
            foreach ($terms as $term) {
                $imageId  = (int) get_term_meta($term->term_id, $taxonomy . '_image_id', true);
                $imageUrl = $imageId ? wp_get_attachment_image_url($imageId, 'thumbnail') : '';

                $options[] = [
                    'slug'  => $term->slug,
                    'name'  => $term->name,
                    'image' => $imageUrl,
                ];
            }

            $steps[] = [
                'key'      => $taxonomy,
                'label'    => __($config['label'], 'casino-finder'),
                'question' => __($config['question'], 'casino-finder'),
                'options'  => $options,
            ];
        }

        return $steps;
    }

    private function buildMarkup(array $atts): string
    {
        $title = esc_html($atts['title']);
        $steps = $this->buildWizardConfig();
        return TemplateRenderer::render('wizard.php', [
            'title' => $title,
            'steps' => $steps,
        ]);
    }
}
