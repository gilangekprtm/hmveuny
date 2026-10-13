import { handleUpload } from '@vercel/blob/client';

const allowedFolders = new Set(['hero', 'unit', 'pembina', 'pimpinan', 'anggota', 'publikasi']);
const maxUploadSize = 10 * 1024 * 1024;

export default async function handler(request, response) {
	if (request.method !== 'POST') {
		return response.status(405).json({ error: 'Method not allowed.' });
	}

	try {
		if (request.body?.type === 'blob.generate-client-token') {
			const requestOrigin = request.headers.origin;
			const requestHost = request.headers.host;

			if (!requestOrigin || !requestHost) {
				throw new Error('The upload request origin is missing.');
			}

			const appOrigin = new URL(requestOrigin);

			if (appOrigin.protocol !== 'https:' || appOrigin.host.toLowerCase() !== requestHost.toLowerCase()) {
				throw new Error('The upload request must use the application HTTPS origin.');
			}

			const authorization = await fetch(new URL('/control-panel/blob-upload-authorize', appOrigin), {
				headers: request.headers.cookie ? { cookie: request.headers.cookie } : {},
				redirect: 'manual',
			});

			if (authorization.status !== 204) {
				return response.status(401).json({ error: 'Admin authentication is required to upload images.' });
			}
		}

		const result = await handleUpload({
			body: request.body,
			request,
			onBeforeGenerateToken: async (pathname) => {
				const match = pathname.match(/^uploads\/([a-z]+)\/[A-Za-z0-9._-]+$/);

				if (!match || !allowedFolders.has(match[1])) {
					throw new Error('The requested upload path is not allowed.');
				}

				return {
					allowedContentTypes: ['image/jpeg', 'image/png', 'image/webp'],
					maximumSizeInBytes: match[1] === 'hero' ? maxUploadSize : 5 * 1024 * 1024,
					addRandomSuffix: true,
				};
			},
		});

		return response.status(200).json(result);
	} catch (error) {
		console.error('Vercel Blob upload failed.', error);

		return response.status(500).json({ error: 'Image upload failed. Please try again.' });
	}
}
