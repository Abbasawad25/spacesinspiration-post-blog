// =============================================
//  qalam.js — الكود الرئيسي (PHP API + سمات rixpost)
// =============================================

const API_BASE = 'php/api.php?path=';

const State = {
  user: null,
  posts: [],
  currentPage: 'home',
  theme: localStorage.getItem('qalam_theme') || 'dark',
  searchTag: 'all',
  currentPostShare: null,
  sidebarOpen: false,
};

// =============================================
//  UTILITIES
// =============================================
async function api(path, opts = {}) {
  const url = API_BASE + path;
  const fullUrl = opts.fullPath ? path : url;
  try {
    const res = await fetch(fullUrl, {
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', ...(opts.headers || {}) },
      ...opts,
    });
    return await res.json();
  } catch(e) {
    console.error('API error:', e);
    return { error: 'خطأ في الاتصال' };
  }
}

function $(sel, ctx = document) { return ctx.querySelector(sel); }
function $$(sel, ctx = document) { return [...ctx.querySelectorAll(sel)]; }
function el(tag, cls = '', inner = '') {
  const e = document.createElement(tag);
  if (cls) e.className = cls;
  if (inner) e.innerHTML = inner;
  return e;
}
function h(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

let _toastTimer;
function toast(msg, type = '') {
  const t = $('#toast');
  t.textContent = msg;
  t.className = 'toast show ' + type;
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.className = 'toast', 2800);
}

function showLoader(container) {
  container.innerHTML = `<div class="loader"><div class="spinner"></div><span class="loader-text">جار التحميل...</span></div>`;
}

function avatarHTML(name, avatarUrl, size = 38) {
  const style = `width:${size}px;height:${size}px;`;
  const initial = (name || '?')[0];
  if (avatarUrl) {
    return `<div class="author-ava" style="${style}"><img src="${avatarUrl}" alt="${h(name)}" loading="lazy"></div>`;
  }
  return `<div class="author-ava" style="${style};font-size:${Math.round(size*0.38)}px;">${initial}</div>`;
}

// =============================================
//  THEME — 3 سمات
// =============================================
function applyTheme(t) {
  State.theme = t;
  document.documentElement.setAttribute('data-theme', t);
  localStorage.setItem('qalam_theme', t);
  // تحديث الأزرار
  $$('.theme-btn').forEach(b => {
    b.classList.toggle('active-theme', b.dataset.theme === t);
  });
}

// =============================================
//  NAVIGATION
// =============================================
function goTo(page) {
  $$('.page').forEach(p => p.classList.remove('active'));
  $$('.nav-btn').forEach(b => b.classList.remove('active'));
  const pageEl = $(`#page-${page}`);
  const navEl  = $(`#nav-${page}`);
  if (pageEl) pageEl.classList.add('active');
  if (navEl)  navEl.classList.add('active');
  State.currentPage = page;

  const loaders = {
    home:      loadPosts,
    search:    initSearch,
    profile:   loadProfile,
    admin:     loadAdmin,
    about:     renderAbout,
    share:     renderSharePage,
    contact:   () => {},
  };
  if (loaders[page]) loaders[page]();
}

// =============================================
//  POSTS — جلب من PHP API
// =============================================
async function loadPosts(cat = '', q = '') {
  const list = $('#postsList');
  showLoader(list);
  let params = 'posts';
  const p = new URLSearchParams({ page: 1 });
  if (cat) p.set('category', cat);
  if (q)   p.set('q', q);
  const data = await api(params + '&' + p.toString());
  State.posts = data.posts || [];
  renderPosts(State.posts, list);
}

function renderPosts(posts, container) {
  if (!container) return;
  if (!posts.length) {
    container.innerHTML = '<div class="empty-state font-serif">✦ لا توجد منشورات في هذا القسم</div>';
    return;
  }
  container.innerHTML = '';
  posts.forEach((p, i) => container.appendChild(createPostCard(p, i)));
}

