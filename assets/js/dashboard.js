// Sidebar Toggle
    document.getElementById("sidebarToggle").addEventListener("click", () => {
      const sidebar = document.getElementById("sidebar");
      const layout = document.getElementById("layout");
      const submenu = document.getElementById('createSubmenu');
      sidebar.classList.toggle("collapsed");
      layout.classList.toggle("collapsed");
      // Close submenu when sidebar collapses
      if (sidebar.classList.contains("collapsed")) {
        submenu.style.display = "none";
      }
    });

    // Collapse sidebar by default on page load
    window.addEventListener('DOMContentLoaded', () => {
      document.getElementById("sidebar").classList.add("collapsed");
      document.getElementById("layout").classList.add("collapsed");
    });

    // Date/Time
    function updateDateTime() {
      const now = new Date();
      document.getElementById("datetime").textContent = now.toLocaleString('en-GB');
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();

    // Sidebar submenu toggle
    function toggleSidebarDropdown(e) {
      e.preventDefault();
      const submenu = document.getElementById('createSubmenu');
      submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
    }