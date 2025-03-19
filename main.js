// Wait for document ready
document.addEventListener("DOMContentLoaded", function () {
  // Initialize Firebase
  window.initFirebase();
  const provider = new window.GoogleAuthProvider();

  // Initialize Magnific Popup if it exists
  if (typeof $.fn.magnificPopup !== "undefined") {
    $(".image-popup").magnificPopup({
      type: "image",
      closeOnContentClick: true,
      closeBtnInside: false,
      fixedContentPos: true,
      mainClass: "mfp-no-margins mfp-with-zoom",
      gallery: {
        enabled: true,
        navigateByImgClick: true,
        preload: [0, 1],
      },
      image: {
        verticalFit: true,
      },
      zoom: {
        enabled: true,
        duration: 300,
      },
    });
  }

  const loginButton = document.getElementById("google-login-button");
  if (loginButton) {
    loginButton.addEventListener("click", () => {
      loginButton.disabled = true;
      loginButton.innerHTML =
        '<img src="https://www.google.com/favicon.ico" alt="Google" style="width: 20px; margin-right: 10px;">Signing in...';

      window
        .signInWithPopup(window.auth, provider)
        .then((result) => {
          const user = result.user;

          // Send to server
          fetch("login.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              google_signin: true,
              email: user.email,
              name: user.displayName,
              google_id: user.uid,
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.status === "success") {
                Swal.fire({
                  title: "Success!",
                  text: data.message,
                  icon: "success",
                  showConfirmButton: false,
                  timer: 1500,
                  timerProgressBar: true,
                }).then(() => {
                  window.location.href = data.redirect;
                });
              } else {
                throw new Error(data.message || "Login failed");
              }
            })
            .catch((error) => {
              Swal.fire({
                title: "Error!",
                text: error.message,
                icon: "error",
                confirmButtonText: "OK",
              });
              resetButton();
            });
        })
        .catch((error) => {
          console.error("Firebase auth error:", error);
          Swal.fire({
            title: "Error!",
            text: error.message,
            icon: "error",
            confirmButtonText: "OK",
          });
          resetButton();
        });
    });
  }

  function resetButton() {
    const button = document.getElementById("google-login-button");
    if (button) {
      button.disabled = false;
      button.innerHTML =
        '<img src="https://www.google.com/favicon.ico" alt="Google" style="width: 20px; margin-right: 10px;">Login with Google';
    }
  }
});