function createPostCard(p, delay = 0) {
  const card = el('div', 'post-card card mb-2');
  card.style.animationDelay = (delay * 0.05) + 's';
  card.style.margin = '0 1.25rem 1rem';

  const isQuote = p.category === 'quote';
  const bodyClass = isQuote ? 'post-body quote-style' : 'post-body collapsed';

  card.innerHTML = `
    <div class="post-inner">
      <div class="post-top" onclick="goToPublisher(${h(p.author_id)})">
        ${avatarHTML(p.author_name, p.author_avatar_url, 38)}
        <div style="flex:1">
          <div class="author-name">${h(p.author_name || 'مجهول')}</div>
          <div class="post-date">@${h(p.author_username || '')} · ${h(p.created_ago || '')}</div>
        </div>
        <span class="cat-badge ${h(p.cat_class || 'cat-tech')}">${h(p.cat_label || p.category)}</span>
      </div>
      ${!isQuote ? `<div class="post-title">${h(p.title)}</div>` : ''}
      <div class="${bodyClass}" id="pb-${p.id}">${h(p.body)}</div>
      ${!isQuote ? `<button class="read-more" onclick="toggleRead(${p.id},this)">اقرأ المزيد ▾</button>` : ''}
      <div class="post-stats">
        <span class="stat">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          <span id="lc-${p.id}">${p.likes_count || 0}</span>
        </span>
        <span class="stat">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          ${p.views_count || 0}
        </span>
      </div>
      <div class="post-actions">
        <button class="act-btn ${p.liked ? 'liked' : ''}" id="like-${p.id}" onclick="toggleLike(${p.id},this)">
          <svg viewBox="0 0 24 24" fill="${p.liked ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          إعجاب
        </button>
        <button class="act-btn rix-copy-inline" onclick="copyPostText(${p.id})">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          نسخ
        </button>
        <div class="share-wrap">
          <button class="act-btn" onclick="toggleShareDD(${p.id},this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            مشاركة
          </button>
          <div class="share-dd" id="sdd-${p.id}">
            <button class="share-opt" onclick="sharePost(${p.id},'twitter')">
              <span class="s-icon si-tw"><svg width="13" height="13" viewBox="0 0 24 24" fill="white"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.259 5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></span>
              X (تويتر)
            </button>
            <button class="share-opt" onclick="sharePost(${p.id},'whatsapp')">
              <span class="s-icon si-wa"><svg width="13" height="13" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></span>
              واتساب
            </button>
            <button class="share-opt" onclick="sharePost(${p.id},'telegram')">
              <span class="s-icon si-tg"><svg width="13" height="13" viewBox="0 0 24 24" fill="white"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg></span>
              تيليغرام
            </button>
            <button class="share-opt" onclick="sharePost(${p.id},'copy')">
              <span class="s-icon si-lk">🔗</span>
              نسخ الرابط
            </button>
          </div>
        </div>
      </div>
    </div>`;
  return card;
}

function toggleRead(id, btn) {
  const pb = $(`#pb-${id}`);
  if (!pb) return;
  pb.classList.toggle('collapsed');
  btn.textContent = pb.classList.contains('collapsed') ? 'اقرأ المزيد ▾' : 'أخفِ ▴';
}

// =============================================
//  LIKE
// =============================================
async function toggleLike(id, btn) {
  if (!State.user) { toast('يجب تسجيل الدخول أولاً', 'error'); goTo('profile'); return; }
  btn.disabled = true;
  const data = await api(`posts/${id}/like`, { method:'POST' });
  const lc = $(`#lc-${id}`);
  if (lc && data.count !== undefined) lc.textContent = data.count;
  btn.classList.toggle('liked', data.liked);
  const svgPath = btn.querySelector('svg path');
  if (svgPath) svgPath.setAttribute('fill', data.liked ? 'currentColor' : 'none');
  btn.disabled = false;
}

// =============================================
//  COPY — نسخ نص المنشور (بنمط rixpost)
// =============================================
function copyPostText(id) {
  const p = State.posts.find(x => x.id === id);
  if (!p) return;
  const text = (p.title ? p.title + '\n\n' : '') + p.body;
  navigator.clipboard.writeText(text).then(() => {
    toast('✅ تم نسخ المنشور!', 'success');
  }).catch(() => {
    // fallback
    const ta = document.createElement('textarea');
    ta.value = text;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    ta.remove();
    toast('✅ تم النسخ!', 'success');
  });
}

// =============================================
//  SHARE
// =============================================
function toggleShareDD(id, btn) {
  const dd = $(`#sdd-${id}`);
  if (!dd) return;
  // أغلق كل dropdowns أخرى
  $$('.share-dd.open').forEach(d => { if (d !== dd) d.classList.remove('open'); });
  dd.classList.toggle('open');
  const p = State.posts.find(x => x.id === id);
  if (p) { State.currentPostShare = p; updateSharePage(p); }
}

