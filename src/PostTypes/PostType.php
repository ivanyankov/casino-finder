<?php

declare(strict_types=1);

namespace CasinoFinder\PostTypes;

/**
 * Reusable custom post type registration wrapper.
 *
 * Encapsulates the WordPress register_post_type() call
 * with sensible defaults that can be overridden per instance.
 *
 * @since 1.0.0
 */
class PostType
{
    /** @var string Post type slug. */
    private string $slug;

    /** @var string Singular label. */
    private string $singular;

    /** @var string Plural label. */
    private string $plural;

    /** @var array<string, mixed> Additional arguments passed to register_post_type(). */
    private array $args;

    /**
     * @param string               $slug     Post type key (max 20 characters).
     * @param string               $singular Singular display name.
     * @param string               $plural   Plural display name.
     * @param array<string, mixed> $args     Optional overrides for register_post_type().
     */
    public function __construct(string $slug, string $singular, string $plural, array $args = [])
    {
        $this->slug     = $slug;
        $this->singular = $singular;
        $this->plural   = $plural;
        $this->args     = $args;
    }

    /**
     * Hook into WordPress to register the post type.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerPostType']);
    }

    /**
     * Callback for the init hook — registers the post type with WordPress.
     *
     * @return void
     */
    public function registerPostType(): void
    {
        register_post_type($this->slug, array_merge($this->defaults(), $this->args));
    }

    /**
     * Build the default arguments including generated labels.
     *
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'labels'       => $this->labels(),
            'public'       => true,
            'has_archive'  => true,
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-admin-post',
            'supports'     => ['title', 'editor', 'thumbnail'],
        ];
    }

    /**
     * Generate the full set of labels from singular and plural names.
     *
     * @return array<string, string>
     */
    private function labels(): array
    {
        return [
            'name'               => $this->plural,
            'singular_name'      => $this->singular,
            'add_new'            => sprintf(__('Add New %s', 'casino-finder'), $this->singular),
            'add_new_item'       => sprintf(__('Add New %s', 'casino-finder'), $this->singular),
            'edit_item'          => sprintf(__('Edit %s', 'casino-finder'), $this->singular),
            'new_item'           => sprintf(__('New %s', 'casino-finder'), $this->singular),
            'view_item'          => sprintf(__('View %s', 'casino-finder'), $this->singular),
            'search_items'       => sprintf(__('Search %s', 'casino-finder'), $this->plural),
            'not_found'          => sprintf(__('No %s found', 'casino-finder'), $this->plural),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', 'casino-finder'), $this->plural),
            'all_items'          => sprintf(__('All %s', 'casino-finder'), $this->plural),
            'menu_name'          => $this->plural,
        ];
    }
}
