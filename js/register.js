$(document).ready(function() {
    // Check if user is already logged in
    if (localStorage.getItem('userToken')) {
        window.location.href = 'profile.html';
    }

    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        const username = $('#username').val();
        const email = $('#email').val();
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();

        // Basic validation
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }

        // Send registration request
        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            data: {
                username: username,
                email: email,
                password: password
            },
            dataType: 'json',  // Expect JSON response
            success: function(data) {  // No need to parse response
                if (data.success) {
                    alert('Registration successful! Please login.');
                    window.location.href = 'login.html';
                } else {
                    alert(data.message || 'Registration failed!');
                }
            },
            error: function(xhr, status, error) {
                console.error('Registration error:', error);
                alert('An error occurred during registration. Please try again.');
            }
        });
    });
}); 