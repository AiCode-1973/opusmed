<script>
// Toggle sidebar — reutilizável em todas as páginas
(function () {
    const COLLAPSED = 'sidebar-collapsed';
    const btnToggle = document.getElementById('btnToggleSidebar');
    if (!btnToggle) return;

    if (localStorage.getItem('sidebarCollapsed') === '1') {
        document.body.classList.add(COLLAPSED);
    }

    btnToggle.addEventListener('click', () => {
        const isCollapsed = document.body.classList.toggle(COLLAPSED);
        localStorage.setItem('sidebarCollapsed', isCollapsed ? '1' : '0');
        btnToggle.setAttribute('aria-label', isCollapsed ? 'Expandir menu' : 'Recolher menu');
    });
})();
</script>
