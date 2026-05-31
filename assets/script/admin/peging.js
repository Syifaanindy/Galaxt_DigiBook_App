    (function () {
      const pageSize = 8;
      let currentPage = 1;
      const body = document.getElementById("userTableBody");
      const info = document.getElementById("userPaginationInfo");
      const label = document.getElementById("userPageLabel");
      const prevBtn = document.getElementById("userPrevBtn");
      const nextBtn = document.getElementById("userNextBtn");
      const prevItem = document.getElementById("userPrevItem");
      const nextItem = document.getElementById("userNextItem");

      function rows() {
        return Array.from(body.querySelectorAll("tr"));
      }

      function pageCount() {
        return Math.max(1, Math.ceil(rows().length / pageSize));
      }

      function render() {
        const allRows = rows();
        const totalPages = pageCount();
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;

        allRows.forEach(function (row, index) {
          row.style.display = index >= start && index < end ? "" : "none";
        });

        info.textContent = "Halaman " + currentPage + " dari " + totalPages + " (Total " + allRows.length + " data)";
        label.textContent = currentPage + " / " + totalPages;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        prevItem.classList.toggle("disabled", currentPage === 1);
        nextItem.classList.toggle("disabled", currentPage === totalPages);
      }

      body.addEventListener("click", function (event) {
        const deleteBtn = event.target.closest(".btn-delete");
        if (!deleteBtn) return;
        const row = deleteBtn.closest("tr");
        if (!row) return;
        row.remove();
        render();
      });

      prevBtn.addEventListener("click", function () {
        if (currentPage > 1) {
          currentPage -= 1;
          render();
        }
      });

      nextBtn.addEventListener("click", function () {
        if (currentPage < pageCount()) {
          currentPage += 1;
          render();
        }
      });

      render();
    })();