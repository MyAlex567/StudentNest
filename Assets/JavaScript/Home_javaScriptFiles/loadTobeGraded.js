export function loadTobeGraded(){
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
            action: 'get-To-be-graded'
        })
    }).then(response => response.json()).then( data => {
        console.log(data);
        
        if(!data.success){
            main_user_content.innerHTML = `
                <div class="card border-0 shadow-sm text-center p-5">
                    <div class="card-body">

                        <div class="mb-3">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>

                        <h4 class="fw-bold mb-2">No submissions to grade</h4>

                        <p class="text-muted mb-4">
                            Nice! There are currently no student submissions waiting for grading.
                        </p>

                        <a href="javascript:void(0)" class="btn btn-primary px-4">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            Refresh
                        </a>

                    </div>
                </div>            
            `;
        }

        if(data.success){
            data.data.forEach(submitted => {
                console.log(submitted.submitted_by_name);

                SettingsTxt += `
                    <div class="row g-4">
                        <!-- Card 1 -->
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 18px;">
                                <div class="card-body p-4">

                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="fw-bold mb-1">Activity Title: ${submitted.title}</h5>
                                            <small class="text-muted">
                                                Submitted by 
                                                <span class="fw-bold text-dark">${submitted.submitted_by_name}</span>
                                            </small>
                                        </div>
                                    </div>

                                    <p class="text-muted small mb-3">
                                        Description: ${submitted.description}
                                    </p>

                                    <div class="bg-light rounded-3 p-3 mb-3">
                                        <small class="text-muted d-block mb-1">Answer</small>
                                        <p class="mb-0">
                                            ${submitted.answer_text}
                                        </p>
                                    </div>



                                    ${
                                        submitted.graded_at === null ? `
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <small class="fw-semibold text-danger">
                                                    <span class="badge bg-warning text-dark">To be graded</span>
                                                </small>
                                            </div>
                                            <div class="d-grid">
                                                <a href="./src/Views/gradingform.php?submission_id=${submitted.submission_id}" class="btn btn-primary rounded-pill">
                                                    <i class="bi bi-pencil-square me-1"></i>
                                                    Grade Submission
                                                </a>
                                            </div>` 
                                            :
                                            `
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <small class="fw-semibold text-danger">
                                                    <span class="badge bg-warning text-dark">To be graded</span>
                                                </small>
                                            </div>

                                            <div class="d-grid">
                                                <a class="btn btn-secondary rounded-pill">
                                                    <i class="bi bi-pencil-square me-1"></i>
                                                    Graded
                                                </a>
                                            </div>
                                            `
                                    }

                                </div>
                            </div>
                        </div>
                    </div>                  
                `;


            });
            main_user_content.innerHTML = SettingsTxt;
        }

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