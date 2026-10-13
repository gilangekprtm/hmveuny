import Toastify from 'toastify-js';
import { upload } from '@vercel/blob/client';

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const revealElements = document.querySelectorAll('[data-reveal]');

if (revealElements.length && 'IntersectionObserver' in window && !prefersReducedMotion) {
	document.documentElement.classList.add('has-scroll-reveal');

	const revealObserver = new IntersectionObserver((entries, observer) => {
		entries.forEach((entry) => {
			if (!entry.isIntersecting) return;

			entry.target.classList.add('is-visible');
			observer.unobserve(entry.target);
		});
	}, {
		threshold: 0.12,
		rootMargin: '0px 0px -40px 0px',
	});

	revealElements.forEach((element) => revealObserver.observe(element));
}

document.querySelectorAll('[data-toast-messages] [data-toast-type]').forEach((message) => {
	const type = message.dataset.toastType;
	const text = message.textContent.trim();

	if (!text) return;

	Toastify({
		text,
		className: `hmve-toast hmve-toast--${type}`,
		duration: 5000,
		gravity: 'top',
		position: 'right',
		offset: { x: 20, y: 20 },
		close: true,
		stopOnFocus: true,
		escapeMarkup: true,
		ariaLive: type === 'error' ? 'assertive' : 'polite',
	}).showToast();
});

document.querySelectorAll('form[data-blob-upload="true"]').forEach((form) => {
	form.addEventListener('submit', async (event) => {
		if (form.dataset.blobReady === 'true') {
			return;
		}

		const uploads = [...form.querySelectorAll('input[type="file"][data-blob-folder]')]
			.map((input) => ({ input, file: input.files?.[0] }))
			.filter(({ file }) => file);

		if (!uploads.length) {
			return;
		}

		event.preventDefault();

		const submitButtons = [...form.querySelectorAll('button[type="submit"]')];
		submitButtons.forEach((button) => {
			button.disabled = true;
		});

		try {
			for (const { input, file } of uploads) {
				const folder = input.dataset.blobFolder;
				const safeName = file.name.replace(/[^A-Za-z0-9._-]/g, '-');
				const blob = await upload(`uploads/${folder}/${Date.now()}-${safeName}`, file, {
					access: 'public',
					handleUploadUrl: '/api/blob-upload',
				});
				const url = document.createElement('input');

				url.type = 'hidden';
				url.name = 'foto_url';
				url.value = blob.url;
				form.append(url);
				input.disabled = true;
			}

			form.dataset.blobReady = 'true';
			form.requestSubmit();
		} catch (error) {
			Toastify({
				text: error instanceof Error ? error.message : 'Gagal mengunggah gambar. Silakan coba lagi.',
				className: 'hmve-toast hmve-toast--error',
				duration: 6000,
				gravity: 'top',
				position: 'right',
				close: true,
				escapeMarkup: true,
				ariaLive: 'assertive',
			}).showToast();

			submitButtons.forEach((button) => {
				button.disabled = false;
			});
		}
	});
});
