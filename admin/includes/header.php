<?php
require_once __DIR__ . '/../paths.php';
require_once __DIR__ . '/../session.php';

$currentPage        = basename($_SERVER['PHP_SELF']);
$currentFolder      = basename(dirname($_SERVER['PHP_SELF']));

$isHome             = $currentPage === 'index.php' && $currentFolder === 'admin';
$isCourses          = $currentFolder === 'courses';
$isStudents         = $currentFolder === 'students';

?>


<header class="dashboard-header" id="dashboardHeader">
    <a href="<?= $indexUrl ?>" class="logo">
        <img src="<?= $iconsUrl ?>logo.png" alt="شعار المنصة">
    </a>

    <nav class="nav-items" id="navMenu">
        <a href="<?= $indexUrl ?>" class="nav-item <?= $isHome ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> الرئيسية
        </a>

        <a href="<?= $coursesUrl ?>courses.php" class="nav-item <?= $isCourses ? 'active' : '' ?>">
            <i class="fas fa-graduation-cap"></i> الكورسات
        </a>

        <a href="<?= $studentsUrl ?>students.php" class="nav-item <?= $isStudents ? 'active' : '' ?>">
            <i class="fas fa-users"></i> الطلاب
        </a>

        <a href="#1" class="nav-item">
            <i class="fas fa-chart-line"></i> المبيعات
        </a>

        <a href="#2" class="nav-item">
            <i class="fas fa-file-alt"></i> الاحصائيات
        </a>

        <a href="#3" class="nav-item">
            <i class="fas fa-bullhorn"></i> الاعلانات
        </a>

        <a href="#4" class="nav-item">
            <i class="fas fa-cog"></i> الإعدادات
        </a>

        <div class="mobile-only-items">
            <a href="#" class="nav-item mobile-theme-item" id="mobileTheme">
                <i class="fas fa-moon"></i> الوضع الليلي
            </a>

            <a href="<?= $logoutUrl ?>" class="nav-item header-mobile-logout">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </a>
        </div>
    </nav>

    <div class="actions">
        <button class="theme-btn" id="themeToggle" title="الوضع الليلي">
            <i class="fas fa-moon"></i>
        </button>

        <div class="add-wrapper">
            <button class="icon-btn header-add-menu-btn" id="addBtn" title="إضافة جديد">
                <i class="fas fa-plus"></i>
            </button>

            <div class="add-dropdown" id="addDropdown">
                <a href="#" class="dropdown-item">
                    <i class="fas fa-graduation-cap"></i> إضافة كورس
                </a>

                <a href="#" class="dropdown-item">
                    <i class="fas fa-user-plus"></i> إضافة طالب
                </a>

                <a href="#" class="dropdown-item">
                    <i class="fas fa-bullhorn"></i> إضافة اعلان
                </a>
            </div>
        </div>

        <div class="notification-wrapper">
            <button class="icon-btn" id="notifBtn" title="الإشعارات">
                <i class="fas fa-bell"></i>
            </button>

            <div class="notification-dropdown" id="notifDropdown">
                <div class="notification-header">
                    <span>الإشعارات</span>
                    <span class="mark-all-read">تحديد الكل كمقروء</span>
                </div>

                <div class="notification-item">
                    <div class="notification-icon notification-success">
                        <i class="fas fa-user-plus"></i>
                    </div>

                    <div>
                        <div class="notif-text">انضم طالب جديد: أحمد سعيد</div>
                        <div class="notif-time">قبل 5 دقائق</div>
                    </div>
                </div>

                <div class="notification-item">
                    <div class="notification-icon notification-danger">
                        <i class="fas fa-dollar-sign"></i>
                    </div>

                    <div>
                        <div class="notif-text">عملية شراء جديدة لكورس تطوير الويب</div>
                        <div class="notif-time">قبل 30 دقيقة</div>
                    </div>
                </div>
            </div>
        </div>

        <a href="<?= $logoutUrl ?>"
            class="header-logout"
            title="تسجيل الخروج"
            aria-label="تسجيل الخروج">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>

        <a href="#" class="profile-btn" title="حسابي">
            <img src="https://ui-avatars.com/api/?name=Admin&background=2563eb&color=fff" alt="Profile">
        </a>

        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>

