// Match the same comma-separated, wildcard paths used by PHP routeActive().
function matchesSidebarPath(path, patterns) {
    return patterns.split(',').some(pattern => {
        if (!pattern) return false;
        const expression = pattern.replace(/[.+?^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
        return new RegExp('^' + expression + '$', 'u').test(path);
    });
}

function sidebarPath(url) {
    const path = new URL(url, window.location.origin).pathname.replace(/^\/+|\/+$/g, '') || '/';
    try {
        return decodeURIComponent(path);
    } catch (e) {
        return path;
    }
}

function updateSidebarActive(url) {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar || !url) return;

    let currentPath;
    try {
        currentPath = sidebarPath(url);
    } catch (e) {
        return;
    }

    const links = sidebar.querySelectorAll('.side-menu a');
    const dropdowns = sidebar.querySelectorAll('.side-dropdown');
    links.forEach(link => {
        const patterns = link.getAttribute('data-active-patterns');
        const linkUrl = link.getAttribute('data-url');
        let active = false;
        if (patterns) {
            active = matchesSidebarPath(currentPath, patterns);
        } else if (linkUrl) {
            try {
                active = sidebarPath(linkUrl) === currentPath;
            } catch (e) {}
        }
        link.dataset.routeActive = String(active);
        link.classList.toggle('active', active);
    });

    // Highlight ancestors even when a group's own pattern omits a visible child.
    links.forEach(link => {
        if (link.dataset.routeActive !== 'true') return;
        let dropdown = link.closest('.side-dropdown');
        while (dropdown) {
            const parentLink = dropdown.parentElement.querySelector('a:first-child');
            if (parentLink) {
                parentLink.dataset.routeActive = 'true';
                parentLink.classList.add('active');
            }
            dropdown = dropdown.parentElement.closest('.side-dropdown');
        }
    });

    // Keep the closed sidebar rail collapsed; reveal the current group when expanded.
    const collapsed = sidebar.classList.contains('hide') && !sidebar.matches(':hover');
    dropdowns.forEach(dropdown => {
        const parentLink = dropdown.parentElement.querySelector('a:first-child');
        dropdown.classList.toggle('show', !collapsed && parentLink?.dataset.routeActive === 'true');
    });
}

module.exports = { matchesSidebarPath, updateSidebarActive };
