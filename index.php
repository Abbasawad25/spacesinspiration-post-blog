<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>مدونة فضاءات الإلهام</title>
<!-- Bootstrap RTL -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.rtl.min.css">
<!-- Boxicons (مستخدمة في rixpost) -->
<link href="https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
<!-- سمات القلم + rixpost -->
<link rel="stylesheet" href="css/qalam.css">
</head>
<body>

<!-- =============================================
     صفحات التطبيق
============================================= -->
<div id="app">

  <!-- ══════════════ HOME ══════════════ -->
  <div class="page active" id="page-home">
    <div class="page-header">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h1 style="font-family:'Amiri',serif;font-size:1.6rem;">مدونة فضاءات الإلهام</h1>
          <p class="sub">أفكار · قصص · إلهام</p>
          <div class="gold-rule"></div>
        </div>
        <!-- 3 أزرار السمات: داكن | فاتح | rixpost -->
        <div class="theme-switcher">
          <button class="theme-btn active-theme" data-theme="dark" onclick="applyTheme('dark')" title="داكن">🌙</button>
          <button class="theme-btn" data-theme="light" onclick="applyTheme('light')" title="فاتح">☀️</button>
          <button class="theme-btn" data-theme="rixpost" onclick="applyTheme('rixpost')" title="RixPost">🟣</button>
        </div>
      </div>
    </div>
    <div class="filter-tags">
      <button class="f-tag active" onclick="loadPosts('');setActive(this)">الكل</button>
      <button class="f-tag" onclick="loadPosts('tech');setActive(this)">💻 تقنية</button>
      <button class="f-tag" onclick="loadPosts('culture');setActive(this)">📚 ثقافة</button>
      <button class="f-tag" onclick="loadPosts('lifestyle');setActive(this)">🌿 أسلوب حياة</button>
      <button class="f-tag" onclick="loadPosts('quote');setActive(this)">💬 اقتباسات</button>
    </div>
    <div id="postsList"></div>
  </div>

  <!-- ══════════════ SEARCH ══════════════ -->
  <div class="page search-page" id="page-search">
    <div class="page-header">
      <h1 style="font-family:'Amiri',serif;font-size:1.5rem;">البحث</h1>
      <div class="gold-rule"></div>
    </div>
    <div class="search-bar">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" id="searchQ" placeholder="ابحث في المنشورات والكتّاب..." oninput="renderSearchResults(State.posts)">
    </div>
    <div class="filter-tags">
      <button class="f-tag active" onclick="setSearchTag('all',this)">الكل</button>
      <button class="f-tag" onclick="setSearchTag('tech',this)">تقنية</button>
      <button class="f-tag" onclick="setSearchTag('culture',this)">ثقافة</button>
      <button class="f-tag" onclick="setSearchTag('lifestyle',this)">أسلوب حياة</button>
      <button class="f-tag" onclick="setSearchTag('quote',this)">اقتباسات</button>
    </div>
    <div id="searchResults" style="padding:0 1.25rem;"></div>
  </div>

  <!-- ══════════════ SHARE ══════════════ -->
  <div class="page" id="page-share">
    <div class="page-header">
      <h1 style="font-family:'Amiri',serif;font-size:1.5rem;">مشاركة المنشور</h1>
      <div class="gold-rule"></div>
    </div>
    <div style="padding:0.5rem 1.25rem 2rem;">
      <div class="card" style="padding:1.2rem;margin-bottom:1rem;" id="sharePreview">
        <p style="color:var(--muted);font-size:0.85rem;text-align:center;padding:0.5rem 0;">اختر منشوراً لمشاركته 🔰</p>
      </div>

      <!-- أزرار المشاركة بنمط rixpost -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.65rem;margin-bottom:1rem;">
        <button onclick="shareOnPlatform('twitter')" class="share-platform-btn">
          <span style="width:34px;height:34px;background:#000;border-radius:8px;display:flex;align-items:center;justify-content:center;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="white"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.259 5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          </span>X (تويتر)
        </button>
        <button onclick="shareOnPlatform('whatsapp')" class="share-platform-btn">
          <span style="width:34px;height:34px;background:#25d366;border-radius:8px;display:flex;align-items:center;justify-content:center;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          </span>واتساب
        </button>
        <button onclick="shareOnPlatform('telegram')" class="share-platform-btn">
          <span style="width:34px;height:34px;background:#0088cc;border-radius:8px;display:flex;align-items:center;justify-content:center;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="white"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
          </span>تيليغرام
        </button>
        <button onclick="copyShareLink()" class="share-platform-btn">
          <span style="width:34px;height:34px;background:var(--surface3);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
          </span>نسخ الرابط
        </button>
      </div>

      <!-- نسخ النص (بنمط rixpost copy button) -->
      <div class="card" style="padding:1rem;margin-bottom:0.75rem;">
        <label style="font-size:0.73rem;color:var(--muted);display:block;margin-bottom:0.5rem;">📋 نص المنشور</label>
        <textarea id="shareTextarea" class="form-textarea" style="height:110px;font-size:0.8rem;" readonly placeholder="اختر منشوراً لعرض نصه هنا..."></textarea>
      </div>
      <button class="btn btn-primary btn-full" onclick="copyShareText()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        نسخ النص كاملاً
      </button>
    </div>
  </div>

  <!-- ══════════════ PROFILE ══════════════ -->
  <div class="page" id="page-profile">
    <div id="profileContent"><div class="loader"><div class="spinner"></div></div></div>
  </div>

  <!-- ══════════════ PUBLISHER ══════════════ -->
  <div class="page" id="publisherProfile">
    <div class="loader"><div class="spinner"></div></div>
  </div>

  <!-- ══════════════ ABOUT ══════════════ -->
  <div class="page" id="page-about">
    <div class="page-header">
      <h1 style="font-family:'Amiri',serif;font-size:1.5rem;">عن الموقع</h1>
      <p class="sub">ABOUT US</p>
      <div class="gold-rule"></div>
    </div>
    <div style="padding:0.5rem 1.25rem 2rem;" id="aboutContent">
      <div class="loader"><div class="spinner"></div></div>
    </div>
  </div>

  <!-- ══════════════ CONTACT ══════════════ -->
  <div class="page" id="page-contact">
    <div class="page-header">
      <h1 style="font-family:'Amiri',serif;font-size:1.5rem;">تواصل معنا</h1>
      <p class="sub">GET IN TOUCH</p>
      <div class="gold-rule"></div>
    </div>
    <div style="padding:0.5rem 1.25rem 2rem;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.65rem;margin-bottom:1.25rem;">
        <div class="card" style="padding:1rem;cursor:pointer;" onclick="navigator.clipboard.writeText('abbasawad@gmail.com').then(()=>toast('📧 تم نسخ البريد','gold'))">
          <div style="font-size:1.3rem;margin-bottom:0.3rem;">📧</div>
          <div style="font-size:0.68rem;color:var(--muted);">البريد الإلكتروني</div>
          <div style="font-size:0.77rem;font-weight:700;">abbasawad@gmail.com</div>
        </div>
        <div class="card" style="padding:1rem;cursor:pointer;" onclick="toast('🐦 @abbasawad25','gold')">
          <div style="font-size:1.3rem;margin-bottom:0.3rem;">🐦</div>
          <div style="font-size:0.68rem;color:var(--muted);">تويتر</div>
          <div style="font-size:0.77rem;font-weight:700;">@abbasawad25</div>
        </div>
        <div class="card" style="padding:1rem;cursor:pointer;" onclick="toast('💬 واتساب','gold')">
          <div style="font-size:1.3rem;margin-bottom:0.3rem;">💬</div>
          <div style="font-size:0.68rem;color:var(--muted);">واتساب</div>
          <div style="font-size:0.77rem;font-weight:700;">+249998812457</div>
        </div>
        <div class="card" style="padding:1rem;cursor:pointer;" onclick="toast('الخرطوم','gold')">
          <div style="font-size:1.3rem;margin-bottom:0.3rem;">📍</div>
          <div style="font-size:0.68rem;color:var(--muted);">الموقع</div>
          <div style="font-size:0.77rem;font-weight:700;">السودان</div>
        </div>
      </div>
      <div class="card" style="padding:1.25rem;">
        <div class="section-title" style="margin-bottom:1rem;">أرسل رسالة</div>
        <div class="form-group"><label class="form-label">الاسم الكامل</label><input class="form-input" id="cName" placeholder="اكتب اسمك..."></div>
        <div class="form-group"><label class="form-label">البريد الإلكتروني</label><input class="form-input" type="email" id="cEmail" placeholder="email@example.com" dir="ltr" style="text-align:left;"></div>
        <div class="form-group"><label class="form-label">الموضوع</label><input class="form-input" id="cSubject" placeholder="موضوع رسالتك..."></div>
        <div class="form-group"><label class="form-label">الرسالة</label><textarea class="form-textarea" id="cMessage" style="height:110px;" placeholder="اكتب رسالتك هنا..."></textarea></div>
        <button class="btn btn-primary btn-full" onclick="sendContact()">إرسال الرسالة 🔰</button>
      </div>
    </div>
  </div>

  <!-- ══════════════ ADMIN ══════════════ -->
  <div class="page" id="page-admin">
    <div class="page-header">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h1 style="font-family:'Amiri',serif;font-size:1.4rem;">🛡 لوحة الإدارة</h1>
          <div class="gold-rule"></div>
        </div>
      </div>
    </div>
    <div id="adminContent"><div class="loader"><div class="spinner"></div></div></div>
  </div>

