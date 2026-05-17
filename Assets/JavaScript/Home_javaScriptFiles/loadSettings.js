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

            <hr class="my-4">

            <form id="update_account_form" method="POST" class="mt-3">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-pencil-square text-primary me-2"></i>
                    Edit Account Information
                </h6>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="first_name" class="form-label fw-semibold">First Name</label>
                        <input 
                            type="text" 
                            class="form-control rounded-3" 
                            id="first_name" 
                            name="first_name"
                            value="${data.userData.data.first_name}"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="last_name" class="form-label fw-semibold">Last Name</label>
                        <input 
                            type="text" 
                            class="form-control rounded-3" 
                            id="last_name" 
                            name="last_name"
                            value="${data.userData.data.last_name}"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <input 
                            type="text" 
                            class="form-control rounded-3" 
                            id="username" 
                            name="username"
                            value="${data.userData.data.username}"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input 
                            type="email" 
                            class="form-control rounded-3" 
                            id="email" 
                            name="email"
                            value="${data.userData.data.email}"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="sex" class="form-label fw-semibold">Sex</label>
                        <select 
                            class="form-select rounded-3" 
                            id="sex" 
                            name="sex"
                            required
                        >
                            <option value="">Select sex</option>
                            <option value="male" ${data.userData.data.sex === 'male' ? 'selected' : ''}>Male</option>
                            <option value="female" ${data.userData.data.sex === 'female' ? 'selected' : ''}>Female</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="birthdate" class="form-label fw-semibold">Birthdate</label>
                        <input 
                            type="date" 
                            class="form-control rounded-3" 
                            id="birthdate" 
                            name="birthdate"
                            value="${data.userData.data.birthdate}"
                            required
                        >
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button 
                        type="reset" 
                        class="btn btn-light rounded-pill px-4 fw-semibold"
                    >
                        Reset
                    </button>

                    <button 
                        type="submit" 
                        name="update_account"
                        class="btn btn-primary rounded-pill px-4 fw-semibold"
                    >
                        <i class="bi bi-save me-2"></i>
                        Save Changes
                    </button>
                </div>
            </form>  

            <hr class="my-4">

            <div class="border border-danger rounded-4 p-4 bg-light">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h6 class="fw-bold text-danger mb-1">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Delete Account
                        </h6>
                        <p class="text-muted small mb-0">
                            This action cannot be undone. All account data connected to this user may be deleted.
                        </p>
                    </div>

                    <form 
                        id="delete_account_form" 
                        method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.');"
                    >
                        <button 
                            type="submit" 
                            name="delete_account"
                            class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold"
                        >
                            <i class="bi bi-trash me-1"></i>
                            Delete Account
                        </button>
                    </form>
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