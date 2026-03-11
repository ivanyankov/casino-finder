<?php

declare(strict_types=1);

namespace CasinoFinder\Rest;

use CasinoFinder\Support\TemplateRenderer;
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
            'no_found_rows'          => false,
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

        $html          = '';
        $totalMatches  = (int) $query->found_posts;

        foreach ($query->posts as $postId) {
            $html .= $this->renderCasinoCard((int) $postId);
        }

        return new WP_REST_Response([
            'html'           => $html,
            'page'           => $page,
            'total_matches'  => $totalMatches,
            'has_more'       => ($page * $perPage) < $totalMatches,
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
        return TemplateRenderer::render('casino-card.php', [
            'postId'      => $postId,
            'thumbnailId' => $thumbnailId,
            'rating'      => $rating,
            'affiliateUrl'=> $affiliateUrl,
            'slotGames'   => $slotGames,
            'hasPackages' => $hasPackages,
            'hasVip'      => $hasVip,
            'bonusOffer'  => $bonusOffer,
            'bonusCode'   => $bonusCode,
        ]);
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
