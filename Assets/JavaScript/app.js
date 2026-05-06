document.addEventListener("DOMContentLoaded", event => {

    const Sign_up_username = document.getElementById('sign_up_username');
    const usernameStatus = document.getElementById('usernameStatus');

    let timoutid_1;

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

});