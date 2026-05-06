export function loadSettings(){
    // Div element to be change
    const main_user_content = document.getElementById('Main_user_content');

    // storing html text after fetching the data from the UserApi with user data
    let SettingsTxt = '';

    fetch('./src/APIs/UserApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'getUser-info'
        })
    }).then(response => response.json()).then( data => {
        console.log(data);

        if(!data.result){
            main_user_content.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-person-x fs-1 text-warning"></i>
                    <h5 class="mt-3">No User Data Found</h5>
                    <p class="text-muted mb-0">We couldn’t find your account information.</p>
                </div>
            `;
            return;
        }

        SettingsTxt = `

        <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold" id="user_content_header"><i class="bi bi-journal-bookmark-fill text-success me-2"></i>Account Settings</h5>
        </div>

        <div class="card-body px-4" id="settings_content">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-2">
                        <span class="text-muted fw-medium" style="min-width: 100px;">Full Name:</span>
                        <span class="fw-bold text-dark" id="account_fullname">${data.userData.data.first_name} ${data.userData.data.last_name}</span>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-2">
                        <span class="text-muted fw-medium" style="min-width: 100px;">Username:</span>
                        <span class="fw-bold text-dark" id="account_username">${data.userData.data.username}</span>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-2">
                        <span class="text-muted fw-medium" style="min-width: 100px;">Email:</span>
                        <span class="fw-bold text-dark" id="account_email">${data.userData.data.email}</span>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-2">
                        <span class="text-muted fw-medium" style="min-width: 100px;">Account Created:</span>
                        <span class="fw-bold text-dark" id="account_created_at">${data.userData.data.created_at}</span>
                    </div>
                </div>  

                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-2">
                        <span class="text-muted fw-medium" style="min-width: 100px;">Sex:</span>
                        <span class="fw-bold text-dark" id="account_sex">${data.userData.data.sex}</span>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-2">
                        <span class="text-muted fw-medium" style="min-width: 100px;">Birthdate:</span>
                        <span class="fw-bold text-dark" id="account_birthdate">${data.userData.data.birthdate}</span>
                    </div>        
                
            </div>
        </div>
        `;

        main_user_content.innerHTML = SettingsTxt;

    }).catch(() => {
        main_user_content.innerHTML = `
            <div class="message_error">
                <h3 style="text-align:center">
                    Network error. Try again.
                </h3>
            </div>
        `;
    });
}