function sharePost(id, platform) {
  const p = State.posts.find(x => x.id === id) || State.currentPostShare;
  if (!p) return;
  $$('.share-dd').forEach(d => d.classList.remove('open'));
  const url  = encodeURIComponent(window.location.origin + '/qalam/?post=' + id);
  const text = encodeURIComponent((p.title || '') + ' — مدوّنة القلم');
  const links = {
    twitter:  `https://twitter.com/intent/tweet?url=${url}&text=${text}`,
    whatsapp: `https://wa.me/?text=${text}%20${url}`,
    telegram: `https://t.me/share/url?url=${url}&text=${text}`,
    copy:     null,
  };
  if (platform === 'copy') {
    const rawUrl = decodeURIComponent(url);
    navigator.clipboard.writeText(rawUrl).then(() => toast('🔗 تم نسخ الرابط!', 'gold'));
  } else if (links[platform]) {
    window.open(links[platform], '_blank', 'width=600,height=400');
  }
}

function updateSharePage(p) {
  const preview = $('#sharePreview');
  const textarea = $('#shareTextarea');
  if (preview)  preview.innerHTML = `<strong class="font-serif">${h(p.title || '')}</strong><p class="text-muted text-sm mt-1">${h((p.body||'').substring(0,180))}...</p>`;
  if (textarea) textarea.value = (p.title ? p.title + '\n\n' : '') + p.body;
}

function renderSharePage() {
  if (State.currentPostShare) updateSharePage(State.currentPostShare);
}

function shareOnPlatform(pl) {
  if (!State.currentPostShare) { toast('اختر منشوراً أولاً', 'error'); return; }
  sharePost(State.currentPostShare.id, pl);
}

function copyShareLink() {
  if (!State.currentPostShare) { toast('اختر منشوراً أولاً', 'error'); return; }
  const url = window.location.origin + '/qalam/?post=' + State.currentPostShare.id;
  navigator.clipboard.writeText(url).then(() => toast('🔗 تم نسخ الرابط!', 'gold'));
}

function copyShareText() {
  const ta = $('#shareTextarea');
  if (!ta || !ta.value) { toast('اختر منشوراً أولاً', 'error'); return; }
  navigator.clipboard.writeText(ta.value).then(() => toast('📋 تم نسخ النص!', 'success'));
}

// =============================================
//  SEARCH
// =============================================
function initSearch() {
  renderSearchResults(State.posts);
}

function setSearchTag(tag, btn) {
  State.searchTag = tag;
  $$('.search-page .f-tag').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderSearchResults(State.posts);
}

function renderSearchResults(posts) {
  const q   = ($('#searchQ')?.value || '').toLowerCase().trim();
  const tag = State.searchTag;
  let filtered = posts;
  if (tag && tag !== 'all') filtered = filtered.filter(p => p.category === tag);
  if (q) filtered = filtered.filter(p =>
    (p.title || '').toLowerCase().includes(q) ||
    (p.body  || '').toLowerCase().includes(q) ||
    (p.author_name || '').toLowerCase().includes(q)
  );
  const c = $('#searchResults');
  if (!c) return;
  if (!filtered.length) { c.innerHTML = '<div class="empty-state font-serif">لا توجد نتائج مطابقة</div>'; return; }
  c.innerHTML = '';
  filtered.forEach((p, i) => c.appendChild(createPostCard(p, i)));
}

// =============================================
//  PROFILE
// =============================================
async function loadProfile() {
  const c = $('#profileContent');
  showLoader(c);
  const data = await api('auth/me');
  State.user = data.user;
  if (!data.user) {
    c.innerHTML = renderLoginForm();
    return;
  }
  c.innerHTML = renderProfileUI(data.user);
}

function renderLoginForm() {
  return `
  <div style="padding:1.5rem 1.25rem">
    <div class="card" style="padding:1.5rem">
      <h2 class="font-serif" style="font-size:1.3rem;margin-bottom:1rem;">تسجيل الدخول</h2>
      <div class="form-group"><label class="form-label">البريد الإلكتروني</label>
        <input class="form-input" type="email" id="loginEmail" placeholder="email@example.com" dir="ltr" style="text-align:left"></div>
      <div class="form-group"><label class="form-label">كلمة المرور</label>
        <input class="form-input" type="password" id="loginPass" placeholder="••••••••"></div>
      <button class="btn btn-primary btn-full" onclick="doLogin()">دخول ✦</button>
      <div style="text-align:center;margin-top:1rem">
        <button class="btn btn-outline" style="width:100%" onclick="showRegister()">إنشاء حساب جديد</button>
      </div>
    </div>
  </div>`;
}

