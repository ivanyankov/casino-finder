<?php

declare(strict_types=1);

namespace CasinoFinder\Rest;

use WP_REST_Request;
use WP_REST_Response;

/**
 * REST endpoint for the casino finder wizard results.
 *
 * Route: GET /wp-json/casino-finder/v1/search
 * Accepts taxonomy term slugs as query params and returns matching casinos.
 *
 * @since 1.0.0
 */
final class CasinoFinderController
{
    private const NAMESPACE = 'casino-finder/v1';
    private const ROUTE     = '/search';

    private const FILTERABLE_TAXONOMIES = [
        'casino_type',
        'casino_game',
        'casino_banking',
        'casino_payout_speed',
    ];

    /**
     * Hook the controller into WordPress REST API bootstrap.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register the /casino-finder/v1/search route.
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods'             => 'GET',
            'callback'            => [$this, 'search'],
            'permission_callback' => '__return_true',
            'args'                => $this->routeArgs(),
        ]);
    }

    /**
     * Define accepted query parameters — one per filterable taxonomy.
     */
    private function routeArgs(): array
    {
        $args = [];

        foreach (self::FILTERABLE_TAXONOMIES as $taxonomy) {
            $args[$taxonomy] = [
                'type'              => 'string',
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ];
        }

        $args['page'] = [
            'type'              => 'integer',
            'required'          => false,
            'default'           => 1,
            'sanitize_callback' => 'absint',
            'minimum'           => 1,
        ];

        $args['per_page'] = [
            'type'              => 'integer',
            'required'          => false,
            'default'           => 10,
            'sanitize_callback' => 'absint',
            'minimum'           => 1,
        ];

        return $args;
    }

    /**
     * Handle a finder search request and return matching casinos.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function search(WP_REST_Request $request): WP_REST_Response
    {
        $taxQuery = $this->buildTaxQuery($request);

        if ($taxQuery === null) {
            return new WP_REST_Response([
                'html'     => '',
                'page'     => 1,
                'has_more' => false,
            ], 200);
        }

        $page     = max(1, (int) $request->get_param('page'));
        $perPage  = (int) $request->get_param('per_page') ?: 10;
        $perPage  = max(1, min(50, $perPage));
        $query = new \WP_Query([
            'post_type'              => 'casino',
            'posts_per_page'         => $perPage,
            'post_status'            => 'publish',
            'tax_query'              => $taxQuery,
            'meta_key'               => 'casino_rating',
            'orderby'                => 'meta_value_num',
            'order'                  => 'DESC',
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
            'paged'                  => $page,
        ]);

        if (empty($query->posts)) {
            return new WP_REST_Response([
                'html'     => '',
                'page'     => $page,
                'has_more' => false,
            ], 200);
        }

        $html = '';

        foreach ($query->posts as $postId) {
            $html .= $this->renderCasinoCard((int) $postId);
        }

        return new WP_REST_Response([
            'html'     => $html,
            'page'     => $page,
            'has_more' => count($query->posts) === $perPage,
        ], 200);
    }

    /**
     * Build a WP_Query tax_query from the request params.
     * Only includes taxonomies that were actually sent.
     */
    private function buildTaxQuery(WP_REST_Request $request): ?array
    {
        $taxQuery  = ['relation' => 'AND'];
        $hasFilter = false;

        foreach (self::FILTERABLE_TAXONOMIES as $taxonomy) {
            $slug = (string) $request->get_param($taxonomy);

            if ($slug === '') {
                continue;
            }

            $hasFilter = true;

            $taxQuery[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $slug,
            ];
        }

        if (! $hasFilter) {
            return null;
        }

        return $taxQuery;
    }

