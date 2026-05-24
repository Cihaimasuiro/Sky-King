import { json } from '@sveltejs/kit';
import cookie from 'cookie';

export async function POST() {
	const headers = {
		'Set-Cookie': cookie.serialize('token', '', {
			httpOnly: true,
			path: '/',
			sameSite: 'lax',
			maxAge: -1
		})
	};

	return json({ message: 'Logout successful' }, { status: 200, headers });
}
