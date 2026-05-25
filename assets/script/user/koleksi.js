const sampleBooks = [
  { title: 'Makrifat Daun: Daun Makrifat', author: 'Kuntowijoyo', category: 'sastra', categoryLabel: 'Sastra Indonesia', price: 50000, cover: '../../assets/pic/b-1.png' },
  { title: 'Dilarang Mencintai Bunga-Bunga', author: 'Kuntowijoyo', category: 'sastra', categoryLabel: 'Sastra Indonesia', price: 135000, cover: '../../assets/pic/b-2.png' },
  { title: 'Perubahan Sosial Dalam Masyarakat', author: 'Kuntowijoyo', category: 'sosiologi', categoryLabel: 'Sosiologi', price: 200000, cover: '../../assets/pic/b-3.png' },
  { title: 'Identitas Politik Umat Islam', author: 'Kuntowijoyo', category: 'agama', categoryLabel: 'Agama', price: 90000, cover: '../../assets/pic/b-4.webp' },
  { title: 'Filsafat Ilmu', author: 'Jujun S. Suriasumantri', category: 'filsafat', categoryLabel: 'Filsafat', price: 120000, cover: '../../assets/pic/b-5.webp' },
  { title: 'Sejarah Umat Manusia', author: 'Yuval Noah Harari', category: 'sejarah', categoryLabel: 'Sejarah', price: 150000, cover: '../../assets/pic/b-6.webp' },
  { title: 'Sosiologi Untuk Pemula', author: 'A. Giddens', category: 'sosiologi', categoryLabel: 'Sosiologi', price: 78000, cover: '../../assets/pic/b-3.webp' },
  { title: 'Pengantar Filsafat Barat', author: 'Bertrand Russell', category: 'filsafat', categoryLabel: 'Filsafat', price: 110000, cover: '../../assets/pic/b-2.webp' }
];

const searchInput = document.getElementById('searchInput');
const categorySelect = document.getElementById('categorySelect');
const bookGrid = document.getElementById('bookGrid');
const resultInfo = document.getElementById('resultInfo');
const pagination = document.getElementById('pagination');

const itemsPerPage = 20;
let currentPage = 1;

function formatRupiah(number) {
  return 'Rp ' + number.toLocaleString('id-ID');
}

function bookCard(book) {
  return `
    <article class="book-card">
      <div class="book-header">
        <div class="book-cover">
          <img src="${book.cover}" alt="${book.title}">
        </div>
      </div>
      <div class="book-content">
        <h3 class="book-title">${book.title}</h3>
        <p class="book-author">${book.author}</p>
        <p class="book-category">Kategori: ${book.categoryLabel}</p>
        <div class="book-footer">
          <p class="book-price">${formatRupiah(book.price)}</p>
          <a href="detail.html" class="detail-btn">Detail</a>
        </div>
      </div>
    </article>
  `;
}

function renderPagination(totalItems) {
  const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

  if (totalItems <= itemsPerPage) {
    pagination.innerHTML = '';
    return;
  }

  let buttons = '';
  for (let page = 1; page <= totalPages; page += 1) {
    buttons += `<button class="page-btn ${page === currentPage ? 'active' : ''}" data-page="${page}" type="button">${page}</button>`;
  }

  pagination.innerHTML = buttons;
  pagination.querySelectorAll('.page-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      currentPage = Number(btn.dataset.page);
      renderBooks();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });
}

function renderBooks() {
  const query = searchInput.value.trim().toLowerCase();
  const category = categorySelect.value;

  const filtered = sampleBooks.filter((book) => {
    const matchCategory = category === 'all' ? true : book.category === category;
    const matchSearch = book.title.toLowerCase().includes(query) || book.author.toLowerCase().includes(query);
    return matchCategory && matchSearch;
  });

  const totalItems = filtered.length;
  const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
  if (currentPage > totalPages) currentPage = 1;

  const start = (currentPage - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  const paginatedBooks = filtered.slice(start, end);

  resultInfo.textContent = `${totalItems} buku ditemukan · Halaman ${currentPage}/${totalPages} · Maks ${itemsPerPage} buku/halaman`;

  if (totalItems === 0) {
    bookGrid.innerHTML = '<div class="empty-state">Buku tidak ditemukan. Coba kata kunci atau kategori lain.</div>';
    pagination.innerHTML = '';
    return;
  }

  bookGrid.innerHTML = paginatedBooks.map(bookCard).join('');
  renderPagination(totalItems);
}

searchInput.addEventListener('input', () => {
  currentPage = 1;
  renderBooks();
});

categorySelect.addEventListener('change', () => {
  currentPage = 1;
  renderBooks();
});

renderBooks();
