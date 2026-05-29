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

  const resolvedFileName = fileName === "index.html" ? "kategori-buku.html" : fileName;

  const menuLinks = document.querySelectorAll("#admin-sidebar .menu .menu-item");
  menuLinks.forEach((link) => {
    const href = link.getAttribute("href");
    if (href === resolvedFileName) {
      link.classList.add("active");
    } else {
      link.classList.remove("active");
    }
  });
}

(async function initAdminSharedLayout() {
  await loadPartial("admin-sidebar", "./partials/sidebar.html");
  setActiveMenu();
})();