function showRegister() {
  const c = $('#profileContent');
  c.innerHTML = `
  <div style="padding:1.5rem 1.25rem">
    <div class="card" style="padding:1.5rem">
      <h2 class="font-serif" style="font-size:1.3rem;margin-bottom:1rem;">إنشاء حساب</h2>
      <div class="form-group"><label class="form-label">الاسم الكامل</label><input class="form-input" id="regName" placeholder="اسمك الكامل"></div>
      <div class="form-group"><label class="form-label">اسم المستخدم</label><input class="form-input" id="regUser" placeholder="username" dir="ltr" style="text-align:left"></div>
      <div class="form-group"><label class="form-label">البريد الإلكتروني</label><input class="form-input" type="email" id="regEmail" dir="ltr" style="text-align:left"></div>
      <div class="form-group"><label class="form-label">كلمة المرور</label><input class="form-input" type="password" id="regPass"></div>
      <button class="btn btn-primary btn-full" onclick="doRegister()">تسجيل ✦</button>
      <div style="text-align:center;margin-top:1rem">
        <button class="btn btn-outline" onclick="loadProfile()">لديّ حساب → دخول</button>
      </div>
    </div>
  </div>`;
}

async function doLogin() {
  const email = $('#loginEmail')?.value.trim();
  const pass  = $('#loginPass')?.value;
  if (!email || !pass) { toast('أدخل البريد وكلمة المرور', 'error'); return; }
  const data = await api('auth/login', { method:'POST', body: JSON.stringify({email, password: pass}) });
  if (data.error) { toast(data.error, 'error'); return; }
  State.user = data.user;
  toast('مرحباً ' + data.user.full_name + ' ✦', 'gold');
  if (data.user.role === 'admin') $(`#nav-admin`).style.display = '';
  loadProfile();
}

async function doRegister() {
  const body = {
    full_name: $('#regName')?.value.trim(),
    username:  $('#regUser')?.value.trim(),
    email:     $('#regEmail')?.value.trim(),
    password:  $('#regPass')?.value,
  };
  if (!body.full_name || !body.username || !body.email) { toast('أكمل جميع الحقول', 'error'); return; }
  const data = await api('auth/register', { method:'POST', body: JSON.stringify(body) });
  if (data.error) { toast(data.error, 'error'); return; }
  toast('تم إنشاء الحساب بنجاح ✦', 'success');
  loadProfile();
}

async function doLogout() {
  await api('auth/logout', { method:'POST' });
  State.user = null;
  $(`#nav-admin`).style.display = 'none';
  toast('تم تسجيل الخروج', '');
  goTo('home');
}

function renderProfileUI(u) {
  return `
  <div class="cover"></div>
  <div style="padding:0 1.25rem 1.5rem">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-top:-40px;margin-bottom:0.75rem">
      ${avatarHTML(u.full_name, u.avatar_url, 80)}
      <div style="display:flex;gap:0.5rem">
        <button class="btn btn-sm btn-outline" onclick="openEditModal()">تعديل</button>
        <button class="btn btn-sm btn-danger" onclick="doLogout()">خروج</button>
      </div>
    </div>
    <div style="font-family:'Amiri',serif;font-size:1.3rem;font-weight:700">${h(u.full_name)}</div>
    <div style="color:var(--muted);font-size:0.8rem;margin:2px 0">@${h(u.username)}</div>
    ${u.bio ? `<div style="font-size:0.85rem;color:var(--text2);margin:0.5rem 0">${h(u.bio)}</div>` : ''}
    <div style="display:flex;gap:1.5rem;margin:0.75rem 0;font-size:0.8rem">
      <span><strong style="color:var(--accent)">${u.followers_count || 0}</strong> <span style="color:var(--muted)">متابع</span></span>
      <span><strong style="color:var(--accent)">${u.following_count || 0}</strong> <span style="color:var(--muted)">يتابع</span></span>
    </div>
    <div class="divider"></div>
    <div class="section-title" style="margin-top:1rem">منشوراتي</div>
    <div id="myPostsList"><div class="loader"><div class="spinner"></div></div></div>
  </div>`;
}

