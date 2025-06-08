function toggleDropdown() {
  const menu = document.getElementById("dropdownMenu");
  menu.style.display = menu.style.display === "block" ? "none" : "block";
}

function selectAccountType(type) {
  showCreateAccount(type); // This is your existing function
  document.getElementById("dropdownMenu").style.display = "none";
}

// Optional: Close dropdown when clicking outside
document.addEventListener("click", function (event) {
  const dropdown = document.querySelector(".account-dropdown");
  if (!dropdown.contains(event.target)) {
    document.getElementById("dropdownMenu").style.display = "none";
  }
});
// Page navigation functions
function showPage(pageId) {
  const pages = [
    "signInPage",
    "personalAccountPage",
    "organizationAccountPage",
    "credentialsPage",
  ];
  pages.forEach((id) => {
    document.getElementById(id).classList.add("hidden");
  });
  document.getElementById(pageId).classList.remove("hidden");
}

function showCreateAccount(type) {
  if (type === "personal") {
    showPage("personalAccountPage");
  } else {
    showPage("organizationAccountPage");
  }
}


function showFriendRequestPage() {
  showPage("friendRequestPage");
}

// Account type selection
document.addEventListener("DOMContentLoaded", function () {
  const accountTypeButtons = document.querySelectorAll(".account-type button");
  accountTypeButtons.forEach((button) => {
    button.addEventListener("click", function () {
      accountTypeButtons.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");
    });
  });
});

// Friend request handling
function handleFriendRequest(button, action) {
  const card = button.closest(".friend-card");
  const name = card.querySelector(".friend-name").textContent;

  if (action === "confirm") {
    alert(`Friend request from ${name} confirmed!`);
    card.style.opacity = "0.5";
    button.textContent = "Friends";
    button.disabled = true;
    card.querySelector(".btn-decline").style.display = "none";
  } else {
    alert(`Friend request from ${name} declined.`);
    card.remove();
  }
}

function handleSuggestion(button, action) {
  const card = button.closest(".friend-card");
  const name = card.querySelector(".friend-name").textContent;

  if (action === "add") {
    alert(`Friend request sent to ${name}!`);
    button.textContent = "Request Sent";
    button.classList.remove("btn-add");
    button.classList.add("btn-cancel");
    button.onclick = function () {
      handleSuggestion(this, "cancel");
    };
  } else {
    alert(`Friend request to ${name} cancelled.`);
    button.textContent = "Add";
    button.classList.remove("btn-cancel");
    button.classList.add("btn-add");
    button.onclick = function () {
      handleSuggestion(this, "add");
    };
  }
}

// Form validation
document.addEventListener("DOMContentLoaded", function () {
  const credentialsForm = document.getElementById("credentialsForm");
  if (credentialsForm) {
    credentialsForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const password = this.querySelector('input[type="password"]').value;
      const confirmPassword = this.querySelectorAll('input[type="password"]')[1]
        .value;

      if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return;
      }

      showFriendRequestPage();
    });
  }
});

function goBack() {
  if (pageHistory.length > 0) {
    const previousPage = pageHistory.pop();
    showPage(previousPage);
  }
}

function togglePasswordVisibility() {
  const passwordInput = document.getElementById("passwordInput");
  passwordInput.type = passwordInput.type === "password" ? "text" : "password";
}

console.log("Personal - Apply Membership page loaded.");

function showPage(pageId) {
  // Hide all containers first
  document.querySelectorAll(".container").forEach((container) => {
    container.classList.add("hidden");
  });

  // Show the selected page
  document.getElementById(pageId).classList.remove("hidden");
}

function toggleDropdown() {
  const dropdown = document.getElementById("dropdownMenu");
  dropdown.style.display = dropdown.style.display === "none" ? "block" : "none";
}

//Sign up logic
let registrationData = {}; // Temporary storage for form data

// Show the credentials page after filling personal/organization form
function showCredentialsPage() {
  // Check which form is currently visible
  const personalForm = document.getElementById("personalAccountForm");
  const orgForm = document.getElementById("organizationAccountForm");

  if (
    !personalForm.classList.contains("hidden") &&
    personalForm.reportValidity()
  ) {
    const formData = new FormData(personalForm);
    formData.forEach((value, key) => {
      registrationData[key] = value;
    });
  }

  if (!orgForm.classList.contains("hidden") && orgForm.reportValidity()) {
    const formData = new FormData(orgForm);
    formData.forEach((value, key) => {
      registrationData[key] = value;
    });
  }

  showPage("credentialsPage");
}

