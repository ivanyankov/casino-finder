(() => {
    'use strict';

    const config           = window.casinoFinderWizard || {};
    const translations     = config.i18n || {};
    const resultsPerPage   = config.perPage || 10;
    const isLoadMoreEnabled = Boolean(config.enableLoadMore);

    const elements = {
        wizard:       document.getElementById('casino-finder-wizard'),
        progress:     document.getElementById('casino-finder-progress'),
        toolbar:      document.getElementById('casino-finder-toolbar'),
        backWrap:     document.getElementById('casino-finder-back-wrap'),
        body:         document.getElementById('casino-finder-body'),
        results:      document.getElementById('casino-finder-results'),
        startOver:    document.getElementById('casino-finder-start-over'),
        loadMoreWrap: document.getElementById('casino-finder-load-more-wrap'),
        loadMore:     document.getElementById('casino-finder-load-more'),
    };

    if (!elements.wizard) {
        return;
    }

    let selections = {};
    let currentStep = 0;
    let currentPage = 1;
    let hasMorePages = false;

    const stepElements = Array.from(document.querySelectorAll('.casino-finder-step'));
    const progressStepElements = elements.progress
        ? Array.from(elements.progress.querySelectorAll('.casino-finder-progress__step'))
        : [];

    const initialiseWizard = () => {
        stepElements.forEach((stepElement, index) => {
            const optionButtons = stepElement.querySelectorAll('.casino-finder-option');
            optionButtons.forEach(button => {
                button.addEventListener('click', handleOptionClick);
            });

            const stepKey = stepElement.getAttribute('data-step-key');
            stepElement.dataset.stepKey = stepKey || '';

            if (index === 0) {
                stepElement.style.display = 'block';
            } else {
                stepElement.style.display = 'none';
            }
        });

        updateProgressBar();

        if (elements.startOver) {
            elements.startOver.addEventListener('click', () => {
                selections = {};
                currentStep = 0;
                currentPage = 1;
                elements.results.style.display = 'none';
                elements.loadMoreWrap.style.display = 'none';
                elements.body.style.display = 'block';

                stepElements.forEach((stepElement, index) => {
                    stepElement.style.display = index === 0 ? 'block' : 'none';
                });

                progressStepElements.forEach(stepElement => {
                    const originalLabel = stepElement.getAttribute('data-original-label');
                    const labelElement = stepElement.querySelector('.casino-finder-progress__label');
                    if (originalLabel && labelElement) {
                        labelElement.textContent = originalLabel;
                    }
                    stepElement.classList.remove('is-active', 'is-completed');
                });

                updateProgressBar();
            });
        }

        if (isLoadMoreEnabled && elements.loadMore) {
            elements.loadMore.addEventListener('click', () => {
                if (!hasMorePages) {
                    return;
                }

                currentPage += 1;
                fetchResults(true);
            });
        }
    };

    const updateProgressBar = () => {
        progressStepElements.forEach((stepElement, index) => {
            stepElement.classList.remove('is-active', 'is-completed');

            if (index < currentStep) {
                stepElement.classList.add('is-completed');

                const stepElementDom = stepElements[index];
                const stepKey = stepElementDom ? stepElementDom.dataset.stepKey : '';
                const selectedSlug = selections[stepKey || ''];

                if (selectedSlug && stepElementDom) {
                    const optionButton = stepElementDom.querySelector(
                        '.casino-finder-option[data-value="' + selectedSlug + '"]'
                    );
                    const optionLabel = optionButton
                        ? optionButton.querySelector('.casino-finder-option__label')
                        : null;

                    const labelElement = stepElement.querySelector('.casino-finder-progress__label');
                    if (labelElement && optionLabel) {
                        if (!stepElement.getAttribute('data-original-label')) {
                            stepElement.setAttribute('data-original-label', labelElement.textContent || '');
                        }
                        labelElement.textContent = optionLabel.textContent || '';
                    }
                }
            } else if (index === currentStep) {
                stepElement.classList.add('is-active');
            }
        });
    };

    const handleOptionClick = (event) => {
        const button = event.currentTarget;
        const stepKey = button.getAttribute('data-step-key');
        const value = button.getAttribute('data-value');

        selections[stepKey] = value || '';

        if (currentStep < stepElements.length - 1) {
            stepElements[currentStep].style.display = 'none';
            currentStep += 1;
            stepElements[currentStep].style.display = 'block';
            updateProgressBar();
            return;
        }

        fetchResults(false);
    };

    const fetchResults = async (isLoadMore) => {
        currentStep = stepElements.length;
        updateProgressBar();

        elements.body.style.display = 'none';
        elements.toolbar.style.display = 'block';
        elements.backWrap.style.display = 'block';
        elements.results.style.display = 'grid';

        if (!isLoadMore) {
            currentPage = 1;
            elements.results.innerHTML =
                '<div class="casino-finder-loading"><p>' + (translations.loading || '') + '</p></div>';
        }

        const searchParams = new URLSearchParams();
        Object.keys(selections).forEach(key => {
            searchParams.append(key, selections[key]);
        });
        searchParams.append('page', String(currentPage));
        searchParams.append('per_page', String(resultsPerPage));

        try {
            const response = await fetch(config.restUrl + '?' + searchParams.toString(), {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': config.nonce,
                },
            });

            if (!response.ok) {
                throw new Error('Request failed with status ' + response.status);
            }

            const data = await response.json();
            renderResults(data.html || '', Boolean(isLoadMore));
            hasMorePages = Boolean(data.has_more);
            elements.loadMoreWrap.style.display =
                isLoadMoreEnabled && hasMorePages ? 'block' : 'none';
        } catch (error) {
            elements.results.innerHTML =
                '<p class="casino-finder-error">' + (translations.error || '') + '</p>';
        }
    };

    const renderResults = (html, append) => {
        if (!append && !html) {
            elements.results.innerHTML =
                '<p class="casino-finder-no-results">' + (translations.noResults || '') + '</p>';
            return;
        }

        elements.results.innerHTML = append
            ? elements.results.innerHTML + html
            : html;
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialiseWizard);
    } else {
        initialiseWizard();
    }
})();