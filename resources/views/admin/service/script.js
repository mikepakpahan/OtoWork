document.addEventListener("DOMContentLoaded", function () {
  const pageContainer = document.querySelector(".page-container");
  const overlay = document.getElementById("modal-overlay");
  const acceptModal = document.getElementById("accept-modal");
  const closeModalBtn = document.getElementById("close-modal-btn");
  const rejectModal = document.getElementById("reject-modal");
  const closeRejectModalBtn = document.getElementById("close-reject-modal-btn");
  const pendingTableBody = document.querySelector(".pending-table tbody");

  const priceInput = document.getElementById("service_price");

  function showAcceptModal(data) {
    document.getElementById("modal-name").textContent = data.name;
    document.getElementById("modal-email").textContent = data.email;
    document.getElementById("modal-motor-type").textContent = data.motorType;
    document.getElementById("modal-complaint").textContent = data.complaint;
    document.getElementById("modal-booking-id").value = data.bookingId;
    overlay.classList.add("show");
    acceptModal.classList.add("show");
    if (pageContainer) pageContainer.classList.add("body-blur");
  }

  function showRejectModal(data) {
    document.getElementById("modal-reject-name").textContent = data.name;
    document.getElementById("modal-reject-email").textContent = data.email;
    document.getElementById("modal-reject-motor-type").textContent = data.motorType;
    document.getElementById("modal-reject-booking-id").value = data.bookingId;
    overlay.classList.add("show");
    rejectModal.classList.add("show");
    if (pageContainer) pageContainer.classList.add("body-blur");
  }

  function hideAllModals() {
    overlay.classList.remove("show");
    acceptModal.classList.remove("show");
    rejectModal.classList.remove("show");
    if (pageContainer) pageContainer.classList.remove("body-blur");
  }

  if (pendingTableBody) {
    pendingTableBody.addEventListener("click", function (event) {
      const button = event.target;
      const row = button.closest("tr");
      if (!row || !button.dataset.bookingId) return;

      const bookingData = {
        bookingId: button.dataset.bookingId,
        name: row.cells[1].textContent,
        email: row.cells[2].textContent,
        motorType: row.cells[3].textContent,
        complaint: row.cells[5].textContent,
      };

      if (button.classList.contains("btn-accept")) {
        showAcceptModal(bookingData);
      } else if (button.classList.contains("btn-reject")) {
        showRejectModal(bookingData);
      }
    });
  }

  if (closeModalBtn) closeModalBtn.addEventListener("click", hideAllModals);
  if (closeRejectModalBtn) closeRejectModalBtn.addEventListener("click", hideAllModals);
  if (overlay) overlay.addEventListener("click", hideAllModals);

  if (priceInput) {
    priceInput.addEventListener("input", function () {
      this.value = this.value.replace(/[^0-9]/g, "");
    });
  }
});
