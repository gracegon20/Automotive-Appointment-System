document.addEventListener('DOMContentLoaded', function() {
    console.log("Customer Dashboard initialized");
    
    // Initialize dashboard with mock data
    initDashboard();
    
    // Set up service selection
    setupServiceSelection();
    
    // Set up form validation
    setupBookingForm();
    
    // Set up back navigation
    setupBackNavigation();
});

function initDashboard() {
    // Load active jobs
    const jobs = fetchJobStatus();
    renderActiveJobs(jobs.activeJobs);
    
    // Set up auto-refresh every 30 seconds
    setInterval(() => {
        const updatedJobs = fetchJobStatus();
        renderActiveJobs(updatedJobs.activeJobs);
    }, 30000);
}

function renderActiveJobs(jobs) {
    const jobsContainer = document.querySelector('.current-jobs');
    if (!jobsContainer) return;
    
    // Clear existing jobs
    jobsContainer.innerHTML = '<h3>Active Repair Jobs</h3>';
    
    if (jobs.length === 0) {
        jobsContainer.innerHTML += '<p class="no-jobs">No active repair jobs</p>';
        return;
    }
    
    jobs.forEach(job => {
        const jobCard = document.createElement('div');
        jobCard.className = 'status-card';
        jobCard.innerHTML = `
            <div style="flex-grow: 1">
                <h4>${job.service}</h4>
                <p>Vehicle: ${job.vehicle}</p>
                <p class="status-badge ${job.status.toLowerCase().replace(' ', '-')}">
                    Status: ${job.status}
                </p>
            </div>
            <div class="payment-status">
                $${job.amount.toFixed(2)} (${job.paid ? 'Paid' : 'Pending'})
            </div>
        `;
        jobsContainer.appendChild(jobCard);
    });
}

function setupServiceSelection() {
    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove active class from all cards
            serviceCards.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked card
            this.classList.add('active');
            
            // Update form with selected service
            const serviceName = this.querySelector('h4').textContent;
            document.querySelector('#selected-service').value = serviceName;
        });
    });
}

function setupBookingForm() {
    const form = document.getElementById('bookingForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        const service = form.querySelector('#selected-service').value;
        const vehicle = form.querySelector('input[type="text"]').value;
        const date = form.querySelector('input[type="date"]').value;
        
        if (!service || !vehicle || !date) {
            showAlert('Please fill all fields', 'error');
            return;
        }
        
        // Simulate booking submission
        showAlert('Service booked successfully!', 'success');
        form.reset();
        
        // Update active jobs list
        const updatedJobs = fetchJobStatus();
        renderActiveJobs(updatedJobs.activeJobs);
    });
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert ${type}`;
    alert.textContent = message;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.classList.add('fade-out');
        setTimeout(() => alert.remove(), 500);
    }, 3000);
}

// Enhanced mock API with more data
function fetchJobStatus() {
    return {
        activeJobs: [
            {
                service: "Brake System Repair",
                vehicle: "Toyota Camry 2020",
                status: "In Progress",
                amount: 450,
                paid: true
            },
            {
                service: "Oil Change",
                vehicle: "Honda Civic 2018",
                status: "Scheduled",
                amount: 80,
                paid: false
            }
        ]
    };
}

function showHistory() {
    // Simulate loading history
    showAlert('Loading service history...', 'info');
    setTimeout(() => {
        // In a real app, this would fetch and display history
        console.log('Service history loaded');
    }, 1000);
}

function showDocuments() {
    // Simulate loading documents
    showAlert('Loading vehicle documents...', 'info');
    setTimeout(() => {
        // In a real app, this would fetch and display documents
        console.log('Vehicle documents loaded');
    }, 1000);
}

function makePayment() {
    // Simulate payment process
    showAlert('Redirecting to payment gateway...', 'info');
    setTimeout(() => {
        // In a real app, this would open payment modal
        console.log('Payment processed');
    }, 1500);
}

function setupBackNavigation() {
    const backButton = document.querySelector('#back-to-services');
    if (!backButton) return;

    backButton.addEventListener('click', function() {
        // Hide search results and show services page
        const searchResults = document.querySelector('.search-results');
        const servicesPage = document.querySelector('.services-page');
        
        if (searchResults) searchResults.style.display = 'none';
        if (servicesPage) servicesPage.style.display = 'block';
    });
}
