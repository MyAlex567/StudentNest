export function loadClass(){
    // Div element to be change
    const main_user_content = document.getElementById('Main_user_content');

    let classTxt = '';

    fetch('./src/APIs/UserApi.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'get-class'
                            })
                        }).then(response => response.json()).then(result => {
                            console.log(result.classes.length);

                            if(!result.success){    
                                main_user_content.innerHTML = `
                                    <div class="text-center py-5">
                                        <i class="bi bi-exclamation-circle fs-1 text-danger"></i>
                                        <h5 class="mt-3">Failed to load data</h5>
                                        <p class="text-muted mb-3">Something went wrong. Please try again.</p>
                                        <button class="btn btn-outline-danger btn-sm" onclick="loadClasses()">
                                            Retry
                                        </button>
                                    </div>                                    
                                `;
                                return;
                            }

                            if(result.classes.length === 0){
                                    main_user_content.innerHTML = `
                                        <div class="text-center py-5">
                                            <i class="bi bi-journal-x fs-1 text-muted"></i>
                                            <h5 class="mt-3 text-muted">No Classes Yet</h5>
                                            <p class="text-muted mb-4">
                                                You haven’t joined or created any classes yet.
                                            </p>

                                            <button class="btn btn-secondary rounded-pill px-4">
                                                Join or Create Class
                                            </button>
                                        </div>
                                    `;
                                return;
                            }

                            result.classes.forEach(value => {
                                classTxt += `
                                    <div class="col-auto">
                                        <div class="card class-card border-0 shadow text-dark">

                                            <img src="./Assets/Images/Logos/ClassLogo.png"
                                                class="card-img-top d-none d-md-block class-img"
                                                alt="...">

                                            <div class="card-body p-3">

                                                <h5 class="card-title fw-bold mb-1">
                                                    ${value.class_name}
                                                </h5>

                                                <p class="card-text text-muted mb-1 small">
                                                    Section: ${value.section ? value.section : 'Not available'}
                                                </p>

                                                <p class="card-text text-muted small mb-3">
                                                    Created by: ${value.creator}
                                                </p>

                                                <a href="./src/Views/classPage.php?class_code=${value.class_code}" class="btn btn-dark btn-sm w-100 rounded-pill">
                                                    View Class
                                                </a>

                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            main_user_content.innerHTML =  `

                                <div class="row g-3 justify-content-center justify-content-md-start">
                                    ${classTxt}
                                </div>

                            `;

                        }).catch(() => {
                            main_user_content.innerHTML = `
                                <div class="text-center py-5">
                                    <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                                    <h5 class="mt-3">Something went wrong</h5>
                                    <p class="text-muted mb-0">There was an error fetching the data.</p>
                                </div>
                            `;
                        });
}