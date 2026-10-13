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

const maxImageDimension = 1920;
const imageQuality = 0.82;

async function compressImage(file) {
	if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
		throw new Error('Pilih foto berformat JPEG, PNG, atau WebP.');
	}

	if (typeof createImageBitmap !== 'function') {
		throw new Error('Browser ini tidak mendukung kompresi foto. Coba gunakan browser versi terbaru.');
	}

	const image = await createImageBitmap(file);

	try {
		const scale = Math.min(1, maxImageDimension / Math.max(image.width, image.height));
		const width = Math.max(1, Math.round(image.width * scale));
		const height = Math.max(1, Math.round(image.height * scale));
		const canvas = document.createElement('canvas');

		canvas.width = width;
		canvas.height = height;

		const context = canvas.getContext('2d');

		if (!context) {
			throw new Error('Browser tidak dapat memproses foto ini.');
		}

		context.drawImage(image, 0, 0, width, height);

		const compressed = await new Promise((resolve) => {
			canvas.toBlob(resolve, 'image/webp', imageQuality);
		});

		if (!compressed || compressed.type !== 'image/webp') {
			throw new Error('Browser gagal mengompresi foto ke WebP.');
		}

		if (scale === 1 && compressed.size >= file.size) {
			return file;
		}

		const filename = `${file.name.replace(/\.[^.]+$/, '')}.webp`;

		return new File([compressed], filename, {
			type: 'image/webp',
			lastModified: file.lastModified,
		});
	} finally {
		image.close();
	}
}

document.querySelectorAll('form[data-blob-upload]').forEach((form) => {
	form.addEventListener('submit', async (event) => {
		if (form.dataset.uploadReady === 'true') {
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
			button.dataset.originalLabel = button.textContent;
			button.textContent = 'Mengompresi foto...';
		});

		try {
			for (const { input, file } of uploads) {
				const compressedFile = await compressImage(file);
				const maxSize = input.dataset.blobFolder === 'hero' ? 10 : 5;

				if (compressedFile.size > maxSize * 1024 * 1024) {
					throw new Error(`Ukuran foto setelah dikompresi masih melebihi ${maxSize} MB.`);
				}

				if (form.dataset.blobUpload !== 'true') {
					const transfer = new DataTransfer();
					transfer.items.add(compressedFile);
					input.files = transfer.files;
					continue;
				}

				const folder = input.dataset.blobFolder;
				const safeName = compressedFile.name.replace(/[^A-Za-z0-9._-]/g, '-');
				const blob = await upload(`uploads/${folder}/${Date.now()}-${safeName}`, compressedFile, {
					access: 'public',
					handleUploadUrl: '/api/blob-upload',
				});
				const url = document.createElement('input');

				url.type = 'hidden';
				url.name = 'foto_url';
				url.value = blob.url;
				form.append(url);
				input.disabled = true;
				submitButtons.forEach((button) => {
					button.textContent = 'Mengunggah foto...';
				});
			}

			form.dataset.uploadReady = 'true';
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
				button.textContent = button.dataset.originalLabel;
				delete button.dataset.originalLabel;
			});
		}
	});
});
