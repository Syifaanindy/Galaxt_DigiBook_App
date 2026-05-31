// assets/script/admin/katalog-buku.js

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. VALIDASI FILE REALTIME (PDF)
    const fileBukuInputs = document.querySelectorAll('#create-file-buku, #edit-file-buku');
    if (fileBukuInputs.length > 0) {
        fileBukuInputs.forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (ext !== 'pdf') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Format File Salah!',
                            text: 'File Buku dilarang keras selain format PDF.',
                            confirmButtonColor: '#dc3545'
                        });
                        this.value = ''; // Reset inputan file jadi kosong
                    }
                }
            });
        });
    }

    // 2. VALIDASI COVER REALTIME (IMAGE)
    const coverBukuInputs = document.querySelectorAll('#create-cover-buku, #edit-cover-buku');
    if (coverBukuInputs.length > 0) {
        coverBukuInputs.forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const ext = file.name.split('.').pop().toLowerCase();
                    const allowedExt = ['png', 'jpg', 'jpeg'];
                    if (!allowedExt.includes(ext)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Format Cover Salah!',
                            text: 'Cover Buku wajib berformat Gambar (PNG, JPG, JPEG).',
                            confirmButtonColor: '#dc3545'
                        });
                        this.value = ''; // Reset inputan file jadi kosong
                    }
                }
            });
        });
    }

    // 3. LOGIKA TOMBOL EDIT (MEMASUKKAN DATA KE MODAL)
    const tombolEdit = document.querySelectorAll('.btn-edit');
    if (tombolEdit.length > 0) {
        tombolEdit.forEach(button => {
            button.addEventListener('click', function() {
                const editId = document.getElementById('edit-id');
                const editTitle = document.getElementById('edit-title');
                const editPublisher = document.getElementById('edit-publisher');
                const editAuthor = document.getElementById('edit-author');
                const editCategory = document.getElementById('edit-category');
                const editPrice = document.getElementById('edit-price');
                const editSynopsis = document.getElementById('edit-synopsis');

                if (editId) editId.value = this.getAttribute('data-id');
                if (editTitle) editTitle.value = this.getAttribute('data-title');
                if (editPublisher) editPublisher.value = this.getAttribute('data-publisher');
                if (editAuthor) editAuthor.value = this.getAttribute('data-author');
                if (editCategory) editCategory.value = this.getAttribute('data-category');
                if (editPrice) editPrice.value = this.getAttribute('data-price');
                if (editSynopsis) editSynopsis.value = this.getAttribute('data-synopsis');
            });
        });
    }

    // 4. LOGIKA TOMBOL HAPUS (KONFIRMASI SWEETALERT2)
    const tombolHapus = document.querySelectorAll('.btn-delete');
    if (tombolHapus.length > 0) {
        tombolHapus.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus buku?',
                    text: 'Data buku yang dihapus tidak bisa dikembalikan.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = this.href;
                    }
                });
            });
        });
    }
});