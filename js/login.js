function myMenuFunction() {
    const i = document.getElementById("navMenu");
    i.className = (i.className === "nav-menu") ? "nav-menu responsive" : "nav-menu";
  }

  function loginForm() {
    document.getElementById("login").style.left = "4px";
    document.getElementById("register").style.right = "-520px";
  }

  function registerForm() {
    document.getElementById("login").style.left = "-510px";
    document.getElementById("register").style.right = "5px";
  }

  function setInitialForm() {
    const type = "<?= $formType ?>";
    if (type === "register") {
      registerForm();
    } else {
      loginForm();
    }
  }