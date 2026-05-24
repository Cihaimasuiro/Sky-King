import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit';
import bcrypt from 'bcrypt';
import { signToken } from '$lib/session';
import cookie from 'cookie';

export async function POST({ request }) {
	const { email, password } = await request.json();

	if (!email || !password) {
		return json({ message: 'Missing email or password' }, { status: 400 });
	}

	let user: { id: number; email: string; name: string | null; password } | null =
		await prisma.user.findUnique({
			where: {
				email
			}
		});

	let role: 'USER' | 'ADMIN' = 'USER';

	if (!user) {
		const admin = await prisma.admin.findUnique({
			where: {
				email
			}
		});
		if (admin) {
			user = { ...admin, name: 'Admin' };
			role = 'ADMIN';
		}
	}

	if (!user) {
		return json({ message: 'Invalid email or password' }, { status: 401 });
	}

	const passwordMatch = await bcrypt.compare(password, user.password);

	if (!passwordMatch) {
		return json({ message: 'Invalid email or password' }, { status: 401 });
	}

	const token = signToken({ userId: user.id, role });

	const headers = {
		'Set-Cookie': cookie.serialize('token', token, {
			httpOnly: true,
			path: '/',
			sameSite: 'lax',
			maxAge: 60 * 60 * 24 * 7 // 1 week
		})
	};

	// Do not return the password hash
	const userWithoutPassword = {
		id: user.id,
		email: user.email,
		name: user.name,
		role
	};

	return json({ message: 'Login successful', user: userWithoutPassword }, { status: 200, headers });
}
