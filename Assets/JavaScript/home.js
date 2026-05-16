import { loadSettings } from "./Home_javaScriptFiles/loadSettings.js";
import { loadClass } from "./Home_javaScriptFiles/loadClass.js";
import { loadTobeGraded } from "./Home_javaScriptFiles/loadTobeGraded.js";

document.addEventListener("DOMContentLoaded", function() {

    const btn = document.getElementById('sidebarCollapse');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    btn.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            // Mobile behavior: Slide full nav in
            sidebar.classList.add('active');
            overlay.classList.add('show');
        } else {
            // Desktop behavior: Mini icons toggle
            sidebar.classList.toggle('mini');
        }
    });

    // Close sidebar on mobile when clicking outside
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('show');
    });

    // ************************************************************************************ //
    const settings_function = document.getElementById('settings_toggle');
    const class_toggle = document.getElementById("class_toggle");
    const to_be_graded = document.getElementById('to_be_graded_toggle');

    // function to be execute if the user click the settings tab
    settings_function.addEventListener('click', function(){

        const main_user_content = document.getElementById('Main_user_content');
        // set the main content to default first
        main_user_content.innerHTML = `
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                <span class="fw-semibold text-muted">Loading, please wait...</span>
                            </div>
                        `;
        let intervalID;

        // called the function from the loadSettings Js
        intervalID = setInterval(()=>{
            loadSettings();
            clearInterval(intervalID);
        }, 500);



    });

    // function to be execute if the user click the class tab
    class_toggle.addEventListener('click', function() {
        const main_user_content = document.getElementById('Main_user_content');
        // set the main content to default first
        main_user_content.innerHTML = `
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                <span class="fw-semibold text-muted">Loading, please wait...</span>
                            </div>
                        `;
        let intervalID;

        // called the function from the loadSettings Js
        intervalID = setInterval(()=>{
            loadClass();
            clearInterval(intervalID);
        }, 500);
        
    });



    // funtion to be execute if the user click the to be graded tab
    to_be_graded.addEventListener('click', function(){
        const main_user_content = document.getElementById('Main_user_content');
        // set the main content to default first
        main_user_content.innerHTML = `
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                <span class="fw-semibold text-muted">Loading, please wait...</span>
                            </div>
                        `;
        let intervalID;

        // called the function from the loadSettings Js
        intervalID = setInterval(()=>{
            loadTobeGraded();
            clearInterval(intervalID);
        }, 500);
    });

    
});