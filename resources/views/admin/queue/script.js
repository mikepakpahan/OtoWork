document.addEventListener("DOMContentLoaded", function () {
  const queueTableBody = document.querySelector(".queue-table tbody");

  if (queueTableBody) {
    queueTableBody.addEventListener("click", function (event) {
      const button = event.target;
      const bookingId = button.dataset.bookingId;

      if (!bookingId) return; // Keluar jika yang diklik bukan tombol

      // Logika untuk tombol DONE
      if (button.classList.contains("btn-done")) {
        if (confirm("Apakah Anda yakin servis untuk booking ini sudah selesai?")) {
          // Arahkan ke skrip 'done' dengan membawa ID
          window.location.href = "/EfkaWorkshop/backend/proses_done_service.php?id=" + bookingId;
        }
      }

      // Logika untuk tombol DELETE
      if (button.classList.contains("btn-delete")) {
        if (confirm("PERINGATAN: Aksi ini akan menghapus booking dari antrian secara permanen. Lanjutkan?")) {
          // Arahkan ke skrip 'delete' dengan membawa ID
          window.location.href = "/EfkaWorkshop/backend/proses_delete_queue.php?id=" + bookingId;
        }
      }
    });
  }
});
