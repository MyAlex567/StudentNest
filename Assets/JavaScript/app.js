document.addEventListener("DOMContentLoaded", event => {

    const Sign_up_username = document.getElementById('sign_up_username');
    const Sign_up_email = document.getElementById('email');
    const usernameStatus = document.getElementById('usernameStatus');
    const emailStatus = document.getElementById('emailStatus');

    let timoutid_1;
    let timoutid_2;

    Sign_up_username.addEventListener('input', function () {
        const username = this.value;

        if(timoutid_1){
            clearTimeout(timoutid_1);
        }

        // Client-side validation
        if (username.length === 0) {
            usernameStatus.textContent = 'Username is required';
            usernameStatus.className = 'username-status unavailable';
            submitBtn.disabled = true;
            return;
        }

        if (username.length < 3) {
            usernameStatus.textContent = 'Username must be at least 3 characters';
            usernameStatus.className = 'username-status unavailable';
            submitBtn.disabled = true;
            return;
        }

        if (!/^[a-zA-Z]/.test(username)) {
            usernameStatus.textContent = 'Username must start with a letter';
            usernameStatus.className = 'username-status unavailable';
            submitBtn.disabled = true;
            return;
        }

        if (!/^[a-zA-Z][a-zA-Z0-9_.]*$/.test(username)) {
            usernameStatus.textContent = 'Username can only contain letters, numbers, underscores and dots';
            usernameStatus.className = 'username-status unavailable';
            submitBtn.disabled = true;
            return;
        }        

        // Show checking status
        usernameStatus.textContent = 'Checking availability...';
        usernameStatus.className = 'username-status checking';
        // submitBtn.disabled = true;

        timoutid_1 = setTimeout(()=>{
            check_username_availability(username);
        }, 1000);

    });

    Sign_up_email.addEventListener('input', function () {
        const email = this.value;

        if(timoutid_2){
            clearTimeout(timoutid_2);
        }

        // Client-side validation
        if (email.length === 0) {
            emailStatus.textContent = 'Email is required';
            emailStatus.className = 'email-status unavailable';
            // submitBtn.disabled = true;
            return;
        }

        if(!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)){
            emailStatus.textContent = 'Invalid Email';
            emailStatus.className = 'email-status unavailable';
            // submitBtn.disabled = true;
            return;  
        }     

        // Show checking status
        emailStatus.textContent = 'Checking availability...';
        emailStatus.className = 'email-status checking';
        // submitBtn.disabled = true;

        timoutid_2 = setTimeout(()=>{
            check_email_availability(email);
        }, 1000);

    });


    function check_username_availability(username){
        fetch('../../src/APIs/UserApi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'check-username',
                username: username
            })
        }).then(response => response.json()).then(data => {
            console.log(data);

            if (!data.valid) {
                usernameStatus.textContent = data.errors.join(', ');
                usernameStatus.className = 'username-status unavailable';
                // submitBtn.disabled = true;
            } else if (data.available) {
                usernameStatus.textContent = '✓ Username is available!';
                usernameStatus.className = 'username-status available';
                // submitBtn.disabled = false;
            } else {
                usernameStatus.textContent = '✗ Username is already taken';
                usernameStatus.className = 'username-status unavailable';
                // submitBtn.disabled = true;
            }
        }).catch(error => {
            console.error('Error:', error);
            usernameStatus.textContent = 'Error checking username. Please try again.';
            usernameStatus.className = 'username-status unavailable';
        });
    }

    function check_email_availability(email){
        fetch('../../src/APIs/UserApi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'check-email',
                email: email
            })
        }).then(response => response.json()).then(data => {
            console.log(data);

            if (!data.valid) {
                emailStatus.textContent = data.errors.join(', ');
                emailStatus.className = 'email-status unavailable';
                // submitBtn.disabled = true;
            } else if (data.available) {
                emailStatus.textContent = '✓ Email is available!';
                emailStatus.className = 'email-status available';
                // submitBtn.disabled = false;
            } else {
                emailStatus.textContent = '✗ Email is already taken';
                emailStatus.className = 'email-status unavailable';
                // submitBtn.disabled = true;
            }
        }).catch(error => {
            console.error('Error:', error);
            emailStatus.textContent = 'Error checking email. Please try again.';
            emailStatus.className = 'email-status unavailable';
        });
    }

});