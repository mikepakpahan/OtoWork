const menuToggleBtn = document.getElementById("menu-toggle-btn");
const sidebar = document.querySelector(".sidebar");

menuToggleBtn.addEventListener("click", () => {
  sidebar.classList.toggle("is-open");
  menuToggleBtn.classList.toggle("is-open");
});
