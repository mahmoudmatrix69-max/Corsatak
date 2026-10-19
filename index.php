<?php
require_once 'student/config.php';

$stmt = $conn->prepare("
    SELECT *
    FROM courses
    WHERE status = 1
    ORDER BY id DESC
");

$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>منصة كورساتك | منصة رقمية </title>
  <meta name="description" content="منصة كورساتك التعليمية تساعدك على اكتشاف الكورسات والمسارات المناسبة لك، والتعلم بمحتوى واضح ومنظم من الهاتف أو الكمبيوتر" />
  <meta name="robots" content="index, follow, max-image-preview:large" />
  <meta name="author" content="منصة كورساتك" />
  <meta name="theme-color" content="#2563eb" />
  <meta property="og:type" content="website" />
  <meta property="og:locale" content="ar_EG" />
  <meta property="og:site_name" content="منصة كورساتك" />
  <meta property="og:title" content="منصة كورساتك | كورسات ومسارات تعليمية أونلاين" />
  <meta property="og:description" content="اكتشف الكورسات والمسارات التعليمية المناسبة لك وابدأ رحلة التعلم" />
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:title" content="منصة كورساتك | كورسات ومسارات تعليمية أونلاين" />
  <meta name="twitter:description" content="اكتشف الكورسات والمسارات التعليمية المناسبة لك وابدأ رحلة التعلم" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <div class="top-bar">
    <i class="bi bi-rocket-takeoff"></i>
    <span>ابدأ رحلة تعلمك اليوم واكتشف الكورس المناسب لك</span>
  </div>

  <header class="header">
    <div class="header-inner">
      <a href="#home" class="logo" aria-label="منصتي التعليمية">
        <span class="logo-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span>منصة كورساتك</span>
      </a>

      <nav class="site-nav" id="siteNav" aria-label="التنقل الرئيسي">
        <a href="#home" class="active">الرئيسية</a>
        <a href="#courses">الكورسات</a>
        <a href="#paths">المسارات</a>
        <a href="#why">لماذا نحن</a>
        <a href="#faq">الأسئلة الشائعة</a>
        <a href="student/auth.php" class="mobile-login"><i class="bi bi-box-arrow-in-left"></i> تسجيل الدخول</a>
      </nav>

      <div class="header-actions">
        <button class="theme-btn" id="themeBtn" type="button" aria-label="تبديل الثيم">
          <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
        </button>

        <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-expanded="false"
          aria-controls="siteNav" aria-label="فتح القائمة">
          <i class="bi bi-list" aria-hidden="true"></i>
        </button>

        <a href="student/auth.php" class="login-btn">
          <i class="bi bi-box-arrow-in-left"></i>
          <span>تسجيل الدخول</span>
        </a>
      </div>
    </div>
  </header>

  <div class="scroll-progress" id="scrollProgress" aria-hidden="true">
    <span class="scroll-progress-bar"></span>
  </div>

  <div class="mobile-nav-backdrop" id="mobileNavBackdrop" aria-hidden="true"></div>

  <main>

    <section class="hero" id="home">
      <div class="hero-deco"></div>
      <div class="hero-deco2"></div>
      <div class="container">
        <div class="hero-content"><br><br>
          <div class="hero-badge">
            <i class="bi bi-lightning-charge-fill"></i> تعلم بشكل أفضل و اسرع
          </div>
          <h1 class="hero-title">
            <span>منصة كورساتك</span>
          </h1>
          <p class="hero-text">
            منصة تعليمية تساعدك على الوصول إلى محتوى واضح ومنظم،
            واختيار الكورسات المناسبة لك، والتعلم بطريقة مريحة
            من أي جهاز
          </p>
          <div class="hero-actions">
            <a href="#courses" class="btn-primary">
              اكتشف الكورسات <i class="bi bi-arrow-left"></i>
            </a>
            <a href="#why" class="btn-outline">لماذا نحن ؟</a>
          </div><br><br>
        </div>
      </div>

      <div class="waves-container">
        <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
          viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
          <defs>
            <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
          </defs>
          <g class="parallax">
            <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(255,255,255,0.7)" />
            <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(255,255,255,0.5)" />
            <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(255,255,255,0.3)" />
            <use xlink:href="#gentle-wave" x="48" y="7" fill="#fff" />
          </g>
        </svg>
      </div>
    </section>

    <section class="ad-banner">
      <div class="container">
        <div class="ad-card reveal">
          <div class="ad-shapes"></div>
          <div style="position:relative;z-index:2;">
            <div
              style="display:inline-flex;align-items:center;gap:10px;background:rgba(255,255,255,0.15);padding:8px 20px;border-radius:50px;font-size:13px;font-weight:800;margin-bottom:14px;">
              <i class="bi bi-megaphone-fill"></i> إعلان
            </div>
            <h2>ابدأ كورساتك اليوم بسرعة</h2>
            <p>اكتشف أحدث الكورسات المتاحة وابدأ في تطوير مهاراتك من خلال محتوى تعليمي واضح ومنظم</p>
            <a href="#courses" class="ad-btn">
              اكتشف الكورسات <i class="bi bi-arrow-left"></i>
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="section-padding" id="courses">
      <div class="container">
        <div class="courses-head reveal">
          <div class="section-label"><i class="bi bi-circle-fill"></i> الكورسات</div>
          <h2 class="section-title">اختار الكورس المناسب ليك</h2>
          <p class="section-desc">مجموعة من الكورسات المصممة بشكل واضح لتساعدك على التعلم والتطور خطوة بخطوة</p>
        </div>

        <div class="course-slider" id="courseSlider">
          <?php if (!empty($courses)): ?>
            <?php foreach ($courses as $course): ?>

              <article class="course-card">
                <div class="course-img">
                  <img
                    width="1200"
                    height="730"
                    decoding="async"
                    src="admin/assets/images/<?= htmlspecialchars($course['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($course['course_name'] ?? 'كورس تعليمي', ENT_QUOTES, 'UTF-8') ?>"
                    loading="lazy">
                </div>

                <div class="course-body">
                  <div class="course-category">
                    <?= htmlspecialchars($course['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </div>

                  <h3 class="course-title">
                    <?= htmlspecialchars($course['course_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </h3>

                  <div class="course-meta">
                    <span><i class="bi bi-calendar3"></i> 15 يناير 2026</span>
                    <span><i class="bi bi-people"></i> 1,240 مشترك</span>
                    <span><i class="bi bi-play-circle"></i> 24 درس</span>
                    <span><i class="bi bi-clock"></i> 8 ساعات</span>
                  </div>

                  <div class="course-footer">
                    <div class="course-price">
                      <small>السعر</small>
                      <strong>
                        <?= number_format((float) ($course['price'] ?? 0)) ?> ج.م
                      </strong>
                    </div>
                    
                    <div class="course-actions">
                    <a href="#" class="course-btn course-btn-enter">
                        تفاصيل الكورس <i class="bi bi-arrow-left"></i>
                      </a>
                      <a href="#" class="course-btn course-btn-buy">
                        <i class="bi bi-cart-plus"></i> شراء الكورس
                      </a>

                    </div>
                  </div>
                </div>
              </article>

            <?php endforeach; ?>
          <?php else: ?>

            <p>لا توجد كورسات متاحة حاليًا</p>

          <?php endif; ?>
        </div>

        <div class="slider-controls">
          <button class="slider-arrow" id="courseRight" aria-label="السابق">
            <i class="bi bi-chevron-right"></i>
          </button>
          <button class="slider-arrow" id="courseLeft" aria-label="التالي">
            <i class="bi bi-chevron-left"></i>
          </button>
        </div>
      </div>
    </section>

    <section class="section-padding" id="paths">
      <div class="container">
        <div class="reveal">
          <div class="section-label"><i class="bi bi-circle-fill"></i> المسارات</div>
          <h2 class="section-title">تعلم بخطة واضحة</h2>
          <p class="section-desc">بدل ما تتعلم بشكل عشوائي، اختار المسار المناسب وابدأ خطوة بخطوة</p>
        </div>
        <div class="paths-grid">
          <article class="path-item reveal">
            <div class="path-icon"><i class="bi bi-compass"></i></div>
            <h3>البداية</h3>
            <p>ابدأ من الأساسيات وابني قاعدة معرفية قوية تساعدك على الاستمرار</p>
          </article>
          <article class="path-item reveal">
            <div class="path-icon"><i class="bi bi-lightning-charge"></i></div>
            <h3>التطور</h3>
            <p>طبق ما تتعلمه وطوّر مهاراتك تدريجيًا من خلال الممارسة والتعلم المستمر</p>
          </article>
          <article class="path-item reveal">
            <div class="path-icon"><i class="bi bi-trophy"></i></div>
            <h3>الاحتراف</h3>
            <p>انتقل للمستويات المتقدمة واستمر في تطوير قدراتك وتحقيق أهدافك</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section-padding" id="why">
      <div class="container">
        <div class="why-grid">
          <div class="why-visual">
            <div class="why-ring"></div>
            <div class="why-ring two"></div>
            <div class="why-center" aria-hidden="true">
              <i class="bi bi-mortarboard-fill"></i>
            </div>
          </div>
          <div class="reveal">
            <div class="section-label"><i class="bi bi-circle-fill"></i> لماذا نحن ؟</div>
            <h2 class="section-title">تجربة تعليمية مريحة</h2>
            <p class="section-desc">صممنا المنصة بحيث يكون الوصول للمحتوى واختيار الكورسات واستكمال التعلم أمرًا بسيطًا
            </p>
            <div class="why-list">
              <div class="why-item">
                <div class="why-item-icon"><i class="bi bi-grid"></i></div>
                <div>
                  <h4>محتوى منظم</h4>
                  <p>كل كورس مرتب بطريقة واضحة تساعدك على التعلم بدون تشتت</p>
                </div>
              </div>
              <div class="why-item">
                <div class="why-item-icon"><i class="bi bi-phone"></i></div>
                <div>
                  <h4>يعمل على كل الأجهزة</h4>
                  <p>تعلم من الهاتف أو التابلت أو الكمبيوتر بسهولة</p>
                </div>
              </div>
              <div class="why-item">
                <div class="why-item-icon"><i class="bi bi-clock"></i></div>
                <div>
                  <h4>تعلم في الوقت المناسب</h4>
                  <p>ارجع للمحتوى واستكمل تعلمك عندما يناسبك</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section-padding" id="faq">
      <div class="container">
        <div class="faq-layout">
          <div class="faq-content reveal">
            <div class="section-label"><i class="bi bi-circle-fill"></i> الأسئلة الشائعة</div>
            <h2 class="section-title">عندك سؤال؟</h2>
            <p class="section-desc">جمعنا لك أهم الأسئلة التي قد تحتاج إلى معرفة إجابتها قبل بدء التعلم</p>
            <div class="accordion-custom">
              <div class="accordion-item">
                <button class="accordion-header collapsed" aria-expanded="false" data-target="faq1">
                  هل أقدر أتعلم من الهاتف ؟
                  <i class="bi bi-chevron-down"></i>
                </button>
                <div class="accordion-body" id="faq1">نعم، المنصة متوافقة مع الهاتف والتابلت والكمبيوتر</div>
              </div>
              <div class="accordion-item">
                <button class="accordion-header collapsed" aria-expanded="false" data-target="faq2">
                  هل أقدر أرجع للكورس بعد الشراء ؟
                  <i class="bi bi-chevron-down"></i>
                </button>
                <div class="accordion-body" id="faq2">يمكنك الوصول إلى الكورس من حسابك واستكمال التعلم من المكان الذي
                  توقفت عنده</div>
              </div>
              <div class="accordion-item">
                <button class="accordion-header collapsed" aria-expanded="false" data-target="faq3">
                  هل الكورسات مناسبة للمبتدئين ؟
                  <i class="bi bi-chevron-down"></i>
                </button>
                <div class="accordion-body" id="faq3">يوجد محتوى يبدأ من الأساسيات، ويمكنك اختيار المستوى المناسب لكل
                  كورس</div>
              </div>
            </div>
          </div>
          <div class="faq-visual">
            <div class="faq-orbit"></div>
            <div class="faq-orbit"></div>
            <div class="faq-orbit"></div>
            <div class="faq-center">
              <i class="bi bi-question-lg"></i>
              <span>اسألنا</span>
            </div>
            <div class="faq-floating one"><i class="bi bi-chat-dots"></i></div>
            <div class="faq-floating two"><i class="bi bi-lightbulb"></i></div>
            <div class="faq-floating three"><i class="bi bi-info-circle"></i></div>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="container">
        <div class="cta-box reveal">
          <h2>هل انت مستعد لخوض التجربة</h2>
          <p>اختار الكورس المناسب وابدأ أول خطوة في رحلة التعلم الخاصة بك</p>
          <a href="#courses" class="cta-btn">
            استكشف الكورسات <i class="bi bi-arrow-left"></i>
          </a>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-layout">
        <div class="footer-brand">
          <a href="#home" class="logo" aria-label="منصتي التعليمية">
            <span class="logo-icon"><i class="bi bi-mortarboard-fill"></i></span>
            <span>منصة كورساتك</span>
          </a>
          <p>منصة تعليمية تساعدك على الوصول إلى محتوى واضح ومنظم وتجربة تعلم مريحة</p>
        </div>

        <div class="footer-social">
          <strong>تابعنا على</strong>
          <span>كن على تواصل معنا عبر منصات التواصل</span>
          <div class="social-links">
            <a href="#" aria-label="فيسبوك"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="انستغرام"><i class="bi bi-instagram"></i></a>
            <a href="#" aria-label="يوتيوب"><i class="bi bi-youtube"></i></a>
            <a href="#" aria-label="تلغرام"><i class="bi bi-telegram"></i></a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <span>
          © 2026 منصة كورساتك صممت بواسطة
          <a href="#" class="developer-name">محمود حسين</a>
        </span>
        <span>جميع الحقوق محفوظة لمنصة كورساتك</span>
      </div>
    </div>
  </footer>

  <button class="back-top" id="backTop" aria-label="العودة للأعلى">
    <i class="bi bi-arrow-up"></i>
  </button>

  <script>
    (function() {
      "use strict";

      const root = document.documentElement;
      const themeBtn = document.getElementById('themeBtn');
      const themeIcon = document.getElementById('themeIcon');

      const savedTheme = localStorage.getItem('theme');
      const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      const initialTheme = savedTheme === 'dark' ? 'dark' :
        savedTheme === 'light' ? 'light' :
        (systemDark ? 'dark' : 'light');

      function setTheme(theme) {
        const isDark = theme === 'dark';
        root.classList.toggle('dark', isDark);
        themeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
      }

      setTheme(initialTheme);

      themeBtn.addEventListener('click', function() {
        setTheme(root.classList.contains('dark') ? 'light' : 'dark');
      });

      const mobileMenuToggle = document.getElementById('mobileMenuToggle');
      const siteNav = document.getElementById('siteNav');
      const mobileNavBackdrop = document.getElementById('mobileNavBackdrop');

      function setMobileMenu(open) {
        if (!mobileMenuToggle || !siteNav) return;

        siteNav.classList.toggle('is-open', open);
        document.body.classList.toggle('mobile-nav-open', open);
        document.body.classList.toggle('menu-is-open', open);

        if (mobileNavBackdrop) {
          mobileNavBackdrop.setAttribute('aria-hidden', String(!open));
        }

        mobileMenuToggle.setAttribute('aria-expanded', String(open));
        mobileMenuToggle.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');

        const icon = mobileMenuToggle.querySelector('i');
        if (icon) icon.className = open ? 'bi bi-x-lg' : 'bi bi-list';
      }

      if (mobileMenuToggle && siteNav) {
        mobileMenuToggle.addEventListener('click', function(event) {
          event.preventDefault();
          event.stopPropagation();
          setMobileMenu(!siteNav.classList.contains('is-open'));
        });

        siteNav.querySelectorAll('a').forEach(function(link) {
          link.addEventListener('click', function() {
            setMobileMenu(false);
          });
        });

        if (mobileNavBackdrop) {
          mobileNavBackdrop.addEventListener('click', function() {
            setMobileMenu(false);
          });
        }

        document.addEventListener('click', function(event) {
          if (!siteNav.classList.contains('is-open')) return;
          if (!siteNav.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
            setMobileMenu(false);
          }
        });

        document.addEventListener('keydown', function(event) {
          if (event.key === 'Escape') setMobileMenu(false);
        });

        window.addEventListener('resize', function() {
          if (window.innerWidth > 991) setMobileMenu(false);
        });
      }

      const topBar = document.querySelector('.top-bar');
      const backTop = document.getElementById('backTop');
      const scrollProgress = document.getElementById('scrollProgress');
      const scrollProgressBar = scrollProgress?.querySelector('.scroll-progress-bar');
      let scrollTicking = false;

      function updateScrollUI() {
        const currentScrollY = window.scrollY;

        if (scrollProgressBar) {
          const documentHeight = document.documentElement.scrollHeight - window.innerHeight;
          const progress = documentHeight > 0 ?
            Math.min(1, Math.max(0, currentScrollY / documentHeight)) :
            0;
          scrollProgressBar.style.transform = 'scaleX(' + progress.toFixed(4) + ')';
        }

        if (topBar) {
          const topBarHidden = currentScrollY > 24;
          topBar.classList.toggle('is-hidden', topBarHidden);
          document.body.classList.toggle('topbar-hidden', topBarHidden);

          if (topBarHidden && siteNav && siteNav.classList.contains('is-open')) {
            setMobileMenu(false);
          }
        }

        if (backTop) {
          backTop.classList.toggle('show', currentScrollY > 450);
        }

        scrollTicking = false;
      }

      function requestScrollUI() {
        if (scrollTicking) return;
        scrollTicking = true;
        requestAnimationFrame(updateScrollUI);
      }

      window.addEventListener('scroll', requestScrollUI, {
        passive: true
      });
      updateScrollUI();

      function setupSlider(sliderId, rightId, leftId) {
        const slider = document.getElementById(sliderId);
        const rightButton = document.getElementById(rightId);
        const leftButton = document.getElementById(leftId);
        if (!slider) return;

        const cards = Array.from(slider.children).filter(card =>
          card.classList.contains('course-card')
        );

        if (!cards.length) {
          slider.hidden = true;
          if (rightButton) rightButton.hidden = true;
          if (leftButton) leftButton.hidden = true;
          return;
        }

        let activeIndex = 0;
        let pointerDown = false;
        let dragMoved = false;
        let startX = 0;
        let startY = 0;
        let startScroll = 0;

        function nearestCardIndex() {
          const sliderRect = slider.getBoundingClientRect();
          const sliderCenter = sliderRect.left + sliderRect.width / 2;
          let nearest = 0;
          let distance = Infinity;
          cards.forEach((card, index) => {
            const rect = card.getBoundingClientRect();
            const center = rect.left + rect.width / 2;
            const currentDistance = Math.abs(sliderCenter - center);
            if (currentDistance < distance) {
              distance = currentDistance;
              nearest = index;
            }
          });
          return nearest;
        }

        function updateControls() {
          activeIndex = nearestCardIndex();
          const hasOverflow = slider.scrollWidth > slider.clientWidth + 2;
          if (rightButton) {
            rightButton.hidden = !hasOverflow;
            rightButton.disabled = !hasOverflow || activeIndex <= 0;
            rightButton.setAttribute('aria-disabled', String(rightButton.disabled));
          }
          if (leftButton) {
            leftButton.hidden = !hasOverflow;
            leftButton.disabled = !hasOverflow || activeIndex >= cards.length - 1;
            leftButton.setAttribute('aria-disabled', String(leftButton.disabled));
          }
        }

        function goTo(index) {
          if (!cards.length) return;
          const nextIndex = Math.max(0, Math.min(index, cards.length - 1));
          cards[nextIndex].scrollIntoView({
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            block: 'nearest',
            inline: 'nearest'
          });
          activeIndex = nextIndex;
          window.setTimeout(updateControls, 350);
        }

        rightButton?.addEventListener('click', () => goTo(nearestCardIndex() - 1));
        leftButton?.addEventListener('click', () => goTo(nearestCardIndex() + 1));
        slider.addEventListener('scroll', updateControls, {
          passive: true
        });

        slider.addEventListener('pointerdown', event => {
          if (event.pointerType !== 'mouse' || event.button !== 0 || event.target.closest('a,button,input')) return;
          pointerDown = true;
          dragMoved = false;
          startX = event.clientX;
          startY = event.clientY;
          startScroll = slider.scrollLeft;
          slider.classList.add('dragging');
        });

        slider.addEventListener('pointermove', event => {
          if (!pointerDown) return;
          const dx = event.clientX - startX;
          const dy = event.clientY - startY;
          if (!dragMoved && Math.abs(dy) > 8 && Math.abs(dy) > Math.abs(dx)) {
            pointerDown = false;
            slider.classList.remove('dragging');
            return;
          }
          if (Math.abs(dx) > 5) dragMoved = true;
          if (dragMoved) {
            event.preventDefault();
            slider.scrollLeft = startScroll - dx;
          }
        });

        function endPointer() {
          if (!pointerDown) return;
          pointerDown = false;
          slider.classList.remove('dragging');
          window.setTimeout(() => {
            dragMoved = false;
          }, 0);
          updateControls();
        }
        slider.addEventListener('pointerup', endPointer);
        slider.addEventListener('pointercancel', endPointer);
        slider.addEventListener('pointerleave', endPointer);
        slider.addEventListener('click', event => {
          if (dragMoved) {
            event.preventDefault();
            event.stopPropagation();
            dragMoved = false;
          }
        }, true);
        slider.addEventListener('dragstart', event => event.preventDefault());

        if ('ResizeObserver' in window) {
          new ResizeObserver(updateControls).observe(slider);
        } else {
          window.addEventListener('resize', updateControls);
        }
        updateControls();
      }

      setupSlider('courseSlider', 'courseRight', 'courseLeft');

      document.querySelectorAll('.accordion-header').forEach(function(btn) {
        btn.addEventListener('click', function() {
          const target = document.getElementById(this.getAttribute('data-target'));
          if (!target) return;
          const wasOpen = target.classList.contains('open');
          document.querySelectorAll('.accordion-body').forEach(function(b) {
            b.classList.remove('open');
          });
          document.querySelectorAll('.accordion-header').forEach(function(h) {
            h.classList.add('collapsed');
            h.setAttribute('aria-expanded', 'false');
          });
          if (!wasOpen) {
            target.classList.add('open');
            this.classList.remove('collapsed');
            this.setAttribute('aria-expanded', 'true');
          }
        });
        btn.setAttribute('aria-expanded', btn.classList.contains('collapsed') ? 'false' : 'true');
      });

      const revealElements = document.querySelectorAll('.reveal');
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function(entries) {
          entries.forEach(function(entry) {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('show');
            observer.unobserve(entry.target);
          });
        }, {
          threshold: 0.04,
          rootMargin: '0px 0px -8% 0px'
        });
        revealElements.forEach(function(el) {
          observer.observe(el);
        });
      } else {
        revealElements.forEach(function(el) {
          el.classList.add('show');
        });
      }

      backTop?.addEventListener('click', function() {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });

      const sections = Array.from(document.querySelectorAll('main section[id]'));
      const navLinks = Array.from(document.querySelectorAll('#siteNav a[href^="#"]')).filter(function(link) {
        return link.getAttribute('href') !== '#';
      });
      if ('IntersectionObserver' in window && sections.length && navLinks.length) {
        const observer = new IntersectionObserver(function(entries) {
          entries.forEach(function(entry) {
            if (!entry.isIntersecting) return;
            const active = navLinks.find(function(link) {
              return link.getAttribute('href') === '#' + entry.target.id;
            });
            if (!active || active.classList.contains('active')) return;
            navLinks.forEach(function(link) {
              link.classList.remove('active');
            });
            active.classList.add('active');
          });
        }, {
          rootMargin: '-35% 0px -55% 0px'
        });
        sections.forEach(function(s) {
          observer.observe(s);
        });
      }
    })();
  </script>
</body>

</html>