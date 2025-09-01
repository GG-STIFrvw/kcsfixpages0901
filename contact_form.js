document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('name');
        const email = document.getElementById('email');
        const phone = document.getElementById('phone');
        const message = document.getElementById('message');
        const submitBtn = this.querySelector('button[type="submit"]');
        const submitText = submitBtn.querySelector('.submit-text');
        const loader = submitBtn.querySelector('.loader');
        const successMessage = this.querySelector('.success-message');
        
        let isValid = true;
        
        // Reset previous errors
        document.querySelectorAll('.error-message').forEach(msg => msg.classList.add('hidden'));
        document.querySelectorAll('.form-input').forEach(input => input.classList.remove('form-error'));
        
        // Validate name
        if (!name.value.trim()) {
            showError(name, 'Please enter your name');
            isValid = false;
        }
        
        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim() || !emailRegex.test(email.value)) {
            showError(email, 'Please enter a valid email');
            isValid = false;
        }
        
        // Validate phone
        const phoneRegex = /^[\d\s\-+\(\)]+$/;
        if (!phone.value.trim() || !phoneRegex.test(phone.value)) {
            showError(phone, 'Please enter a valid phone number');
            isValid = false;
        }
        
        // Validate message
        if (!message.value.trim()) {
            showError(message, 'Please enter your message');
            isValid = false;
        }
        
        if (isValid) {
            // Show loading state
            submitText.textContent = 'Sending...';
            loader.classList.remove('hidden');
            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('name', name.value);
            formData.append('email', email.value);
            formData.append('phone', phone.value);
            formData.append('message', message.value);

            fetch('send_contact_email.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Reset form
                    document.getElementById('contactForm').reset();
                    
                    // Show success message
                    successMessage.classList.remove('hidden');
                    
                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        successMessage.classList.add('hidden');
                    }, 5000);
                } else {
                    alert('An error occurred: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again later.');
            })
            .finally(() => {
                // Reset button
                submitText.textContent = 'Send Message';
                loader.classList.add('hidden');
                submitBtn.disabled = false;
            });
        }
    });

    function showError(input, message) {
        input.classList.add('form-error');
        const errorMsg = input.parentNode.querySelector('.error-message');
        errorMsg.textContent = message;
        errorMsg.classList.remove('hidden');
    }
});