document.addEventListener("DOMContentLoaded", function () {
  const credentialsForm = document.getElementById("credentialsForm");
  if (credentialsForm) {
    credentialsForm.addEventListener("submit", function (e) {
      e.preventDefault(); // prevent real form submission

      const credentialsFormData = new FormData(credentialsForm);
      credentialsFormData.forEach((value, key) => {
        registrationData[key] = value;
      });

      // Send to PHP via fetch
      fetch("register.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(registrationData),
      })
        .then((response) => response.text())
        .then((data) => {
          alert("Registration successful!");
          console.log(data);
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred during registration.");
        });
    });
  } else {
    console.warn("credentialsForm not found at DOMContentLoaded.");
  }
});

//login logic
let loginData = {}; // Temporary storage for login

function goToPasswordPage() {
  const emailInput = document.getElementById("emailInput").value.trim();
  if (emailInput === "") {
    alert("Please enter your email or phone.");
    return;
  }

  loginData.email = emailInput;

  // Show password page
  showPage("emailPage"); // assumes this hides the email form and shows the password page
}

function login() {
  const passwordInput = document.getElementById("passwordInput").value.trim();
  if (passwordInput === "") {
    alert("Please enter your password.");
    return;
  }

  loginData.password = passwordInput;

  fetch("login.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(loginData),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        localStorage.setItem("user_id", data.user_id);
        localStorage.setItem("username", data.username);
        const accountType = data.account_type;
        if (accountType === "organization") {
          window.location.href = "organization_newsfeed.html";
        } else if (accountType === "personal") {
          window.location.href = "personal_newsfeed.html";
        } else {
          alert("Unknown account type.");
        }
      } else {
        alert("Login failed: " + data.message);
      }
    })
    .catch((err) => {
      console.error("Login error:", err);
      alert("An error occurred during login.");
    });
}

//leftside panel logic
document.addEventListener("DOMContentLoaded", function () {
  const userId = localStorage.getItem("user_id");
  const username = localStorage.getItem("username");

  // Show username
  document.getElementById("usernameDisplay").textContent = username;

  // Fetch org membership info
  fetch("get_org_membership.php", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({ user_id: userId }),
})
  .then((res) => res.json())
  .then((data) => {
    if (data.status === "success" && Array.isArray(data.organizations)) {
      const orgContainer = document.getElementById("orgContainer");
      const template = document.getElementById("orgTemplate");

      data.organizations.forEach((org) => {
        const clone = template.content.cloneNode(true);
        clone.querySelector(".org-name").textContent = org.org_name;
        clone.querySelector(".member-badge").textContent = org.role.toUpperCase();
        orgContainer.appendChild(clone);
      });
    }
  })
  .catch((err) => {
    console.error("Org fetch error:", err);
  });

});



