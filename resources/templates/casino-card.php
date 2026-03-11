<?php
/**
 * Single casino result card.
 *
 * Expects variables set by the caller:
 * - int    $postId
 * - int    $thumbnailId
 * - float  $rating
 * - string $affiliateUrl
 * - int    $slotGames
 * - bool   $hasPackages
 * - bool   $hasVip
 * - string $bonusOffer
 * - string $bonusCode
 */
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

