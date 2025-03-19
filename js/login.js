$(document).ready(function() {
    // Check if user is already logged in
    if (localStorage.getItem('userToken')) {
        window.location.href = 'profile.html';
    }

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const email = $('#email').val();
        const password = $('#password').val();

        console.log('Attempting login with email:', email);

        // Basic validation
        if (!email || !password) {
            alert('Please enter both email and password');
            return;
        }

        // Send login request
        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            data: {
                email: email,
                password: password
            },
            dataType: 'json',  // Expect JSON response
            beforeSend: function() {
                console.log('Sending login request...');
            },
            success: function(data) {  // No need to parse response
                console.log('Login response:', data);
                if (data.success) {
                    // Store the token in localStorage
                    localStorage.setItem('userToken', data.token);
                    localStorage.setItem('userEmail', email);
                    window.location.href = 'profile.html';
                } else {
                    alert(data.message || 'Login failed!');
                }
            },
            error: function(xhr, status, error) {
                console.error('Login error:', error);
                console.error('Status:', status);
                console.error('Server response:', xhr.responseText);
                try {
                    const response = JSON.parse(xhr.responseText);
                    alert(response.message || 'An error occurred during login. Please try again.');
                } catch (e) {
                    alert('An error occurred during login. Please try again.');
                }
            }
        });
    });
}); 