// =============================================
//  PUBLISHER PROFILE
// =============================================
async function goToPublisher(userId) {
  if (!userId) return;
  $$('.page').forEach(p => p.classList.remove('active'));
  $$('.nav-btn').forEach(b => b.classList.remove('active'));
  const pp = $('#publisherProfile');
  if (!pp) return;
  pp.classList.add('active');
  showLoader(pp);
  const data = await api('users/' + userId);
  if (data.error) { toast(data.error, 'error'); goTo('home'); return; }
  pp.innerHTML = renderPublisherUI(data);
}

function renderPublisherUI(u) {
  const postsHtml = (u.posts || []).map(p => `
  <div class="card mb-2" style="margin:0 0 0.75rem;padding:1rem;cursor:pointer" onclick="">
    <div style="font-family:'Amiri',serif;font-size:1rem;font-weight:700;margin-bottom:4px">${h(p.title)}</div>
    <div style="display:flex;gap:1rem;font-size:0.73rem;color:var(--muted)">
      <span>❤️ ${p.likes_count}</span>
      <span>👁 ${p.views_count}</span>
      <span>${h(p.created_ago || '')}</span>
      <span class="cat-badge ${p.cat_class || 'cat-tech'}">${h(p.cat_label || p.category)}</span>
    </div>
  </div>`).join('');

  return `
  <div>
    <div class="cover"></div>
    <div style="padding:0 1.25rem 1.5rem">
      <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-top:-40px;margin-bottom:0.75rem">
        ${avatarHTML(u.full_name, u.avatar_url, 80)}
        <button class="btn btn-sm btn-outline" onclick="goTo('home')">← رجوع</button>
      </div>
      <div style="font-family:'Amiri',serif;font-size:1.3rem;font-weight:700">${h(u.full_name)}</div>
      <div style="color:var(--muted);font-size:0.8rem;margin:2px 0">@${h(u.username)}</div>
      ${u.bio ? `<div style="font-size:0.85rem;color:var(--text2);margin:0.5rem 0">${h(u.bio)}</div>` : ''}
      <div style="display:flex;gap:1.5rem;margin:0.75rem 0;font-size:0.8rem">
        <span><strong style="color:var(--accent)">${u.followers_count || 0}</strong> <span style="color:var(--muted)">متابع</span></span>
        <span><strong style="color:var(--accent)">${u.following_count || 0}</strong> <span style="color:var(--muted)">يتابع</span></span>
      </div>
      <div class="divider"></div>
      <div class="section-title" style="margin-top:1rem">منشورات ${h(u.full_name)}</div>
      ${postsHtml || '<div class="empty-state">لا توجد منشورات بعد</div>'}
    </div>
  </div>`;
}

// =============================================
//  EDIT PROFILE MODAL
// =============================================
function openEditModal() {
  const u = State.user;
  if (!u) return;
  $('#epName').value  = u.full_name || '';
  $('#epBio').value   = u.bio || '';
  $('#epEmail').value = u.email || '';
  $('#epPass').value  = '';
  openModal('editProfileModal');
}

async function saveProfile() {
  const body = {
    full_name: $('#epName').value.trim(),
    bio:       $('#epBio').value.trim(),
    email:     $('#epEmail').value.trim(),
    password:  $('#epPass').value,
  };
  if (!body.full_name) { toast('الاسم مطلوب', 'error'); return; }
  const data = await api('users/update', { method:'POST', body: JSON.stringify(body) });
  if (data.error) { toast(data.error, 'error'); return; }
  toast('✅ تم حفظ التغييرات', 'success');
  closeModal('editProfileModal');
  loadProfile();
}

