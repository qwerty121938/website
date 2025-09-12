// 設定
const PATH_POSTS = 'posts/';
const POSTS_JSON = PATH_POSTS + 'posts.json';

// DOM
const postListEl = document.getElementById('postList');
const contentEl = document.getElementById('post');
const searchEl = document.getElementById('search');
const serveWarningEl = document.getElementById('serveWarning');

let posts = [];

// 範例備援文章（fetch 失敗時使用，不依賴外部檔案）
const SAMPLE_POSTS = [
	{
		"title": "範例：歡迎來到深色 Blog",
		"date": "2025-09-12",
		"summary": "內建範例文章（備援顯示）",
		"file": "sample-1",
		"content": `<article class="post-content"><h2>歡迎（範例）</h2><p class="post-meta">2025-09-12 · 範例</p><div class="content-body"><p>此為內建範例文章，當無法以 fetch 載入外部 posts 時會顯示。建議在專案資料夾啟動本機伺服器以使用完整功能。</p></div></article>`
	},
	{
		"title": "範例：如何管理文章檔案",
		"date": "2025-09-12",
		"summary": "說明如何把文章放在 posts/ 資料夾",
		"file": "sample-2",
		"content": `<article class="post-content"><h2>文章分檔管理</h2><p class="post-meta">2025-09-12 · 教學</p><div class="content-body"><p>把每篇文章放在 posts/ 目錄，並在 posts.json 中加入對應 metadata。如果 fetch 無法使用，網站會回退到內建範例。</p></div></article>`
	}
];

function showMessage(msg) {
	if (contentEl) contentEl.innerHTML = `<p class="placeholder">${msg}</p>`;
}

function showServeWarning(show) {
	if (!serveWarningEl) return;
	serveWarningEl.style.display = show ? 'block' : 'none';
	serveWarningEl.setAttribute('aria-hidden', show ? 'false' : 'true');
}

async function loadPosts() {
	// 若透過 file:// 協議開啟，顯示提示（fetch 很可能失敗）
	const isFileProtocol = location.protocol === 'file:';
	showServeWarning(isFileProtocol);

	try {
		const res = await fetch(POSTS_JSON);
		if (!res.ok) throw new Error('posts.json not found');
		posts = await res.json();
	} catch (e) {
		console.warn('無法 fetch posts.json，使用內建 SAMPLE_POSTS 作為備援。', e);
		posts = SAMPLE_POSTS.slice();
	}
	renderList(posts);

	// 初始載入（hash 或第一篇）
	const initial = location.hash.replace('#', '') || posts[0]?.file;
	if (initial) loadPost(initial);
}

function renderList(list) {
	if (!postListEl) return;
	postListEl.innerHTML = '';
	if (!list || list.length === 0) {
		postListEl.innerHTML = '<div class="post-meta">目前沒有文章</div>';
		return;
	}
	for (const p of list) {
		const item = document.createElement('div');
		item.className = 'post-item';
		item.innerHTML = `<div class="post-title">${p.title}</div><div class="post-meta">${p.date} · ${p.summary || ''}</div>`;
		item.onclick = () => {
			location.hash = p.file;
			loadPost(p.file);
		};
		postListEl.appendChild(item);
	}
}

async function loadPost(filename) {
	if (!contentEl) return;
	// 先嘗試從已讀取的 posts 陣列尋找（支援內嵌 content）
	const p = posts.find(x => x.file === filename);
	if (p && p.content) {
		contentEl.innerHTML = p.content;
		window.scrollTo({top:0,behavior:'smooth'});
		return;
	}

	// 否則嘗試 fetch 對應檔案（posts/filename）
	try {
		const res = await fetch(`${PATH_POSTS}${filename}`);
		if (!res.ok) throw new Error('not found');
		const html = await res.text();
		contentEl.innerHTML = html;
		window.scrollTo({top:0,behavior:'smooth'});
	} catch (e) {
		showMessage('找不到該文章。');
		console.error(e);
	}
}

// 搜尋（如果有對應的元素）
if (searchEl) {
	searchEl.addEventListener('input', (ev) => {
		const q = ev.target.value.trim().toLowerCase();
		if (!q) return renderList(posts);
		renderList(posts.filter(p => (p.title + ' ' + (p.summary||'')).toLowerCase().includes(q)));
	});
}

// hash 路由
window.addEventListener('hashchange', () => {
	const f = location.hash.replace('#','');
	if (f) loadPost(f);
});

loadPosts();
