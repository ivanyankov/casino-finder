const elementSelectors = {
    wizard: '#casino-finder-wizard',
    progress: '#casino-finder-progress',
    toolbar: '#casino-finder-toolbar',
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
  
    const elements = Object.fromEntries(
      Object.entries(elementSelectors).map(([key, selector]) => [key, $(selector)]),
    )
  
    if (!elements.wizard) return
  
    let selections = {}
    let currentStep = 0
    let currentPage = 1
    let hasMorePages = false
  
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
  
      addClass(elements.results, 'is-hidden')
      addClass(elements.loadMoreWrap, 'is-hidden')
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
  
      // Event delegation for all option buttons
      elements.wizard.addEventListener('click', (event) => {
        const button = event.target.closest('.casino-finder-option')
        if (!button || !elements.wizard.contains(button)) return
        handleOptionClick(button)
      })
  
      updateProgressBar()
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
      progressStepElements.forEach((stepElement, index) => {
        stepElement.classList.remove('is-active', 'is-completed')
  
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
            const labelElement = $('.casino-finder-progress__label', stepElement)
            if (labelElement && optionLabel) {
              if (!stepElement.getAttribute('data-original-label')) {
                stepElement.setAttribute('data-original-label', labelElement.textContent || '')
              }
              labelElement.textContent = optionLabel.textContent || ''
            }
          }
        } else if (index === currentStep) stepElement.classList.add('is-active')
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
  
    const fetchResults = async (isLoadMore) => {
      currentStep = stepElements.length
      updateProgressBar()
  
      addClass(elements.body, 'is-hidden')
      removeClass(elements.toolbar, 'is-hidden')
      removeClass(elements.backWrap, 'is-hidden')
      removeClass(elements.results, 'is-hidden')
  
      if (!isLoadMore) {
        currentPage = 1
        elements.results.innerHTML = `<div class="casino-finder-loading"><p>${
          translations.loading || ''
        }</p></div>`
      }
  
      try {
        const response = await fetch(`${config.restUrl}?${buildSearchParams().toString()}`, {
          method: 'GET',
          headers: { 'X-WP-Nonce': config.nonce },
        })
  
        if (!response.ok) throw new Error(`Request failed with status ${response.status}`)
        const data = await response.json()
        renderResults(data.html || '', Boolean(isLoadMore))
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
        elements.results.innerHTML = `<p class="casino-finder-no-results">${
          translations.noResults || ''
        }</p>`
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