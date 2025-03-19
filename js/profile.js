$(document).ready(function() {
    // Check if user is logged in
    const token = localStorage.getItem('userToken');
    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    // Load user profile data
    $.ajax({
        url: 'php/profile.php',
        type: 'GET',
        headers: {
            'Authorization': token
        },
        dataType: 'json',  // Expect JSON response
        success: function(data) {  // No need to parse response
            if (data.success) {
                // Fill in the form with user data
                $('#username').val(data.profile.username);
                $('#email').val(data.profile.email);
                $('#age').val(data.profile.age);
                $('#dob').val(data.profile.dob);
                $('#phone').val(data.profile.phone);
                $('#address').val(data.profile.address);
                $('#bio').val(data.profile.bio);
            } else {
                alert(data.message || 'Failed to load profile data!');
                if (data.message === 'Invalid token') {
                    localStorage.clear();
                    window.location.href = 'login.html';
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Profile error:', error);
            console.error('Status:', status);
            console.error('Server response:', xhr.responseText);
            alert('An error occurred while loading profile data.');
        }
    });

    // Handle profile update
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        const profileData = {
            age: $('#age').val(),
            dob: $('#dob').val(),
            phone: $('#phone').val(),
            address: $('#address').val(),
            bio: $('#bio').val()
        };

        $.ajax({
            url: 'php/update_profile.php',
            type: 'POST',
            headers: {
                'Authorization': token
            },
            data: profileData,
            dataType: 'json',  // Expect JSON response
            success: function(data) {  // No need to parse response
                if (data.success) {
                    alert('Profile updated successfully!');
                } else {
                    alert(data.message || 'Failed to update profile!');
                    if (data.message === 'Invalid token') {
                        localStorage.clear();
                        window.location.href = 'login.html';
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Update error:', error);
                console.error('Status:', status);
                console.error('Server response:', xhr.responseText);
                alert('An error occurred while updating profile.');
            }
        });
    });

    // Handle logout
    $('#logoutBtn').on('click', function() {
        $.ajax({
            url: 'php/logout.php',
            type: 'POST',
            headers: {
                'Authorization': token
            },
            dataType: 'json',  // Expect JSON response
            success: function(data) {  // No need to parse response
                if (data.success) {
                    localStorage.clear();
                    window.location.href = 'login.html';
                }
            },
            complete: function() {
                // Clear localStorage and redirect even if the request fails
                localStorage.clear();
                window.location.href = 'login.html';
            }
        });
    });
}); 