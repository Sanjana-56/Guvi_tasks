$(document).ready(function() {
    $('#email').on('blur', function() {
        var email = $(this).val();
        checkEmail(email);
    });

    $('#pass').on('blur', function() {
        var email = $('#email').val();
        var password = $(this).val();
        if (email !== '') {
            checkPassword(email, password);
        }
    });
});

function checkEmail(email) {
    if (email === '') {
        $('#email-error').text('Email is required');
        return;
    }

    $.ajax({
        url: "http://localhost/task1/php/login.php",
        type: 'POST',
        data: { action: 'check_email', email: email },
        success: function(response) {
            var res = JSON.parse(response);
            if (!res.success) {
                $('#email-error').text(res.message);
            } else {
                $('#email-error').text('');
            }
        }
    });
}

function checkPassword(email, password) {
    if (password === '') {
        $('#pass-error').text('Password is required');
        return;
    }

    $.ajax({
        url: "http://localhost/task1/php/login.php",
        type: 'POST',
        data: { action: 'check_password', email: email, password: password },
        success: function(response) {
            var res = JSON.parse(response);
            if (!res.success) {
                $('#pass-error').text(res.message);
            } else {
                $('#pass-error').text('');
            }
        }
    });
}

function submitForm() {
    var email = $('input[name=email]').val();
    var password = $('input[name=pass]').val();
    
    var formData = { email: email, password: password };

    $.ajax({
        url: "http://localhost/task1/php/login.php",
        type: 'POST',
        data: formData,
        success: function(response) {
            var res = JSON.parse(response);

            if (res.success == true) {
                localStorage.setItem('userName', res.user.user);
                window.location.href = 'http://localhost/task1/index.html';
            } else {
                if (res.errorField === 'email') {
                    $('#email-error').text(res.message);
                    $('#pass-error').text('');
                } else if (res.errorField === 'password') {
                    $('#pass-error').text(res.message);
                    $('#email-error').text('');
                } else {
                    $('#email-error').text('');
                    $('#pass-error').text('');
                    alert(res.message);
                }
            }
        }
    });
}
