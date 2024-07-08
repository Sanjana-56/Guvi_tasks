$(document).ready(function(){
    function fetchUserProfile() {
        $.ajax({
            url: 'http://localhost/task1/php/profile.php',
            type: 'GET',
            success: function(response) {
                try {
                    var profile = JSON.parse(response);
                    $('#name').val(profile.name);
                    $('#surname').val(profile.surname);
                    $('#age').val(profile.age);
                    $('#dob').val(profile.dob);
                    $('#mobileNumber').val(profile.mobileNumber);
                    $('#address').val(profile.address);
                    $('#email').val(profile.email).attr('readonly', true); // Populate email field (disabled)
                    $('#country').val(profile.country);
                    $('#state').val(profile.state);
                } catch (error) {
                    console.error('Error parsing profile response:', error);
                }
            },
            error: function() {
                $('#responseMessage').text('Error fetching profile details.');
            }
        });
    }

    fetchUserProfile();

    function validateMobileNumber() {
        var mobileNumber = $('#mobileNumber').val();
        var mobileError = $('#mobileError');

        if (mobileNumber && !/^\d{10}$/.test(mobileNumber)) {
            mobileError.text('Provide a valid mobile number with 10 digits.');
            return false;
        } else {
            mobileError.text('');
            return true;
        }
    }

    function validateDOB() {
        var dob = $('#dob').val();
        var dobError = $('#dobError');

        if (dob && !/^\d{4}-\d{2}-\d{2}$/.test(dob)) {
            dobError.text('Provide a valid date of birth in YYYY-MM-DD format.');
            return false;
        } else {
            dobError.text('');
            return true;
        }
    }

    $('#mobileNumber').blur(validateMobileNumber);
    $('#dob').blur(validateDOB);

    $('#saveProfile').click(function(){
        var isMobileValid = validateMobileNumber();
        var isDobValid = validateDOB();

        if (!isMobileValid || !isDobValid) {
            return;
        }

        var formData = $('#profileForm').serialize();

        $.ajax({
            url: 'http://localhost/task1/php/profile.php',
            type: 'POST',
            data: formData,
            success: function(response){
                $('#responseMessage').text(response);
                $('#responseMessage').css('color', response.trim() === "Profile updated successfully." ? 'green' : 'red');
                if (response.trim() === "Profile updated successfully.") {
                    fetchUserProfile();
                }
            },
            error: function(){
                $('#responseMessage').text('Error saving profile.');
                $('#responseMessage').css('color', 'red');
            }
        });
    });
});
