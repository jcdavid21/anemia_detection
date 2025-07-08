// Vanilla JavaScript for show/hide password and form switching
const container = document.querySelector(".container"),
  pwShowHide = document.querySelectorAll(".showHidePw"),
  pwFields = document.querySelectorAll(".password"),
  signUp = document.querySelector(".signup-link"),
  login = document.querySelector(".login-link");

// js code to show/hide password and change icon
pwShowHide.forEach((eyeIcon) => {
  eyeIcon.addEventListener("click", () => {
    pwFields.forEach((pwField) => {
      if (pwField.type === "password") {
        pwField.type = "text";
        pwShowHide.forEach((icon) => {
          icon.classList.replace("uil-eye-slash", "uil-eye");
        });
      } else {
        pwField.type = "password";
        pwShowHide.forEach((icon) => {
          icon.classList.replace("uil-eye", "uil-eye-slash");
        });
      }
    });
  });
});

// js code to appear signup and login form
signUp.addEventListener("click", (e) => {
  e.preventDefault();
  container.classList.add("active");
});

login.addEventListener("click", (e) => {
  e.preventDefault();
  container.classList.remove("active");
});

// Fixed login functionality - replaced jQuery with vanilla JavaScript
document.addEventListener("DOMContentLoaded", function() {
    const loginButton = document.getElementById("login");
    
    loginButton.addEventListener("click", function(e) {
        e.preventDefault();
        
        // Get the email and password values
        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;
        
        // Get the error container - fixed selector
        const invalidContainer = document.querySelector(".invalid-login");
        
        // Clear previous error messages
        invalidContainer.innerHTML = "";

        // Validation
        if (!email || !password) {
            const errorDiv = document.createElement("div");
            errorDiv.className = "error";
            errorDiv.textContent = "Please fill in all fields.";
            invalidContainer.appendChild(errorDiv);

            setTimeout(() => {
                errorDiv.remove();
            }, 3000); // Remove error after 3 seconds
            return;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            const errorDiv = document.createElement("div");
            errorDiv.className = "error";
            errorDiv.textContent = "Please enter a valid email address.";
            invalidContainer.appendChild(errorDiv);
            return;
        }

        $.ajax({
            url: "../backend/users/login.php",
            type: "POST",
            data: {
                email: email,
                password: password
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.status === "success") {
                    // Redirect to the dashboard or home page
                    window.location.href = "components/dashboard.php";
                } else {
                    // Show error message
                    const errorDiv = document.createElement("div");
                    errorDiv.className = "error";
                    errorDiv.textContent = data.message;
                    invalidContainer.appendChild(errorDiv);
                    
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 3000); // Remove error after 3 seconds
                }
            },
            error: function(xhr, status, error) {
                const errorDiv = document.createElement("div");
                errorDiv.className = "error";
                errorDiv.textContent = "An error occurred. Please try again later.";
                invalidContainer.appendChild(errorDiv);
                
                setTimeout(() => {
                    errorDiv.remove();
                }, 3000); // Remove error after 3 seconds
            }
        })
        
    });

    const signupButton = document.getElementById("signup");
    signupButton.addEventListener("click", function(e){
        e.preventDefault();

        const fullName = document.getElementById("full_name").value;
        const createEmail = document.getElementById("create_email").value;
        const createPass = document.getElementById("create_pass").value;
        const confirmPass = document.getElementById("confirm_pass").value;
        const invalidContainer = document.querySelector(".invalid-signup");
        invalidContainer.innerHTML = "";
        if (!fullName || !createEmail || !createPass || !confirmPass) {
            const errorDiv = document.createElement("div");
            errorDiv.className = "error";
            errorDiv.textContent = "Please fill in all fields.";
            invalidContainer.appendChild(errorDiv);

            setTimeout(() => {
                errorDiv.remove();
            }, 3000); // Remove error after 3 seconds
            return;
        }
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(createEmail)) {
            const errorDiv = document.createElement("div");
            errorDiv.className = "error";
            errorDiv.textContent = "Please enter a valid email address.";
            invalidContainer.appendChild(errorDiv);
            return;
        }
        if (createPass !== confirmPass) {
            const errorDiv = document.createElement("div");
            errorDiv.className = "error";
            errorDiv.textContent = "Passwords do not match.";
            invalidContainer.appendChild(errorDiv);
            return;
        }
        $.ajax({
            url: "../backend/users/signup.php",
            type: "POST",
            data: {
                full_name: fullName,
                email: createEmail,
                password: createPass,
                confirm_pass: confirmPass
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.status === "success") {
                    invalidContainer.innerHTML = ""; // Clear previous errors
                    // Show success message
                    const successDiv = document.createElement("div");
                    successDiv.className = "success";
                    successDiv.textContent = "Registration successful! Redirecting to login...";
                    invalidContainer.appendChild(successDiv);
                    setTimeout(() => {
                        successDiv.remove();
                        window.location.href = "index.php";
                    }, 3000); // Redirect after 3 seconds
                } else {
                    // Show error message
                    const errorDiv = document.createElement("div");
                    errorDiv.className = "error";
                    errorDiv.textContent = data.message;
                    invalidContainer.appendChild(errorDiv);
                    
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 3000); // Remove error after 3 seconds
                }
            },
            error: function(xhr, status, error) {
                const errorDiv = document.createElement("div");
                errorDiv.className = "error";
                errorDiv.textContent = "An error occurred. Please try again later.";
                invalidContainer.appendChild(errorDiv);
                
                setTimeout(() => {
                    errorDiv.remove();
                }, 3000); // Remove error after 3 seconds
            }
        })
    })
});