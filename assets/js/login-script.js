const container = document.getElementById("container");
const registerBtn = document.getElementById("register");
const loginBtn = document.getElementById("login");

registerBtn.addEventListener("click", () => {
  container.classList.add("active");
});

loginBtn.addEventListener("click", () => {
  container.classList.remove("active");
});

document.addEventListener("DOMContentLoaded", function () {
  const togglePassword = document.getElementById("toggle-password");
  const passwordInput = document.getElementById("signup-password");

  if (togglePassword && passwordInput) {
    togglePassword.addEventListener("click", function () {
      const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);
      this.classList.toggle("fa-eye-slash");
    });
  }

  const signupForm = document.getElementById("signup-form");
  const signupButton = document.getElementById("signup-button");
  const criteria = {
    length: document.getElementById("length"),
    capital: document.getElementById("capital"),
    number: document.getElementById("number"),
    special: document.getElementById("special"),
  };

  if (passwordInput) {
    passwordInput.addEventListener("input", function () {
      const value = passwordInput.value;
      let allValid = true;

      if (value.length >= 8) {
        criteria.length.classList.add("valid");
        criteria.length.classList.remove("invalid");
      } else {
        criteria.length.classList.add("invalid");
        criteria.length.classList.remove("valid");
        allValid = false;
      }

      if (/[A-Z]/.test(value)) {
        criteria.capital.classList.add("valid");
        criteria.capital.classList.remove("invalid");
      } else {
        criteria.capital.classList.add("invalid");
        criteria.capital.classList.remove("valid");
        allValid = false;
      }

      if (/[0-9]/.test(value)) {
        criteria.number.classList.add("valid");
        criteria.number.classList.remove("invalid");
      } else {
        criteria.number.classList.add("invalid");
        criteria.number.classList.remove("valid");
        allValid = false;
      }

      if (/[^A-Za-z0-9]/.test(value)) {
        criteria.special.classList.add("valid");
        criteria.special.classList.remove("invalid");
      } else {
        criteria.special.classList.add("invalid");
        criteria.special.classList.remove("valid");
        allValid = false;
      }

      signupButton.disabled = !allValid;
    });
  }

  const container = document.getElementById("container");
  const registerBtn = document.getElementById("register");
  const loginBtn = document.getElementById("login");

  if (registerBtn) {
    registerBtn.addEventListener("click", () => {
      container.classList.add("active");
    });
  }

  if (loginBtn) {
    loginBtn.addEventListener("click", () => {
      container.classList.remove("active");
    });
  }
  const togglePasswordSignin = document.getElementById("toggle-password-signin");
  const passwordInputSignin = document.getElementById("signin-password");

  if (togglePasswordSignin && passwordInputSignin) {
    togglePasswordSignin.addEventListener("click", function () {
      const type = passwordInputSignin.getAttribute("type") === "password" ? "text" : "password";
      passwordInputSignin.setAttribute("type", type);
      this.classList.toggle("fa-eye-slash");
    });
  }
});
