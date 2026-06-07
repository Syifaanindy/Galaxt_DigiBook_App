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

function setActiveNav() {
  const page = document.body.dataset.page;
  if (!page) return;
  const activeLink = document.querySelector(`[data-nav="${page}"]`);
  if (activeLink) activeLink.classList.add('active');
}

(async function initSharedLayout() {
  await Promise.all([
    loadPartial('site-navbar', './partials/navbar.html'),
    loadPartial('site-footer', './partials/footer.html')
  ]);
  setActiveNav();
})();