// =============================================
//  ABOUT
// =============================================
async function renderAbout() {
  const c = $('#aboutContent');
  const data = await api('settings');
  const siteName = data.site_name || 'مدوّنة القلم';
  const tagline  = data.site_tagline || '';

  c.innerHTML = `
  <div class="card" style="padding:1.5rem;margin-bottom:1rem;text-align:center;background:linear-gradient(135deg,var(--surface),var(--surface2))">
    <div class="font-serif" style="font-size:2.5rem;font-weight:900;color:var(--accent)">${h(siteName)}</div>
    <div style="color:var(--muted);font-size:0.85rem;margin-top:4px">${h(tagline)}</div>
  </div>
  <div class="card" style="padding:1.25rem;margin-bottom:1rem">
    <div class="section-title">من نحن</div>
    <p style="font-size:0.875rem;color:var(--text2);line-height:2">${h(siteName)} منصة عربية متخصصة في نشر المقالات والأفكار والاقتباسات. نؤمن بقوة الكلمة وأثرها في صنع التغيير.</p>
  </div>
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">✍️</div><div class="stat-num" id="ab-posts">—</div><div class="stat-lbl">منشور</div></div>
    <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-num" id="ab-users">—</div><div class="stat-lbl">كاتب</div></div>
    <div class="stat-card"><div class="stat-icon">❤️</div><div class="stat-num" id="ab-likes">—</div><div class="stat-lbl">إعجاب</div></div>
    <div class="stat-card"><div class="stat-icon">👁</div><div class="stat-num" id="ab-views">—</div><div class="stat-lbl">مشاهدة</div></div>
  </div>`;

  if (State.user?.role === 'admin') {
    const stats = await api('admin/stats');
    if ($('#ab-posts')) { $('#ab-posts').textContent = stats.posts; $('#ab-users').textContent = stats.users; $('#ab-likes').textContent = stats.likes; $('#ab-views').textContent = stats.views; }
  }
}

// =============================================
//  CONTACT
// =============================================
async function sendContact() {
  const body = {
    name:    $('#cName').value.trim(),
    email:   $('#cEmail').value.trim(),
    subject: $('#cSubject').value.trim(),
    message: $('#cMessage').value.trim(),
  };
  if (!body.name || !body.email || !body.message) { toast('أكمل الحقول المطلوبة', 'error'); return; }
  const data = await api('contact', { method:'POST', body: JSON.stringify(body) });
  if (data.error) { toast(data.error, 'error'); return; }
  toast('✅ تم إرسال رسالتك بنجاح', 'success');
  ['#cName','#cEmail','#cSubject','#cMessage'].forEach(s => { const el = $(s); if(el) el.value=''; });
}

// =============================================
//  ADMIN — لوحة تحكم بنمط rixpost sidebar
// =============================================
async function loadAdmin() {
  if (!State.user || State.user.role !== 'admin') {
    $('#adminContent').innerHTML = `<div style="padding:1.5rem">
      <div class="card" style="padding:1.5rem;text-align:center">
        <div style="font-size:2rem;margin-bottom:0.5rem">🔐</div>
        <p class="text-muted">هذه الصفحة للمشرفين فقط</p>
        <button class="btn btn-primary" style="margin-top:1rem" onclick="goTo('profile')">تسجيل الدخول</button>
      </div>
    </div>`;
    return;
  }
  renderAdminDash('stats');
}

