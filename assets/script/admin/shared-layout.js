async function loadPartial(targetId, partialPath) {
    const target = document.getElementById(targetId);
    if (!target) return;

    try {
        const response = await fetch(partialPath);
        if (!response.ok) throw new Error(`Failed to load ${partialPath}`);
        target.innerHTML = await response.text();
    } catch (error) {
        console.error(error);
    }
}

function setActiveMenu() {
    const fileName = window.location.pathname.split("/").pop();
    if (!fileName) return;

    // Baris 18 yang sudah diperbaiki untuk PHP
    const resolvedFileName = fileName === "index.php" || fileName === "" ? "dashboard.php" : fileName;

    // Baris 20 yang selector-nya disesuaikan dengan class .menu di sidebar.php
    const menuLinks = document.querySelectorAll(".menu .menu-item");
    
    menuLinks.forEach((link) => {
        const href = link.getAttribute("href");
        if (href === resolvedFileName) {
            link.classList.add("active");
        } else {
            link.classList.remove("active");
        }
    });
}