document.getElementById('profile-form').addEventListener('submit', function(e) {
    e.preventDefault();

    let isValid = true;
    let firstName = this.firstName.value.trim();
    let lastName = this.lastName.value.trim();
    let email = this.user_email.value.trim();
    let password = this.new_password.value.trim();
    let confirmPassword = this.confirm_password.value.trim();

    document.querySelectorAll('.error-message').forEach(el => el.remove());

    if (firstName === '') {
        isValid = false;
        const nameInput = document.querySelector('.first-name');

        const error = document.createElement('p');
        error.className = 'text-danger error-message mt-2 small';
        error.textContent = 'First name is required.';

        nameInput.parentNode.insertBefore(error, nameInput.nextSibling);
    }

    if (lastName === '') {
        isValid = false;
        const nameInput = document.querySelector('.last-name');

        const error = document.createElement('p');
        error.className = 'text-danger error-message mt-2 small';
        error.textContent = 'Last name is required.';

        nameInput.parentNode.insertBefore(error, nameInput.nextSibling);
    }

    if (email === '') {
        isValid = false;
        const emailInput = document.querySelector('.email');

        const error = document.createElement('p');
        error.className = 'text-danger error-message mt-2 small';
        error.textContent = 'Email is required.';

        emailInput.parentNode.insertBefore(error, emailInput.nextSibling);
    }

    if (password) {
        if (password != confirmPassword) {
            isValid = false;
            const passwordInput = document.querySelector('.confirm-password');

            const error = document.createElement('p');
            error.className = 'text-danger error-message mt-2 small';
            error.textContent = 'Confirm password do not match with password.';

            passwordInput.parentNode.insertBefore(error, passwordInput.nextSibling);
        }
    }

    if (!isValid) return;

    const formData = new FormData(this);
    formData.append('action', 'update_profile');

    fetch(myAjax.ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(response => {
        if (!response.success) {
            const emailInput = document.querySelector('.email');

            const error = document.createElement('p');
            error.className = 'text-danger error-message mt-2 small';
            error.textContent = response.data.message;

            emailInput.parentNode.insertBefore(error, emailInput.nextSibling);
        } else {
            const msg = document.getElementById('profile-update-message');
            msg.innerHTML = `<div class="alert alert-${response.success ? 'success' : 'danger'}">${response.data.message}</div>`;
            if (response.success) setTimeout(() => location.reload(), 2000);
        }
    });
});

// jQuery(document).ready(function($) {
//     $('#password-form').on('submit', function(e) {
//         e.preventDefault();

//         const messageContainer = $('#password-update-message');
//         let isValid = true;
//         let currentPassword = $('#current_password').val().trim();
//         let newPassword = $('#new_password').val().trim();
//         let confirmPassword = $('#confirm_password').val().trim();

//         $('.error-message').remove();

//         if (currentPassword === '') {
//             isValid = false;
//             $('.current-password').after('<p class="text-danger error-message mt-2 small">Current password is required.</p>');
//         }

//         if (newPassword === '') {
//             isValid = false;
//             $('.new-password').after('<p class="text-danger error-message mt-2 small">New password is required.</p>');
//         }

//         if (confirmPassword === '') {
//             isValid = false;
//             $('.confirm-password').after('<p class="text-danger error-message mt-2 small">Confirm password is required.</p>');
//         }

//         if (!isValid) return;

//         messageContainer.html('');

//         const data = {
//             action: 'update_user_password',
//             security: $('#update_password_nonce').val(),
//             current_password: $('#current_password').val(),
//             new_password: $('#new_password').val(),
//             confirm_password: $('#confirm_password').val()
//         };

//         console.log(data); // Debug: See what's being sent

//         $.post(myAjax.ajaxUrl, data, function(response) {
//             messageContainer.html('<div class="alert alert-' + (response.success ? 'success' : 'danger') + '">' + response.data.message + '</div>');
//         });
//     });
// });