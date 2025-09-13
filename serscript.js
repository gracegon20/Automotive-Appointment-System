document.addEventListener('DOMContentLoaded', function() {
    // Card interaction functionality
    const serviceCards = document.querySelectorAll('.service-card');
    let currentlyExpandedCard = null;

    serviceCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't toggle if clicking on book button or its children
            if (!e.target.closest('.book-btn')) {
                // If clicking the currently expanded card, collapse it
                if (this === currentlyExpandedCard) {
                    this.classList.remove('expanded');
                    currentlyExpandedCard = null;
                } 
                // Otherwise collapse current and expand new one
                else {
                    if (currentlyExpandedCard) {
                        currentlyExpandedCard.classList.remove('expanded');
                    }
                    this.classList.add('expanded');
                    currentlyExpandedCard = this;
                }
            }
        });
    });

    // Initialize with all services visible
    serviceCards.forEach(card => {
        card.style.display = 'block';
    });

    // Set up back navigation
    setupBackNavigation();
});

function setupBackNavigation() {
    const backButton = document.querySelector('#back-to-services');
    if (!backButton) return;

    backButton.addEventListener('click', function() {
        // Hide search results and show services page
        const searchResults = document.querySelector('.search-results');
        const servicesPage = document.querySelector('.services-grid');
        
        if (searchResults) searchResults.style.display = 'none';
        if (servicesPage) servicesPage.style.display = 'grid';
    });
}
