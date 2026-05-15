document.addEventListener('DOMContentLoaded', function(){
    const fileInput = document.getElementById('fileInput');
    const attach_file_container = document.getElementById('attach_file_container');
    const activity_file_display = document.getElementById("activity_file_display");
    const file_activity = document.getElementById("file_activity");
    const editor_section = document.getElementById('editor_section');

    fileInput.addEventListener('change', function(){
        AttachFile(this.files);
    });

    file_activity.addEventListener('change', function(){
        activityAttach(this.files);
    });


    // render for displaying the activity file
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

        activity_file_display.innerHTML = attach_display;

    }

    // render for displaying the attach file
    function AttachFile(files){
        let attach_display = '';

        for(let index = 0; index < files.length; index++){
            attach_display += `
                <div id="fileItem" class="d-flex justify-content-between align-items-center mt-3">

                    <p class="mb-0">${files[index].name}</p>

                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile(${index})">
                        ×
                    </button>

                </div>
            `;
        }
        attach_file_container.innerHTML = attach_display;
    }

    // removing attach activity
    window.removeActivityFile = function(index){
        const dataTransfer = new DataTransfer();

        for(let i = 0; i < file_activity.files.length; i++){
            if(index !== i){
                dataTransfer.items.add(file_activity.files[i]);
            }
        }

        file_activity.files = dataTransfer.files;
        activityAttach(dataTransfer.files);
    }

    // removing the attach file
    window.removeFile = function(index){
        const dataTransfer = new DataTransfer();

        for(let i = 0; i < fileInput.files.length; i++){
            if(index !== i){
                dataTransfer.items.add(fileInput.files[i]);
            }
        }

        fileInput.files = dataTransfer.files;
        AttachFile(dataTransfer.files);
    }

    document.getElementById('post_type').addEventListener('change', function(){
        
        if(this.value === 'post_announcement'){
            editor_section.innerHTML = `
                <textarea class="form-control border-0 bg-transparent shadow-none" 
                    rows="5"
                    name="announcement"
                    placeholder="Announce something to your class"
                    style="resize: none;"></textarea>
            `;

            fileInput.value = '';
            filename_display.textContent = 'No file choosen';
            fileInput.disabled = true;
            return;
        }else if(this.value === 'post_material'){
            editor_section.innerHTML = `
                <label for="material_title">Title: </label>
                <input type="text" name="post_title" id="post_title" class="bg-light p-2 rounded-pill px-3 text-muted border" placeholder="title here...">
                <textarea class="form-control border-0 bg-transparent shadow-none" 
                    rows="5" 
                    placeholder="Description (optional)"
                    name="post_description"
                    style="resize: none;"></textarea>
            `;
        }


        document.getElementById('fileInput').disabled = false;
    });

    const delete_post = document.querySelectorAll('.delete_post');

    delete_post.forEach(btn => {
        btn.addEventListener('click', function(){
            fetch('../../src/APIs/UserApi.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'delete-post',
                    postId: this.dataset.postId
                })
            }).then(response => response.json()).then(data => {
                console.log(data);
                
                if(data.success){
                    window.location.href = `./classPage.php?class_code=${this.dataset.classCode}`;
                }
            }).catch(error => {
                // console.error('Error:', error);
                // emailStatus.textContent = 'Error checking email. Please try again.';
                // emailStatus.className = 'email-status unavailable';
            });
        });
    });
});