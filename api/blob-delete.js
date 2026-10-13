import { timingSafeEqual } from 'node:crypto';
import { del } from '@vercel/blob';

function hasValidToken(request) {
	const expectedToken = process.env.VERCEL_BLOB_INTERNAL_TOKEN;
	const suppliedToken = request.headers.authorization?.replace(/^Bearer\s+/i, '');

	if (!expectedToken || !suppliedToken) {
		return false;
	}

	const expected = Buffer.from(expectedToken);
	const supplied = Buffer.from(suppliedToken);

	return expected.length === supplied.length && timingSafeEqual(expected, supplied);
}

export default async function handler(request, response) {
	if (request.method !== 'POST') {
		return response.status(405).json({ error: 'Method not allowed.' });
	}

	if (!hasValidToken(request)) {
		return response.status(401).json({ error: 'Unauthorized.' });
	}

	const url = request.body?.url;

	if (typeof url !== 'string' || !/^https:\/\/[a-z0-9-]+\.public\.blob\.vercel-storage\.com\/uploads\/hero\/[A-Za-z0-9._-]+$/i.test(url)) {
		return response.status(422).json({ error: 'Invalid Blob URL.' });
	}

	try {
		await del(url);

		return response.status(204).end();
	} catch (error) {
		console.error('Vercel Blob deletion failed.', error);

		return response.status(500).json({ error: 'Image deletion failed.' });
	}
}
