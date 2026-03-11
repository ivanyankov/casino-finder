<?php
/**
 * Casino Finder wizard markup.
 *
 * Expects:
 * - string $title
 * - array  $steps
 */
?>
<div class="casino-finder-wizard" id="casino-finder-wizard">
    <h2 class="casino-finder-wizard__title"><?php echo $title; ?></h2>

    <p class="casino-finder-wizard__intro">
        <?php esc_html_e('Find the perfect online casino here. Choose from bonus offers, payout speed, types of games and more.', 'casino-finder'); ?>
    </p>

    <div class="casino-finder-wizard__progress" id="casino-finder-progress">
        <ol class="casino-finder-progress__list">
            <?php foreach ($steps as $index => $step) : ?>
                <li class="casino-finder-progress__step" data-step="<?php echo (int) $index; ?>">
                    <span class="casino-finder-progress__circle">
                        <?php echo (int) ($index + 1); ?>
                    </span>
                    <span class="casino-finder-progress__label">
                        <?php echo esc_html($step['label']); ?>
                    </span>
                </li>
            <?php endforeach; ?>
            <li class="casino-finder-progress__step" data-step="<?php echo count($steps); ?>">
                <span class="casino-finder-progress__circle">
                    <?php echo (int) (count($steps) + 1); ?>
                </span>
                <span class="casino-finder-progress__label">
                    <?php esc_html_e('Best', 'casino-finder'); ?>
                </span>
            </li>
        </ol>
    </div>

    <div class="casino-finder-wizard__toolbar is-hidden" id="casino-finder-toolbar">
        <button type="button" class="casino-finder-wizard__btn casino-finder-wizard__btn--start-over" id="casino-finder-start-over">
            <?php esc_html_e('Start Over', 'casino-finder'); ?>
        </button>
    </div>

    <div class="casino-finder-wizard__summary is-hidden" id="casino-finder-summary">
        <div class="casino-finder-summary__text">
            <p class="casino-finder-summary__headline" id="casino-finder-summary-headline"></p>
            <p class="casino-finder-summary__subline" id="casino-finder-summary-subline"></p>
        </div>
        <div class="casino-finder-summary__tags">
            <p class="casino-finder-summary__tags-label">
                <?php esc_html_e('This casino is best for:', 'casino-finder'); ?>
            </p>
            <div class="casino-finder-summary__tags-list" id="casino-finder-summary-tags"></div>
        </div>
    </div>

    <div class="casino-finder-wizard__back-wrap is-hidden" id="casino-finder-back-wrap"></div>

    <div class="casino-finder-wizard__body" id="casino-finder-body">
        <?php foreach ($steps as $index => $step) : ?>
            <div
                class="casino-finder-step<?php echo $index === 0 ? ' casino-finder-step--active' : ''; ?>"
                data-step-index="<?php echo (int) $index; ?>"
                data-step-key="<?php echo esc_attr($step['key']); ?>"
            >
                <h3 class="casino-finder-step__question">
                    <?php echo esc_html($step['question']); ?>
                </h3>
                <p class="casino-finder-step__subtitle">
                    <?php esc_html_e('Choose from the following options:', 'casino-finder'); ?>
                </p>
                <div class="casino-finder-step__options">
                    <?php foreach ($step['options'] as $option) : ?>
                        <button
                            type="button"
                            class="casino-finder-option"
                            data-step-key="<?php echo esc_attr($step['key']); ?>"
                            data-value="<?php echo esc_attr($option['slug']); ?>"
                        >
                            <?php if ($option['image']) : ?>
                                <img
                                    class="casino-finder-option__img"
                                    src="<?php echo esc_url($option['image']); ?>"
                                    alt="<?php echo esc_attr($option['name']); ?>"
                                />
                            <?php endif; ?>
                            <span class="casino-finder-option__label">
                                <?php echo esc_html($option['name']); ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="casino-finder-wizard__results is-hidden" id="casino-finder-results"></div>
    <div class="casino-finder-wizard__load-more is-hidden" id="casino-finder-load-more-wrap">
        <button type="button" class="casino-finder-wizard__btn casino-finder-wizard__btn--load-more" id="casino-finder-load-more">
            <?php esc_html_e('Load more results', 'casino-finder'); ?>
        </button>
    </div>
</div>

