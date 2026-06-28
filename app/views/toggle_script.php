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

    // ── Toggle de grupos da nav ──────────────────────────────
    const NAV_KEY = 'navGroupsCollapsed'; // JSON array de grupos fechados

    function loadCollapsedGroups() {
        try { return JSON.parse(localStorage.getItem(NAV_KEY)) || []; }
        catch { return []; }
    }

    function saveCollapsedGroups(list) {
        localStorage.setItem(NAV_KEY, JSON.stringify(list));
    }

    // Aplica estado salvo ao carregar
    const collapsedGroups = loadCollapsedGroups();
    document.querySelectorAll('.nav-toggle').forEach(btn => {
        const groupId = 'group-' + btn.dataset.group;
        const group   = document.getElementById(groupId);
        if (!group) return;

        if (collapsedGroups.includes(btn.dataset.group)) {
            group.classList.add('collapsed');
            btn.setAttribute('aria-expanded', 'false');
        }

        btn.addEventListener('click', () => {
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            if (isOpen) {
                group.classList.add('collapsed');
                btn.setAttribute('aria-expanded', 'false');
                const list = loadCollapsedGroups();
                if (!list.includes(btn.dataset.group)) list.push(btn.dataset.group);
                saveCollapsedGroups(list);
            } else {
                group.classList.remove('collapsed');
                btn.setAttribute('aria-expanded', 'true');
                saveCollapsedGroups(loadCollapsedGroups().filter(g => g !== btn.dataset.group));
            }
        });
    });
})();
</script>