    /**
     * Render a single casino card as HTML to be injected on the frontend.
     */
    private function renderCasinoCard(int $postId): string
    {
        $thumbnailId = get_post_thumbnail_id($postId);

        $rating        = (float) get_post_meta($postId, 'casino_rating', true);
        $affiliateUrl  = (string) get_post_meta($postId, 'casino_affiliate_url', true);
        $slotGames     = (int) get_post_meta($postId, 'casino_slot_games', true);
        $hasPackages   = (bool) get_post_meta($postId, 'casino_has_packages', true);
        $hasVip        = (bool) get_post_meta($postId, 'casino_has_vip', true);
        $bonusOffer    = (string) get_post_meta($postId, 'casino_bonus_offer', true);
        $bonusCode     = (string) get_post_meta($postId, 'casino_bonus_code', true);

        ob_start();
        ?>
        <div class="casino-finder-card">
            <div class="casino-finder-card__header">
                <div class="casino-finder-card__rating">
                    <span class="casino-finder-card__rating-icon" aria-hidden="true">
                        <svg width="16" height="15" viewBox="0 0 16 15" xmlns="http://www.w3.org/2000/svg" role="img" focusable="false">
                            <path d="M7.60846 0L9.40457 5.52786H15.2169L10.5146 8.94427L12.3107 14.4721L7.60846 11.0557L2.90618 14.4721L4.70229 8.94427L7.15256e-06 5.52786H5.81235L7.60846 0Z" fill="#FFB700"/>
                        </svg>
                    </span>
                    <span class="casino-finder-card__rating-text">
                        <span class="casino-finder-card__rating-value">
                            <?php echo esc_html(number_format($rating, 1)); ?>
                        </span>
                        <span class="casino-finder-card__rating-scale">/ 5</span>
                    </span>
                </div>
                <?php if ($thumbnailId) : ?>
                    <img
                        class="casino-finder-card__logo"
                        src="<?php echo esc_url(wp_get_attachment_image_url((int) $thumbnailId, 'medium')); ?>"
                        alt="<?php echo esc_attr(get_the_title($postId)); ?>"
                    />
                <?php endif; ?>
            </div>

            <div class="casino-finder-card__stats">
                <div class="casino-finder-card__stat">
                    <span class="casino-finder-card__stat-label">
                        <?php esc_html_e('Slot Games', 'casino-finder'); ?>
                    </span>
                    <span class="casino-finder-card__stat-value">
                            <?php echo esc_html((string) $slotGames); ?>
                        </span>
                    </span>
                </div>
                <div class="casino-finder-card__stat">
                    <span class="casino-finder-card__stat-label">
                        <?php esc_html_e('Packages', 'casino-finder'); ?>
                    </span>
                    <span class="casino-finder-card__stat-value">
                        <?php echo $hasPackages ? esc_html__('YES', 'casino-finder') : esc_html__('NO', 'casino-finder'); ?>
                    </span>
                </div>
                <div class="casino-finder-card__stat">
                    <span class="casino-finder-card__stat-label">
                        <?php esc_html_e('VIP', 'casino-finder'); ?>
                    </span>
                    <span class="casino-finder-card__stat-value">
                        <?php echo $hasVip ? esc_html__('YES', 'casino-finder') : esc_html__('NO', 'casino-finder'); ?>
                    </span>
                </div>
            </div>

            <?php if ($bonusOffer !== '') : ?>
                <div class="casino-finder-card__review">
                    <div class="casino-finder-card__review-title">
                        <?php
                        printf(
                            esc_html__('%s review', 'casino-finder'),
                            esc_html(get_the_title($postId))
                        );
                        ?>
                    </div>
                    <div class="casino-finder-card__review-text">
                        <?php echo esc_html($bonusOffer); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="casino-finder-card__code<?php echo $bonusCode === '' ? ' casino-finder-card__code--none' : ''; ?>">
                <?php echo $bonusCode === '' ? esc_html__('NO CODE NEEDED', 'casino-finder') : esc_html($bonusCode); ?>
            </div>

            <?php if ($affiliateUrl !== '') : ?>
                <a
                    class="casino-finder-card__cta"
                    href="<?php echo esc_url($affiliateUrl); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php
                    printf(
                        esc_html__('Play at %s', 'casino-finder'),
                        esc_html(get_the_title($postId))
                    );
                    ?>
                </a>
            <?php endif; ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Collect taxonomy term names.
     */
    private function getCasinoTags(int $postId): array
    {
        $tags = [];

        foreach (self::FILTERABLE_TAXONOMIES as $taxonomy) {
            $terms = wp_get_post_terms($postId, $taxonomy, ['fields' => 'names']);
            if (!is_wp_error($terms)) {
                $tags = array_merge($tags, $terms);
            }
        }

        return $tags;
    }
}