//posting logic
document.addEventListener("DOMContentLoaded", () => {
  const fileInput = document.querySelector(".postbox-file");
  const uploadBtn = document.querySelector(".upload-btn");
  const postBtn = document.querySelector(".post-btn");
  const contentInput = document.querySelector(".postbox-input");
  let selectedFile = null;

  // Safety check: make sure required elements exist
  if (!fileInput || !uploadBtn || !postBtn || !contentInput) {
    console.error("One or more required DOM elements not found.");
    return;
  }

  uploadBtn.addEventListener("click", () => {
    fileInput.click();
  });

  fileInput.addEventListener("change", (e) => {
    selectedFile = e.target.files[0];
  });

  postBtn.addEventListener("click", () => {
    const content = contentInput.value.trim();
    const userId = localStorage.getItem("user_id");

    if (!content && !selectedFile) {
      return alert("Post cannot be empty.");
    }

    const formData = new FormData();
    formData.append("user_id", userId);
    formData.append("content", content);
    if (selectedFile) {
      formData.append("file", selectedFile);
    }

    fetch("create_post.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          contentInput.value = "";
          fileInput.value = "";
          selectedFile = null;
          fetchPosts(); // Refresh feed
        } else {
          alert("Post failed: " + data.message);
        }
      })
      .catch((err) => {
        console.error("Post error:", err);
        alert("Something went wrong while posting.");
      });
  });

  function fetchPosts() {
  fetch("get_posts.php")
    .then((res) => res.json())
    .then((data) => {
      const feed = document.querySelector(".post-feed");
      feed.innerHTML = ""; // Clear existing posts

      if (data.status === "success") {
        data.posts.forEach((post) => {
          const postCard = document.createElement("div");
          postCard.className = "post-card";
          postCard.innerHTML = `
            <div class="post-avatar"></div>
            <div class="post-content">
              <div class="post-header">
                <div class="post-meta">
                  <span class="post-name">${post.username}</span>
                  <span class="post-handle">@${post.username.toLowerCase()}</span>
                  <span class="date">${new Date(post.created_at).toLocaleDateString()}</span>
                </div>
              </div>
              <p class="post-text">${post.content}</p>
              ${post.image_url ? `<img src="${post.image_url}" style="max-width: 100%; margin-top: 10px;">` : ""}
              <div class="post-footer">
                <button><img src="assets/vectors/handthumbsup.svg" alt="Like" /> Like</button>
                <button><img src="assets/vectors/chatbubble.svg" alt="Comment" /> Comment</button>
              </div>
            </div>
          `;
          feed.appendChild(postCard);
        });
      } else {
        feed.innerHTML = `<p class="error-message">Failed to load posts: ${data.message}</p>`;
      }
    })
    .catch((err) => {
      console.error("Fetch error:", err);
      document.querySelector(".post-feed").innerHTML = "<p class='error-message'>Unable to fetch posts. Please try again later.</p>";
    });
}


  fetchPosts(); // Initial load
});

//Friend requests logic

document.addEventListener("DOMContentLoaded", () => {
  const userId = localStorage.getItem("user_id");
  if (!userId) return;

  const requestContainer = document.getElementById("friend-requests-container");
  const friendsContainer = document.getElementById("friends-list-container");

  fetchFriendRequests(userId, requestContainer);
  fetchFriends(userId, friendsContainer);
});


function fetchFriendRequests(userId, container) {
  if (!container) return;
  fetch(`get_friend_requests.php?user_id=${userId}`)
    .then((res) => res.json())
    .then((data) => {
      container.innerHTML = '';
      if (data.status !== "success") return;

      data.requests.forEach((req) => {
        const card = document.createElement("div");
        card.className = "request-card";
        card.dataset.senderId = req.user_id;
        card.innerHTML = `
          <div class="request-avatar"></div>
          <div class="request-info">
            <p class="request-name">${req.username}</p>
            <div class="request-buttons">
              <button class="btn-confirm">Confirm</button>
              <button class="btn-decline">Decline</button>
            </div>
          </div>
        `;
        container.appendChild(card);
      });

      container.querySelectorAll(".btn-confirm").forEach((btn) =>
        btn.addEventListener("click", () =>
          updateFriendRequest(btn.closest(".request-card"), userId, "accept")
        )
      );

      container.querySelectorAll(".btn-decline").forEach((btn) =>
        btn.addEventListener("click", () =>
          updateFriendRequest(btn.closest(".request-card"), userId, "reject")
        )
      );
    });
}

function updateFriendRequest(card, receiverId, action) {
  const senderId = card.dataset.senderId;

  fetch("update_friend_request.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `sender_id=${senderId}&receiver_id=${receiverId}&action=${action}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        card.remove();
        fetchFriends(receiverId); // Refresh friends list on accept
      } else {
        alert("Failed to update friend request.");
      }
    });
}

function fetchFriends(userId, container) {
  if (!container) return;
  fetch(`get_friends.php?user_id=${userId}`)
    .then((res) => res.json())
    .then((data) => {
      container.innerHTML = '';
      if (data.status !== "success") return;

      data.friends.forEach((friend) => {
        const card = document.createElement("div");
        card.className = "friend-card";
        card.innerHTML = `
          <div class="friend-avatar"></div>
          <div>${friend.username}</div>
        `;
        container.appendChild(card);
      });
    });
}




