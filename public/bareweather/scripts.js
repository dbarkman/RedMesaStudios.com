(function(){
	const lb = document.getElementById('lightbox');
	const img = document.getElementById('lightbox-img');
	function open(src, alt){ img.src = src; img.alt = alt||'Screenshot'; lb.classList.add('show'); lb.setAttribute('aria-hidden','false'); }
	window.closeLightbox = function(){ lb.classList.remove('show'); lb.setAttribute('aria-hidden','true'); img.removeAttribute('src'); };
	document.addEventListener('click',function(e){
		const a = e.target.closest('a.shot');
		if(!a) return;
		e.preventDefault();
		open(a.getAttribute('href'), a.querySelector('img')?.getAttribute('alt'));
	});
	lb?.addEventListener('click',function(e){ if(e.target===lb) window.closeLightbox(); });
})();
