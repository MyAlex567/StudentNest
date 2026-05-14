document.addEventListener('DOMContentLoaded', function(){
    const fileInput = document.getElementById('fileInput');
    const attach_file_container = document.getElementById('attach_file_container');
    const editor_section = document.getElementById('editor_section');

    fileInput.addEventListener('change', function(){
        // const fileName = this.files.length ? Object.values(this.files).map(file => file.name).join(" | ") : 'No file choosen';
        // filename_display.textContent = fileName;
        AttachFile(this.files);
    });


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
});