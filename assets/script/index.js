const toggleBtn =
document.getElementById("darkModeToggle");

toggleBtn.addEventListener("click", () => {

    document.body.classList.toggle("dark-mode");

    const icon =
    toggleBtn.querySelector("i");

    if(document.body.classList.contains("dark-mode")){

        icon.classList.remove("fa-moon");
        icon.classList.add("fa-sun");

    }else{

        icon.classList.remove("fa-sun");
        icon.classList.add("fa-moon");

    }

});