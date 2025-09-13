function togglePassword() {
    const password = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-password i');
    if (password.type === 'password') {
        password.type = 'text';
        toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        password.type = 'password';
        toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Function to show the modal
function showSuccessModal() {
    document.getElementById('successModal').style.display = 'block';
}

// Function to redirect to the dashboard
function redirectToDashboard() {
    window.location.href = 'customer.php';
}

// Handle form submission with AJAX
document.getElementById('signupForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this); // Collect form data

    // Send data to the server using fetch
    fetch('signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('Registration successful')) {
            showSuccessModal(); // Show the modal on success
        } else {
            alert(data); // Show error message from the server
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request.');
    });
});