<?php

declare(strict_types=1);

namespace CasinoFinder\Taxonomies;

/**
 * Reusable taxonomy registration wrapper.
 *
 * @since 1.0.0
 */
class Taxonomy
{
    private string $slug;
    private string $singular;
    private string $plural;
    private string $postType;
    private array $args;

    /**
     * @param string $slug     Taxonomy key (max 32 characters).
     * @param string $singular Singular display name.
     * @param string $plural   Plural display name.
     * @param string $postType Post type to attach the taxonomy to.
     * @param array  $args     Optional overrides for register_taxonomy().
     */
    public function __construct(string $slug, string $singular, string $plural, string $postType, array $args = [])
    {
        $this->slug     = $slug;
        $this->singular = $singular;
        $this->plural   = $plural;
        $this->postType = $postType;
        $this->args     = $args;
    }

    public function register(): void
    {
        add_action('init', [$this, 'registerTaxonomy']);
    }

    public function registerTaxonomy(): void
    {
        register_taxonomy($this->slug, $this->postType, array_merge($this->defaults(), $this->args));
    }

    private function defaults(): array
    {
        return [
            'labels'            => $this->labels(),
            'hierarchical'      => true,
            'public'            => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
        ];
    }

    private function labels(): array
    {
        return [
            'name'              => $this->plural,
            'singular_name'     => $this->singular,
            'search_items'      => sprintf(__('Search %s', 'casino-finder'), $this->plural),
            'all_items'         => sprintf(__('All %s', 'casino-finder'), $this->plural),
            'parent_item'       => sprintf(__('Parent %s', 'casino-finder'), $this->singular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'casino-finder'), $this->singular),
            'edit_item'         => sprintf(__('Edit %s', 'casino-finder'), $this->singular),
            'update_item'       => sprintf(__('Update %s', 'casino-finder'), $this->singular),
            'add_new_item'      => sprintf(__('Add New %s', 'casino-finder'), $this->singular),
            'new_item_name'     => sprintf(__('New %s Name', 'casino-finder'), $this->singular),
            'menu_name'         => $this->plural,
        ];
    }
}