let _adminTab = 'stats';
async function renderAdminDash(tab) {
  _adminTab = tab;
  const c = $('#adminContent');
  c.innerHTML = `
  <!-- RIXPOST SIDEBAR STYLE -->
  <div style="display:flex;min-height:calc(100dvh - 130px)">
    <div class="sidebar-rix" id="adminSidebar">
      <button class="s-toggle" onclick="toggleAdminSidebar()" id="sToggleBtn"><i class="bx bx-chevron-right"></i></button>
      <div class="s-header">
        <div class="s-logo">🛡</div>
        <div class="s-header-text">
          <h3>الإدارة</h3>
          <span>مدوّنة القلم</span>
        </div>
      </div>
      <ul class="s-menu">
        <li><button class="s-link ${tab==='stats'?'active':''}" onclick="renderAdminDash('stats')"><i class='bx bx-bar-chart-alt-2'></i><span class="s-label">الإحصائيات</span></button></li>
        <li><button class="s-link ${tab==='posts'?'active':''}" onclick="renderAdminDash('posts')"><i class='bx bx-news'></i><span class="s-label">المنشورات</span></button></li>
        <li><button class="s-link ${tab==='users'?'active':''}" onclick="renderAdminDash('users')"><i class='bx bx-group'></i><span class="s-label">المستخدمون</span></button></li>
        <li><button class="s-link ${tab==='msgs'?'active':''}" onclick="renderAdminDash('msgs')"><i class='bx bx-envelope'></i><span class="s-label">الرسائل</span></button></li>
        <li><button class="s-link ${tab==='settings'?'active':''}" onclick="renderAdminDash('settings')"><i class='bx bx-cog'></i><span class="s-label">الإعدادات</span></button></li>
        <li><button class="s-link" onclick="doLogout()"><i class='bx bx-log-out'></i><span class="s-label">خروج</span></button></li>
      </ul>
    </div>
    <div id="adminMain" style="flex:1;padding:1rem;overflow:auto;margin-right:var(--sidebar-w);transition:margin-right 0.3s">
      <div class="loader"><div class="spinner"></div></div>
    </div>
  </div>`;

  const main = $('#adminMain');
  showLoader(main);

  if (tab === 'stats') {
    const s = await api('admin/stats');
    main.innerHTML = `
    <h2 class="font-serif" style="font-size:1.3rem;margin-bottom:1rem">📊 الإحصائيات</h2>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon">✍️</div><div class="stat-num">${s.posts}</div><div class="stat-lbl">منشور</div></div>
      <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-num">${s.users}</div><div class="stat-lbl">مستخدم</div></div>
      <div class="stat-card"><div class="stat-icon">❤️</div><div class="stat-num">${s.likes}</div><div class="stat-lbl">إعجاب</div></div>
      <div class="stat-card"><div class="stat-icon">👁</div><div class="stat-num">${s.views}</div><div class="stat-lbl">مشاهدة</div></div>
      <div class="stat-card"><div class="stat-icon">📩</div><div class="stat-num">${s.messages}</div><div class="stat-lbl">رسالة جديدة</div></div>
    </div>`;
    return;
  }

  if (tab === 'posts') {
    const rows = await api('admin/posts');
    main.innerHTML = `<h2 class="font-serif" style="font-size:1.3rem;margin-bottom:1rem">📝 المنشورات (${rows.length})</h2>` +
      (rows.map(p => `
      <div class="card mb-2" style="padding:0.9rem;margin-bottom:0.6rem;display:flex;align-items:center;gap:0.75rem">
        <div style="flex:1">
          <div style="font-weight:700;font-size:0.875rem">${h(p.title)}</div>
          <div style="font-size:0.72rem;color:var(--muted)">${h(p.author_name)} · ${h(p.cat_label)} · ❤️${p.likes_count} 👁${p.views_count}</div>
        </div>
        <span class="badge badge-${p.status==='published'?'active':'inactive'}">${p.status==='published'?'منشور':'مسودة'}</span>
        <button class="btn btn-sm btn-danger" onclick="adminDeletePost(${p.id})">حذف</button>
      </div>`).join('') || '<div class="empty-state">لا توجد منشورات</div>');
    return;
  }

  if (tab === 'users') {
    const rows = await api('admin/users');
    main.innerHTML = `<h2 class="font-serif" style="font-size:1.3rem;margin-bottom:1rem">👥 المستخدمون (${rows.length})</h2>` +
      rows.map(u => `
      <div class="card mb-2" style="padding:0.9rem;margin-bottom:0.6rem;display:flex;align-items:center;gap:0.75rem">
        <div class="author-ava" style="width:38px;height:38px;font-size:14px">${u.full_name[0]}</div>
        <div style="flex:1">
          <div style="font-weight:700;font-size:0.875rem">${h(u.full_name)}</div>
          <div style="font-size:0.72rem;color:var(--muted)">@${h(u.username)} · ${u.posts_count} منشور</div>
        </div>
        <span class="badge badge-${u.role==='admin'?'admin':'author'}">${u.role==='admin'?'مدير':'كاتب'}</span>
        <button class="btn btn-sm btn-danger" onclick="adminDeleteUser(${u.id})">حذف</button>
      </div>`).join('');
    return;
  }

  if (tab === 'msgs') {
    const rows = await api('admin/messages');
    main.innerHTML = `<h2 class="font-serif" style="font-size:1.3rem;margin-bottom:1rem">📩 الرسائل (${rows.length})</h2>` +
      rows.map(m => `
      <div class="card mb-2" style="padding:1rem;margin-bottom:0.6rem;${m.is_read?'opacity:0.6':''}">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
          <div style="font-weight:700">${h(m.name)} <span style="color:var(--muted);font-size:0.78rem">${h(m.email)}</span></div>
          <div style="display:flex;gap:6px">
            ${!m.is_read ? `<button class="btn btn-sm btn-outline" onclick="markRead(${m.id})">تم القراءة</button>` : ''}
            <button class="btn btn-sm btn-danger" onclick="deleteMsg(${m.id})">حذف</button>
          </div>
        </div>
        <div style="font-weight:600;font-size:0.85rem">${h(m.subject || '')}</div>
        <div style="font-size:0.82rem;color:var(--text2);margin-top:4px">${h(m.message)}</div>
      </div>`).join('') || '<div class="empty-state">لا توجد رسائل</div>';
    return;
  }

  if (tab === 'settings') {
    const s = await api('settings');
    main.innerHTML = `
    <h2 class="font-serif" style="font-size:1.3rem;margin-bottom:1rem">⚙️ إعدادات الموقع</h2>
    <div class="card" style="padding:1.25rem">
      <div class="form-group"><label class="form-label">اسم الموقع</label><input class="form-input" id="s-name" value="${h(s.site_name||'')}"></div>
      <div class="form-group"><label class="form-label">الوصف</label><input class="form-input" id="s-tag" value="${h(s.site_tagline||'')}"></div>
      <div class="form-group"><label class="form-label">البريد الإلكتروني</label><input class="form-input" id="s-email" value="${h(s.site_email||'')}"></div>
      <div class="form-group"><label class="form-label">تويتر</label><input class="form-input" id="s-tw" value="${h(s.site_twitter||'')}"></div>
      <div class="form-group"><label class="form-label">منشورات في الصفحة</label><input class="form-input" type="number" id="s-ppp" value="${s.posts_per_page||10}"></div>
      <button class="btn btn-primary" onclick="saveSettings()">💾 حفظ الإعدادات</button>
    </div>`;
    return;
  }
}

