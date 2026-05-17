export function loadSubmitted(){
    // Div element to be change
    const main_user_content = document.getElementById('Main_user_content');

    let submittedTxt = '';

    fetch('./src/APIs/UserApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'get-Submitted'
        })
    }).then(response => response.json()).then( data => {
        console.log(data);
        
        if(data.success){
            data.data.forEach(submitted => {
                console.log(submitted);
                submittedTxt += `
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">

                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <h5 class="fw-bold mb-1">Activity: ${submitted.title}</h5>
                                        <small class="text-muted">
                                            Submitted on ${submitted.submitted_at}
                                        </small>
                                    </div>

                                    ${
                                        submitted.graded_at === null ? 
                                    `<span class="badge bg-warning rounded-pill px-3 py-2">
                                        Pending
                                    </span>`:
                                    `<span class="badge bg-success rounded-pill px-3 py-2">
                                        Graded
                                    </span>`
                                    }
                                </div>

                                <div class="bg-light rounded-3 p-3 mb-3">
                                    <small class="text-muted fw-semibold d-block mb-1">
                                        Your Answer
                                    </small>

                                    <p class="mb-0">
                                        ${submitted.answer_text}
                                    </p>
                                </div>
                                <a href="./src/Views/submittedfilesview.php?submission_id=${submitted.submission_id}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    View Submitted File
                                </a>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <small class="text-muted d-block">Grade</small>
                                        <span class="fw-bold fs-5">${submitted.grade}</span>
                                    </div>

                                    <small class="text-muted">
                                        Graded on: ${submitted.graded_at === null ? 'Not yet' : submitted.graded_at}
                                    </small>
                                </div>

                            </div>
                        </div>
                    </div>                
                `;
            });
            main_user_content.innerHTML = `<div class="row g-4">${submittedTxt}</div`;
        }        


    }).catch(() => {
        main_user_content.innerHTML = `
            <div class="bg-success-subtle text-success p-3 rounded">
                <h3 style="text-align:center">
                    Ops
                </h3>
            </div>
        `;
    });        
}