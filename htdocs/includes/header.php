<header class="header">
  <div class="logo">
    <img src="img/logo.png" alt="Company Logo">
  </div>

  <div class="user-menu">
    <div class="user-icon" onclick="toggleDropdown()">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="User">
    </div>

    <div class="dropdown" id="dropdownMenu">
      <a href="#">Profile</a>
      <a href="#">Settings</a>
      <a href="logout.php" class="logout">Logout</a>
    </div>
  </div>
</header>
<script>
  function toggleDropdown() {
    document.getElementById("dropdownMenu").classList.toggle("show");
  }

  // Close dropdown if clicked outside
  window.onclick = function(event) {
    if (!event.target.closest('.user-menu')) {
      document.getElementById("dropdownMenu").classList.remove("show");
    }
  }
</script>
<script src="https://kit.fontawesome.com" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

