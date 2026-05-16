document.addEventListener('DOMContentLoaded', function(){
    const attach_file_container = document.getElementById('attach_file_container');
    const submission_file = document.getElementById('submission_file');

    submission_file.addEventListener('change', function(){
        activityAttach(this.files);
    });

    function activityAttach(files){
        let attach_display = '';

        for(let index = 0; index < files.length; index++){
            attach_display += `
                <div id="fileItem" class="d-flex justify-content-between align-items-center mt-3">

                    <p class="mb-0">${files[index].name}</p>

                    <button type="button" class="btn btn-sm btn-danger" onclick="removeActivityFile(${index})">
                        ×
                    </button>

                </div>
            `;
        }

        attach_file_container.innerHTML = attach_display;

    }

    window.removeActivityFile = function(index){
        const dataTransfer = new DataTransfer();

        for(let i = 0; i < submission_file.files.length; i++){
            if(index !== i){
                dataTransfer.items.add(submission_file.files[i]);
            }
        }

        submission_file.files = dataTransfer.files;
        activityAttach(dataTransfer.files);
    }
});