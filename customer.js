document.addEventListener("DOMContentLoaded", () => {
    // Get references to modal elements
    const addAppointmentBtn = document.getElementById("addAppointmentBtn");
    const appointmentModal = document.getElementById("appointmentModal");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const appointmentForm = document.getElementById("appointmentForm");
    const appointmentTableContainer = document.getElementById("appointmentTableContainer");
    const appointmentTableBody = document.getElementById("appointmentTableBody");

    // Open modal when "Add Appointment" button is clicked
    if (addAppointmentBtn && appointmentModal) {
        addAppointmentBtn.addEventListener("click", () => {
            appointmentModal.style.display = "block";
        });
    }

    // Close modal when close button is clicked
    if (closeModalBtn && appointmentModal) {
        closeModalBtn.addEventListener("click", () => {
            appointmentModal.style.display = "none";
        });
    }

    // Close modal when clicking outside the modal content
    window.addEventListener("click", (event) => {
        if (event.target === appointmentModal) {
            appointmentModal.style.display = "none";
        }
    });

    // Handle form submission
    if (appointmentForm) {
        appointmentForm.addEventListener("submit", (event) => {
        });

    }

    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.content-section');

    navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const targetSection = link.getAttribute('data-section');

            // Hide all sections
            sections.forEach(section => {
                section.style.display = 'none';
            });

            // Show the selected section
            const selectedSection = document.getElementById(targetSection);
            if (selectedSection) {
                selectedSection.style.display = 'block';
            }
        });
    });

    // Initially hide all sections except the first one
    sections.forEach((section, index) => {
        section.style.display = index === 0 ? 'block' : 'none';
    });

    // Get references to vehicle modal elements
    const addVehicleBtn = document.getElementById("addVehicleBtn");
    const vehicleModal = document.getElementById("vehicleModal");
    const closeVehicleModalBtn = document.getElementById("closeVehicleModalBtn");

    // Open vehicle modal when "Add Vehicle" button is clicked
    if (addVehicleBtn && vehicleModal) {
        addVehicleBtn.addEventListener("click", () => {
            vehicleModal.style.display = "block";
        });
    }

    // Close vehicle modal when close button is clicked
    if (closeVehicleModalBtn && vehicleModal) {
        closeVehicleModalBtn.addEventListener("click", () => {
            vehicleModal.style.display = "none";
        });
    }

    // Close vehicle modal when clicking outside the modal content
    window.addEventListener("click", (event) => {
        if (event.target === vehicleModal) {
            vehicleModal.style.display = "none";
        }
    });

    // const logoutBtn = document.getElementById("logout-btn");

    // if (logoutBtn) {
    //     logoutBtn.addEventListener("click", (e) => {
    //         e.preventDefault();
    //         alert("You have successfully logged out.");
    //         window.location.href = 'home.php';
    //     });
    // }
});

document.getElementById('profileManagementLink').addEventListener('click', function (event) {
    event.preventDefault();
    openProfileModal();
});

// Function to open the profile modal
function openProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) {
        modal.style.display = 'block';
    } else {
        console.error('Profile modal not found!');
    }
}

// Close Profile Modal
document.getElementById('closeProfileModalBtn').addEventListener('click', function () {
    document.getElementById('profileModal').style.display = 'none';
});

// Ensure the modal can also close when clicking outside the modal content
window.addEventListener('click', function (event) {
    const profileModal = document.getElementById('profileModal');
    if (event.target === profileModal) {
        profileModal.style.display = 'none';
    }
});

