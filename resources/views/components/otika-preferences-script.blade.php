<script>
    (function () {
        var form = document.getElementById('display-preferences-form');
        if (!form) return;

        var preferences = @json($preferences);
        var body = document.body;
        var themes = ['white', 'cyan', 'black', 'purple', 'orange', 'green', 'red'];

        function applyPreferences() {
            body.classList.remove('light', 'dark', 'light-sidebar', 'dark-sidebar', 'sidebar-mini', 'navbar-static');
            themes.forEach(function (theme) { body.classList.remove('theme-' + theme); });
            body.classList.add(preferences.theme === 'dark' ? 'dark' : 'light');
            body.classList.add(preferences.sidebar_color === 'dark' ? 'dark-sidebar' : 'light-sidebar');
            body.classList.add('theme-' + (preferences.color_theme || 'white'));
            if (preferences.sidebar === 'compact') body.classList.add('sidebar-mini');
            if (preferences.navbar === 'static') body.classList.add('navbar-static');

            var navbar = document.querySelector('.main-navbar');
            if (navbar) navbar.classList.toggle('sticky', preferences.navbar === 'sticky');
            var mini = document.getElementById('mini_sidebar_setting');
            var sticky = document.getElementById('sticky_header_setting');
            if (mini) mini.checked = preferences.sidebar === 'compact';
            if (sticky) sticky.checked = preferences.navbar === 'sticky';
        }

        function syncForm() {
            var theme = form.querySelector('input[name="theme_choice"]:checked');
            var sidebarColor = form.querySelector('input[name="sidebar_color_choice"]:checked');
            var colorTheme = form.querySelector('#preference-color-theme');
            var mini = document.getElementById('mini_sidebar_setting');
            var sticky = document.getElementById('sticky_header_setting');
            if (theme) form.querySelector('#preference-theme').value = theme.value === '2' ? 'dark' : 'light';
            if (sidebarColor) form.querySelector('#preference-sidebar-color').value = sidebarColor.value === '2' ? 'dark' : 'light';
            if (colorTheme) preferences.color_theme = colorTheme.value;
            preferences.theme = form.querySelector('#preference-theme').value;
            preferences.sidebar_color = form.querySelector('#preference-sidebar-color').value;
            preferences.sidebar = mini && mini.checked ? 'compact' : 'expanded';
            preferences.navbar = sticky && sticky.checked ? 'sticky' : 'static';
        }

        form.addEventListener('change', function (event) {
            if (event.target.matches('.select-layout, .select-sidebar, #mini_sidebar_setting, #sticky_header_setting')) {
                syncForm();
                applyPreferences();
            }
        });
        form.querySelectorAll('[data-theme-color]').forEach(function (button) {
            button.addEventListener('click', function () {
                form.querySelector('#preference-color-theme').value = button.dataset.themeColor;
                form.querySelectorAll('.choose-theme li').forEach(function (item) { item.classList.remove('active'); });
                button.parentElement.classList.add('active');
                syncForm();
                applyPreferences();
            });
        });
        form.addEventListener('submit', syncForm);
        form.querySelector('.btn-restore-theme').addEventListener('click', function () {
            form.querySelector('.select-layout[value="1"]').checked = true;
            form.querySelector('.select-sidebar[value="1"]').checked = true;
            form.querySelector('#preference-color-theme').value = 'white';
            form.querySelectorAll('.choose-theme li').forEach(function (item) { item.classList.toggle('active', item.title === 'white'); });
            document.getElementById('mini_sidebar_setting').checked = false;
            document.getElementById('sticky_header_setting').checked = true;
            syncForm();
            applyPreferences();
            if (form.requestSubmit) form.requestSubmit(); else form.submit();
        });

        syncForm();
        applyPreferences();
    }());
</script>