<script>
    const addBtn = document.getElementById('addBtn');
    const addDropdown = document.getElementById('addDropdown');
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');
    const dashboardHeader = document.getElementById('dashboardHeader');

    function closeAll() {
        if (addDropdown) addDropdown.classList.remove('show');
        if (notifDropdown) notifDropdown.classList.remove('show');
        if (navMenu) navMenu.classList.remove('open');

        if (menuToggle) {
            const icon = menuToggle.querySelector('i');

            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }

            menuToggle.setAttribute('aria-expanded', 'false');
        }
    }

    function positionDropdown(dropdown, button) {
        if (!dropdown || !button || !dashboardHeader) return;
        if (window.innerWidth <= 900) return;

        const rect = button.getBoundingClientRect();
        const width = dropdown.classList.contains('notification-dropdown') ? 320 : 240;
        const margin = 12;

        const left = Math.max(
            margin,
            Math.min(
                rect.right - width,
                window.innerWidth - width - margin
            )
        );

        dropdown.style.left = left + 'px';
        dropdown.style.top = (
            dashboardHeader.getBoundingClientRect().bottom + 10
        ) + 'px';
    }

    function toggleDropdown(dropdown, button) {
        if (!dropdown) return;

        const open = dropdown.classList.contains('show');

        closeAll();

        if (!open) {
            dropdown.classList.add('show');

            requestAnimationFrame(() => {
                positionDropdown(dropdown, button);
            });
        }
    }

    function toggleMenu() {
        if (!navMenu) return;

        const open = navMenu.classList.contains('open');

        closeAll();

        if (!open) {
            navMenu.classList.add('open');

            if (menuToggle) {
                const icon = menuToggle.querySelector('i');

                if (icon) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                }

                menuToggle.setAttribute('aria-expanded', 'true');
            }
        }
    }

    if (addBtn) {
        addBtn.addEventListener('click', e => {
            e.stopPropagation();
            toggleDropdown(addDropdown, addBtn);
        });
    }

    if (notifBtn) {
        notifBtn.addEventListener('click', e => {
            e.stopPropagation();
            toggleDropdown(notifDropdown, notifBtn);
        });
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', e => {
            e.stopPropagation();
            toggleMenu();
        });
    }

    document.addEventListener('click', e => {
        const insideMenu = navMenu && navMenu.contains(e.target);
        const insideAdd = addBtn && addBtn.contains(e.target);
        const insideAddDrop = addDropdown && addDropdown.contains(e.target);
        const insideNotif = notifBtn && notifBtn.contains(e.target);
        const insideNotifDrop = notifDropdown && notifDropdown.contains(e.target);
        const insideToggle = menuToggle && menuToggle.contains(e.target);

        if (
            !insideMenu &&
            !insideAdd &&
            !insideAddDrop &&
            !insideNotif &&
            !insideNotifDrop &&
            !insideToggle
        ) {
            closeAll();
        }
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeAll();
        }
    });

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', closeAll);
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            if (addDropdown && addDropdown.classList.contains('show')) {
                positionDropdown(addDropdown, addBtn);
            }

            if (notifDropdown && notifDropdown.classList.contains('show')) {
                positionDropdown(notifDropdown, notifBtn);
            }
        } else {
            if (addDropdown) addDropdown.style.left = '';
            if (notifDropdown) notifDropdown.style.left = '';
        }
    });

    window.addEventListener('scroll', () => {
        if (window.innerWidth > 900) {
            if (addDropdown && addDropdown.classList.contains('show')) {
                positionDropdown(addDropdown, addBtn);
            }

            if (notifDropdown && notifDropdown.classList.contains('show')) {
                positionDropdown(notifDropdown, notifBtn);
            }
        }
    }, {
        passive: true
    });

    if (menuToggle) {
        menuToggle.setAttribute('aria-expanded', 'false');
    }
</script>