document.addEventListener('DOMContentLoaded', function(){
    const fileInput = document.getElementById('fileInput');
    const filename_display = document.getElementById('filename_display');
    const editor_section = document.getElementById('editor_section');

    fileInput.addEventListener('change', function(){
        const fileName = this.files.length ? Object.values(this.files).map(file => file.name).join(" | ") : 'No file choosen';
        filename_display.textContent = fileName;

    });

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