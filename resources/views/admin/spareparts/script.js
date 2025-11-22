document.addEventListener("DOMContentLoaded", function () {
  const orderList = document.querySelector(".order-list");

  if (orderList) {
    orderList.addEventListener("click", function (event) {
      const button = event.target.closest("button");
      if (!button) return;

      const orderId = button.dataset.orderid;

      if (button.classList.contains("btn-accept")) {
        if (confirm(`Proses pesanan dengan ID ${orderId} dan pindahkan ke riwayat?`)) {
          window.location.href = "/EfkaWorkshop/backend/proses_accept_order.php?id=" + orderId;
        }
      }

      if (button.classList.contains("btn-delete")) {
        if (confirm(`Anda yakin ingin MEMBATALKAN pesanan dengan ID ${orderId}?`)) {
          window.location.href = "/EfkaWorkshop/backend/proses_cancel_order.php?id=" + orderId;
        }
      }
    });
  }
});
