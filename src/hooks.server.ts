import { verifyToken } from '$lib/session';
import prisma from '$lib/prisma';
import cookie from 'cookie';

export async function handle({ event, resolve }) {
	const cookies = cookie.parse(event.request.headers.get('cookie') || '');
	const token = cookies.token;

	if (token) {
		const payload = verifyToken(token);
		if (payload) {
			const { userId, role } = payload;
			if (role === 'ADMIN') {
				const admin = await prisma.admin.findUnique({
					where: { id: userId },
					select: { id: true, email: true }
				});
				if (admin) {
					event.locals.user = { ...admin, name: 'Admin', role: 'ADMIN' };
				}
			} else {
				const user = await prisma.user.findUnique({
					where: { id: userId },
					select: { id: true, email: true, name: true }
				});
				if (user) {
					event.locals.user = { ...user, role: 'USER' };
				}
			}
		}
	}

	return resolve(event);
}