function toggleAdminSidebar() {
  const sb = $('#adminSidebar');
  const main = $('#adminMain');
  sb.classList.toggle('closed');
  if (sb.classList.contains('closed')) {
    main.style.marginRight = '70px';
    document.documentElement.style.setProperty('--sidebar-w', '70px');
  } else {
    main.style.marginRight = '250px';
    document.documentElement.style.setProperty('--sidebar-w', '250px');
  }
}

async function adminDeletePost(id) {
  if (!confirm('هل تريد حذف هذا المنشور؟')) return;
  const data = await api('admin/posts/' + id, { method:'DELETE' });
  if (data.success) { toast('تم الحذف', 'success'); renderAdminDash('posts'); }
}

async function adminDeleteUser(id) {
  if (!confirm('حذف هذا المستخدم وجميع بياناته؟')) return;
  const data = await api('admin/users/' + id, { method:'DELETE' });
  if (data.error) { toast(data.error, 'error'); return; }
  toast('تم الحذف', 'success'); renderAdminDash('users');
}

async function markRead(id) {
  await api('admin/messages/' + id + '/read', { method:'PUT' });
  renderAdminDash('msgs');
}

async function deleteMsg(id) {
  if (!confirm('حذف الرسالة؟')) return;
  await api('admin/messages/' + id, { method:'DELETE' });
  renderAdminDash('msgs');
}

async function saveSettings() {
  const body = {
    site_name:     $('#s-name')?.value || '',
    site_tagline:  $('#s-tag')?.value || '',
    site_email:    $('#s-email')?.value || '',
    site_twitter:  $('#s-tw')?.value || '',
    posts_per_page:$('#s-ppp')?.value || '10',
  };
  const data = await api('settings', { method:'POST', body: JSON.stringify(body) });
  if (data.success) toast('✅ تم حفظ الإعدادات', 'success');
}

// =============================================
//  MODAL HELPERS
// =============================================
function openModal(id) {
  const m = $(`#${id}`);
  if (m) m.classList.add('open');
}
function closeModal(id) {
  const m = $(`#${id}`);
  if (m) m.classList.remove('open');
}

// =============================================
//  CLOSE DROPDOWNS ON OUTSIDE CLICK
// =============================================
document.addEventListener('click', (e) => {
  if (!e.target.closest('.share-wrap')) {
    $$('.share-dd.open').forEach(d => d.classList.remove('open'));
  }
});

// =============================================
//  INIT
// =============================================
document.addEventListener('DOMContentLoaded', async () => {
  // تطبيق السمة المحفوظة
  applyTheme(State.theme);

  // تحديث أزرار السمات
  $$('.theme-btn').forEach(b => {
    b.classList.toggle('active-theme', b.dataset.theme === State.theme);
  });

  // تحقق من المستخدم الحالي
  const me = await api('auth/me');
  State.user = me.user;
  if (State.user?.role === 'admin') {
    const navAdmin = $(`#nav-admin`);
    if (navAdmin) navAdmin.style.display = '';
  }

  // تحميل المنشورات
  loadPosts();
});
