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
        <?php if ($bonusCode === '') : ?>
            <?php echo esc_html__('NO CODE NEEDED', 'casino-finder'); ?>
        <?php else : ?>
            <span class="casino-finder-card__code-text" data-code="<?php echo esc_attr($bonusCode); ?>">
                <?php echo esc_html($bonusCode); ?>
            </span>
            <button
                type="button"
                class="casino-finder-card__code-copy"
                aria-label="<?php esc_attr_e('Copy bonus code', 'casino-finder'); ?>"
            >
                <span aria-hidden="true">
                    <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1V4.4C12 4.96005 12 5.24008 12.109 5.45399C12.2049 5.64215 12.3578 5.79513 12.546 5.89101C12.7599 6 13.0399 6 13.6 6H17M7 6H3C1.89543 6 1 6.89543 1 8V17C1 18.1046 1.89543 19 3 19H9C10.1046 19 11 18.1046 11 17V14M13 1H10.2C9.07989 1 8.51984 1 8.09202 1.21799C7.71569 1.40973 7.40973 1.71569 7.21799 2.09202C7 2.51984 7 3.0799 7 4.2V10.8C7 11.9201 7 12.4802 7.21799 12.908C7.40973 13.2843 7.71569 13.5903 8.09202 13.782C8.51984 14 9.0799 14 10.2 14H13.8C14.9201 14 15.4802 14 15.908 13.782C16.2843 13.5903 16.5903 13.2843 16.782 12.908C17 12.4802 17 11.9201 17 10.8V5L13 1Z" stroke="#C29034" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </span>
            </button>
        <?php endif; ?>
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

