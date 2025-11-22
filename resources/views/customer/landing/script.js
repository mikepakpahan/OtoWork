document.addEventListener("DOMContentLoaded", function () {
  const navToggle = document.getElementById("navToggle");
  const mobileNav = document.getElementById("mobileNav");
  if (navToggle && mobileNav) {
    navToggle.addEventListener("click", function () {
      mobileNav.classList.toggle("open");
    });
    mobileNav.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        mobileNav.classList.remove("open");
      });
    });
  }

  document.querySelectorAll(".feature-card").forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.classList.add("feature-card-hover");
    });
    card.addEventListener("mouseleave", function () {
      this.classList.remove("feature-card-hover");
    });
    card.addEventListener("click", function () {
      document.querySelectorAll(".feature-card").forEach((c) => c.classList.remove("feature-card-active"));
      this.classList.add("feature-card-active");
    });
  });

  const bookingForm = document.getElementById("booking-form");
  if (bookingForm) {
    bookingForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const submitButton = bookingForm.querySelector('button[type="submit"]');
      submitButton.disabled = true;
      submitButton.textContent = "Mengirim...";

      fetch("backend/proses_booking.php", {
        method: "POST",
        body: new FormData(bookingForm),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            Swal.fire({
              title: "Berhasil Terkirim!",
              text: data.message,
              icon: "success",
              confirmButtonColor: "#FFC72C",
              confirmButtonText: "Mantap!",
            });
            bookingForm.reset();
          } else {
            Swal.fire({
              title: "Oops... Terjadi Kesalahan",
              text: data.message,
              icon: "error",
              confirmButtonColor: "#FFC72C",
            });
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          Swal.fire("Error", "Terjadi masalah koneksi. Silakan coba lagi.", "error");
        })
        .finally(() => {
          submitButton.disabled = false;
          submitButton.textContent = "Kirim Jadwal Booking";
        });
    });
  }

  const feedbackForm = document.getElementById("feedback-form");

  if (feedbackForm) {
    feedbackForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const submitButton = feedbackForm.querySelector('button[type="submit"]');
      const messageTextarea = feedbackForm.querySelector('textarea[name="message"]');

      if (submitButton.disabled) return;

      submitButton.disabled = true;
      submitButton.textContent = "Mengirim...";

      fetch(feedbackForm.action, {
        method: "POST",
        body: new FormData(feedbackForm),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            Swal.fire({
              title: "Terima Kasih!",
              text: data.message,
              icon: "success",
              confirmButtonColor: "#FFC72C",
              confirmButtonText: "Sama-sama!",
              showClass: {
                popup: "animate__animated animate__fadeInDown",
              },
              hideClass: {
                popup: "animate__animated animate__fadeOutUp",
              },
            });
            messageTextarea.value = "";
          } else {
            Swal.fire({
              title: "Oops... Terjadi Kesalahan",
              text: data.message,
              icon: "error",
              confirmButtonColor: "#FFC72C",
            });
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          Swal.fire({
            title: "Error Koneksi",
            text: "Terjadi masalah saat menghubungi server. Silakan coba lagi.",
            icon: "error",
            confirmButtonColor: "#FFC72C",
          });
        })
        .finally(() => {
          submitButton.disabled = false;
          submitButton.textContent = "Kirim Feedback";
        });
    });
  }
});
function goHome() {
  window.scrollTo({ top: 0, behavior: "smooth" });
}