</div><!-- /#app -->

<!-- ══════════════ BOTTOM NAV (TikTok style) ══════════════ -->
<nav class="bottom-nav">
  <button class="nav-btn" id="nav-profile" onclick="goTo('profile')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    الملف
  </button>
  <button class="nav-btn" id="nav-search" onclick="goTo('search')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    بحث
  </button>
  <button class="nav-btn active" id="nav-home" onclick="goTo('home')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
    المنشورات
  </button>
  <button class="nav-btn" id="nav-share" onclick="goTo('share')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
    مشاركة
  </button>
  <button class="nav-btn" id="nav-about" onclick="goTo('about')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    عن الموقع
  </button>
  <button class="nav-btn" id="nav-contact" onclick="goTo('contact')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    تواصل
  </button>
  <button class="nav-btn" id="nav-admin" onclick="goTo('admin')" style="display:none;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    إدارة
  </button>
</nav>

<!-- ══════════════ EDIT PROFILE MODAL ══════════════ -->
<div class="modal-overlay" id="editProfileModal" onclick="if(event.target===this)closeModal('editProfileModal')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">تعديل الملف الشخصي</span>
      <button class="modal-close" onclick="closeModal('editProfileModal')">×</button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">الاسم الكامل</label><input class="form-input" id="epName"></div>
      <div class="form-group"><label class="form-label">نبذة شخصية</label><textarea class="form-textarea" id="epBio" style="height:80px;"></textarea></div>
      <div class="form-group"><label class="form-label">البريد الإلكتروني</label><input class="form-input" type="email" id="epEmail" dir="ltr" style="text-align:left;"></div>
      <div class="form-group"><label class="form-label">كلمة مرور جديدة (اختياري)</label><input class="form-input" type="password" id="epPass" placeholder="اتركه فارغاً للإبقاء على نفس الكلمة"></div>
      <div style="display:flex;gap:0.5rem;margin-top:1rem;">
        <button class="btn btn-primary" style="flex:1;" onclick="saveProfile()">حفظ التغييرات</button>
        <button class="btn btn-outline" style="flex:1;" onclick="closeModal('editProfileModal')">إلغاء</button>
      </div>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="js/qalam.js"></script>
<script>
function setActive(btn) {
  document.querySelectorAll('.filter-tags .f-tag').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
</script>
</body>
</html>
