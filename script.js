const postListEl = document.getElementById('postList');
const contentEl = document.getElementById('post');
const searchEl = document.getElementById('search');

let posts = [];

async function loadPosts() {
	try {
		const res = await fetch('posts/posts.json');
		posts = await res.json();
		renderList(posts);
		const initial = location.hash.replace('#', '') || posts[0]?.file;
		if (initial) loadPost(initial);
	} catch (e) {
		contentEl.innerHTML = '<p class="placeholder">無法讀取文章清單。</p>';
		console.error(e);
	}
}

function renderList(list) {
	postListEl.innerHTML = '';
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
	try {
		const res = await fetch(`posts/${filename}`);
		if (!res.ok) throw new Error('not found');
		const html = await res.text();
		contentEl.innerHTML = html;
		window.scrollTo({top:0,behavior:'smooth'});
	} catch (e) {
		contentEl.innerHTML = '<p class="placeholder">找不到該文章。</p>';
		console.error(e);
	}
}

searchEl.addEventListener('input', (e) => {
	const q = e.target.value.trim().toLowerCase();
	if (!q) return renderList(posts);
	renderList(posts.filter(p => (p.title + ' ' + (p.summary||'')).toLowerCase().includes(q)));
});

window.addEventListener('hashchange', () => {
	const f = location.hash.replace('#','');
	if (f) loadPost(f);
});

loadPosts();
