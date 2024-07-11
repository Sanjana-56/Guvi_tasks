$(document).ready(function() {
    function validatePassword(password) {
        var passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/;
        return passwordRegex.test(password);
    }
    $('#username').on('blur', function() {
        var username = $(this).val();
        if (username.length > 0) {
            $.ajax({
                url: "http://localhost/task1/php/register.php",
                type: 'POST',
                data: { checkUsername: username },
                success: function(response) {
                    var res = JSON.parse(response);
                    if (!res.success) {
                        $('#username-error').text('Username not available');
                    } else {
                        $('#username-error').text('');
                    }
                }
            });
        }
    });
    $('#email').on('blur', function() {
        var email = $(this).val();
        if (email.length > 0) {
            $.ajax({
                url: "http://localhost/task1/php/register.php",
                type: 'POST',
                data: { checkEmail: email },
                success: function(response) {
                    var res = JSON.parse(response);
                    if (!res.success) {
                        $('#email-error').text('Email is already registered');
                    } else {
                        $('#email-error').text('');
                    }
                }
            });
        }
    });
    $('#signup-form').on('submit', function(e) {
        e.preventDefault();
        var name = $('input[name=username]').val();
        var password = $('input[name=pass]').val();
        var email = $('input[name=email]').val();
        var passwordError = '';
        if (!validatePassword(password)) {
            passwordError = 'Password must be at least 8 characters long, contain an uppercase letter, a number, and a special character.';
            $('#pass-error').text(passwordError);
            return;
        } else {
            $('#pass-error').text('');
        }
        var formData = { name: name, password: password, email: email };
        $.ajax({
            url: "http://localhost/task1/php/register.php",
            type: 'POST',
            data: formData,
            success: function(response) {
                var res = JSON.parse(response);
                if (res.success == true) {
                    window.location.href = 'http://localhost/task1/login.html';
                } else {
                    $('#error-message').text(res.message).removeClass('d-none');
                }
            }
        });
    });
});