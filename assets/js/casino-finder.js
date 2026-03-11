const elementSelectors = {
    wizard: '#casino-finder-wizard',
    progress: '#casino-finder-progress',
    toolbar: '#casino-finder-toolbar',
    summary: '#casino-finder-summary',
    summaryHeadline: '#casino-finder-summary-headline',
    summarySubline: '#casino-finder-summary-subline',
    summaryTags: '#casino-finder-summary-tags',
    backWrap: '#casino-finder-back-wrap',
    body: '#casino-finder-body',
    results: '#casino-finder-results',
    startOver: '#casino-finder-start-over',
    loadMoreWrap: '#casino-finder-load-more-wrap',
    loadMore: '#casino-finder-load-more',
  }
  
  const $ = (selector, scope = document, all = false) =>
    all ? [...scope.querySelectorAll(selector)] : scope.querySelector(selector)
  
  ;(() => {
    'use strict'
  
    const config = window.casinoFinderWizard || {}
    const translations = config.i18n || {}
    const resultsPerPage = config.perPage || 10
    const isLoadMoreEnabled = Boolean(config.enableLoadMore)
    const totalCasinos = Number(config.totalCasinos || 0)
  
    const elements = Object.fromEntries(
      Object.entries(elementSelectors).map(([key, selector]) => [key, $(selector)]),
    )
  
    if (!elements.wizard) return
  
    let selections = {}
    let currentStep = 0
    let currentPage = 1
    let hasMorePages = false
    let totalMatches = 0
  
    const stepElements = $('.casino-finder-step', document, true)
    const progressStepElements = elements.progress
      ? $('.casino-finder-progress__step', elements.progress, true)
      : []
  
    const addClass = (element, className) => {
      if (element) element.classList.add(className)
    }

    const removeClass = (element, className) => {
      if (element) element.classList.remove(className)
    }

    const resetWizard = () => {
      selections = {}
      currentStep = 0
      currentPage = 1
      totalMatches = 0
  
      addClass(elements.results, 'is-hidden')
      addClass(elements.loadMoreWrap, 'is-hidden')
      addClass(elements.summary, 'is-hidden')
      if (elements.summaryHeadline) elements.summaryHeadline.textContent = ''
      if (elements.summarySubline) elements.summarySubline.textContent = ''
      if (elements.summaryTags) elements.summaryTags.innerHTML = ''
      removeClass(elements.body, 'is-hidden')
  
      stepElements.forEach((stepElement, index) => {
        if (index === 0) {
          addClass(stepElement, 'casino-finder-step--active')
        } else {
          removeClass(stepElement, 'casino-finder-step--active')
        }
      })
  
      progressStepElements.forEach((stepElement) => {
        const originalLabel = stepElement.getAttribute('data-original-label')
        const labelElement = $('.casino-finder-progress__label', stepElement)
        if (originalLabel && labelElement) labelElement.textContent = originalLabel
        stepElement.classList.remove('is-active', 'is-completed')
      })
  
      updateProgressBar()
    }
  
    const buildSearchParams = () =>
      new URLSearchParams({
        ...selections,
        page: currentPage,
        per_page: resultsPerPage,
      })
  
    const initialiseWizard = () => {
      stepElements.forEach((stepElement, index) => {
        const stepKey = stepElement.dataset.stepKey || stepElement.getAttribute('data-step-key')
        stepElement.dataset.stepKey = stepKey || ''
        if (index === 0) {
          addClass(stepElement, 'casino-finder-step--active')
        } else {
          removeClass(stepElement, 'casino-finder-step--active')
        }
      })
  
      elements.wizard.addEventListener('click', (event) => {
        const button = event.target.closest('.casino-finder-option')
        if (!button || !elements.wizard.contains(button)) return
        handleOptionClick(button)
      })
  
      updateProgressBar()

      if (elements.results) {
        elements.results.addEventListener('click', (event) => {
          const button = event.target.closest('.casino-finder-card__code-copy')
          if (!button || !elements.results.contains(button)) return

          const codeWrapper = button.closest('.casino-finder-card__code')
          if (!codeWrapper) return

          const codeElement = codeWrapper.querySelector('.casino-finder-card__code-text')
          if (!codeElement || !codeElement.textContent) return

          const code = codeElement.textContent.trim()
          if (!code) return

          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).catch(() => {})
          }
        })
      }
      if (elements.startOver) elements.startOver.addEventListener('click', resetWizard)
      if (isLoadMoreEnabled && elements.loadMore) {
        elements.loadMore.addEventListener('click', () => {
          if (!hasMorePages) return
          currentPage += 1
          fetchResults(true)
        })
      }
    }
  
    const updateProgressBar = () => {
      const isResultsView = currentStep >= stepElements.length

      progressStepElements.forEach((stepElement, index) => {
        stepElement.classList.remove('is-active', 'is-completed')

        const labelElement = $('.casino-finder-progress__label', stepElement)

        if (isResultsView) {
          stepElement.classList.add('is-completed')
          const originalLabel = stepElement.getAttribute('data-original-label')
          if (originalLabel && labelElement) labelElement.textContent = originalLabel
          return
        }

        if (index < currentStep) {
          stepElement.classList.add('is-completed')
          const stepElementDom = stepElements[index]
          const stepKey = stepElementDom?.dataset.stepKey
          const selectedSlug = selections[stepKey || '']
          if (selectedSlug && stepElementDom) {
            const optionButton = $(
              `.casino-finder-option[data-value="${selectedSlug}"]`,
              stepElementDom,
            )
            const optionLabel = optionButton ? $('.casino-finder-option__label', optionButton) : null
            if (labelElement && optionLabel) {
              if (!stepElement.getAttribute('data-original-label')) {
                stepElement.setAttribute('data-original-label', labelElement.textContent || '')
              }
              labelElement.textContent = optionLabel.textContent || ''
            }
          }
        } else if (index === currentStep) {
          stepElement.classList.add('is-active')
        }
      })
    }
  
    const handleOptionClick = (button) => {
      const stepKey = button.dataset.stepKey
      const value = button.dataset.value
      selections[stepKey] = value || ''
  
      if (currentStep < stepElements.length - 1) {
        removeClass(stepElements[currentStep], 'casino-finder-step--active')
        currentStep += 1
        addClass(stepElements[currentStep], 'casino-finder-step--active')
        updateProgressBar()
        return
      }
  
      fetchResults(false)
    }
  
    const buildBestForTags = () => {
      const tags = []

      stepElements.forEach((stepElement) => {
        const stepKey = stepElement.dataset.stepKey
        if (!stepKey) return
        const selectedSlug = selections[stepKey]
        if (!selectedSlug) return

        const optionButton = $(
          `.casino-finder-option[data-value="${selectedSlug}"]`,
          stepElement,
        )
        const optionLabel = optionButton ? $('.casino-finder-option__label', optionButton) : null
        if (optionLabel && optionLabel.textContent) {
          tags.push(optionLabel.textContent.trim())
        }
      })

      return tags
    }

    const updateSummary = () => {
      if (!elements.summary || !elements.results) return
      if (!totalMatches || !translations.summaryTemplate) {
        addClass(elements.summary, 'is-hidden')
        return
      }

      if (elements.summaryHeadline) {
        const template = translations.summaryTemplate
        elements.summaryHeadline.textContent = template
          .replace('%total%', String(totalCasinos))
          .replace('%matched%', String(totalMatches))
      }

      if (elements.summarySubline) {
        elements.summarySubline.textContent = translations.summarySubline || ''
      }

      if (elements.summaryTags) {
        elements.summaryTags.innerHTML = ''
        const tagLabels = buildBestForTags()
        tagLabels.forEach((text) => {
          const chip = document.createElement('span')
          chip.className = 'casino-finder-summary__tag'
          chip.textContent = text
          elements.summaryTags.appendChild(chip)
        })
      }

      removeClass(elements.summary, 'is-hidden')
    }

    const fetchResults = async (isLoadMore) => {
      currentStep = stepElements.length
      updateProgressBar()
  
      addClass(elements.body, 'is-hidden')
      removeClass(elements.toolbar, 'is-hidden')
      removeClass(elements.backWrap, 'is-hidden')
      removeClass(elements.results, 'is-hidden')
  
      if (!isLoadMore) {
        currentPage = 1
        elements.results.innerHTML = `<div class="casino-finder-loading"><p>${translations.loading || ''}</p></div>`
      }
  
      try {
        const response = await fetch(`${config.restUrl}?${buildSearchParams().toString()}`, {
          method: 'GET',
          headers: { 'X-WP-Nonce': config.nonce },
        })
  
        if (!response.ok) throw new Error(`Request failed with status ${response.status}`)
        const data = await response.json()
        totalMatches = Number(data.total_matches || 0)
        renderResults(data.html || '', Boolean(isLoadMore))
        updateSummary()
        hasMorePages = Boolean(data.has_more)
        if (isLoadMoreEnabled && hasMorePages) {
          removeClass(elements.loadMoreWrap, 'is-hidden')
        } else {
          addClass(elements.loadMoreWrap, 'is-hidden')
        }
      } catch (error) {
        elements.results.innerHTML = `<p class="casino-finder-error">${translations.error || ''}</p>`
      }
    }
  
    const renderResults = (html, append) => {
      if (!html && !append) {
        elements.results.innerHTML = `<p class="casino-finder-no-results">${translations.noResults || ''}</p>`
        return
      }
      elements.results.innerHTML = append ? elements.results.innerHTML + html : html
    }
  
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initialiseWizard)
    } else {
      initialiseWizard()
    }
  })();