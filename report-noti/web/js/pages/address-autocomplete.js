/**
 * Custom Address Autocomplete Component
 * Replaces Google Maps Places Autocomplete with our proxy service
 */
class AddressAutocomplete {
  constructor(inputElement, options = {}) {
    this.input = inputElement;
    this.options = {
      minLength: 3,
      delay: 300,
      maxResults: 5,
      ...options
    };

    this.suggestionsContainer = null;
    this.currentRequest = null;
    this.selectedIndex = -1;

    this.init();
  }

  init() {
    this.createSuggestionsContainer();
    this.bindEvents();
  }

  createSuggestionsContainer() {
    this.suggestionsContainer = document.createElement('div');
    this.suggestionsContainer.className = 'address-suggestions';
    this.suggestionsContainer.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        `;

    this.input.parentNode.style.position = 'relative';
    this.input.parentNode.appendChild(this.suggestionsContainer);
  }

  bindEvents() {
    let debounceTimer;

    this.input.addEventListener('input', (e) => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        this.handleInput(e.target.value);
      }, this.options.delay);
    });

    this.input.addEventListener('keydown', (e) => {
      this.handleKeydown(e);
    });

    this.input.addEventListener('blur', () => {
      setTimeout(() => {
        this.hideSuggestions();
      }, 200);
    });

    this.input.addEventListener('focus', () => {
      if (this.input.value.length >= this.options.minLength) {
        this.handleInput(this.input.value);
      }
    });
  }

  async handleInput(value) {
    if (value.length < this.options.minLength) {
      this.hideSuggestions();
      return;
    }

    try {
      const suggestions = await this.getAddressSuggestions(value);
      this.showSuggestions(suggestions);
    } catch (error) {
      console.error('Error getting address suggestions:', error);
      this.hideSuggestions();
    }
  }

  async getAddressSuggestions(query) {
    // Cancel previous request
    if (this.currentRequest) {
      this.currentRequest.abort();
    }

    // Create new request
    const controller = new AbortController();
    this.currentRequest = controller;

    try {
      const response = await fetch(`/api/google-map/google-maps-proxy?action=geocode&address=${encodeURIComponent(query)}`, {
        signal: controller.signal
      });

      const data = await response.json();

      if (data.error) {
        throw new Error(data.message);
      }

      return this.parseGeocodingResults(data.results || []);
    } catch (error) {
      if (error.name === 'AbortError') {
        return [];
      }
      throw error;
    }
  }

  parseGeocodingResults(results) {
    return results.slice(0, this.options.maxResults).map(result => ({
      address: result.formatted_address,
      placeId: result.place_id,
      location: result.geometry?.location
    }));
  }

  showSuggestions(suggestions) {
    if (suggestions.length === 0) {
      this.hideSuggestions();
      return;
    }

    this.suggestionsContainer.innerHTML = '';
    this.selectedIndex = -1;

    suggestions.forEach((suggestion, index) => {
      const item = document.createElement('div');
      item.className = 'suggestion-item';
      item.style.cssText = `
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
            `;
      item.textContent = suggestion.address;

      item.addEventListener('mouseenter', () => {
        this.selectedIndex = index;
        this.updateSelection();
      });

      item.addEventListener('click', () => {
        this.selectSuggestion(suggestion);
      });

      this.suggestionsContainer.appendChild(item);
    });

    this.suggestionsContainer.style.display = 'block';
  }

  hideSuggestions() {
    this.suggestionsContainer.style.display = 'none';
    this.selectedIndex = -1;
  }

  updateSelection() {
    const items = this.suggestionsContainer.querySelectorAll('.suggestion-item');
    items.forEach((item, index) => {
      if (index === this.selectedIndex) {
        item.style.backgroundColor = '#f0f0f0';
      } else {
        item.style.backgroundColor = 'white';
      }
    });
  }

  handleKeydown(e) {
    const items = this.suggestionsContainer.querySelectorAll('.suggestion-item');

    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
        this.updateSelection();
        break;

      case 'ArrowUp':
        e.preventDefault();
        this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
        this.updateSelection();
        break;

      case 'Enter':
        e.preventDefault();
        if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
          const suggestion = {
            address: items[this.selectedIndex].textContent,
            placeId: items[this.selectedIndex].dataset.placeId
          };
          this.selectSuggestion(suggestion);
        }
        break;

      case 'Escape':
        this.hideSuggestions();
        break;
    }
  }

  selectSuggestion(suggestion) {
    this.input.value = suggestion.address;
    this.hideSuggestions();

    // Trigger change event
    const event = new Event('change', { bubbles: true });
    this.input.dispatchEvent(event);

    // Store additional data if needed
    if (suggestion.location) {
      this.input.dataset.lat = suggestion.location.lat;
      this.input.dataset.lng = suggestion.location.lng;
    }
    if (suggestion.placeId) {
      this.input.dataset.placeId = suggestion.placeId;
    }
  }
}

// Initialize autocomplete for elements with data-autocomplete attribute
document.addEventListener('DOMContentLoaded', () => {
  const autocompleteInputs = document.querySelectorAll('[data-autocomplete="address"]');
  autocompleteInputs.forEach(input => {
    new AddressAutocomplete(input);
  });
});
