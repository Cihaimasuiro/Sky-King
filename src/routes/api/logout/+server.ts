import { removeSession } from '$lib/session';
import { json } from '@sveltejs/kit';
import cookie from 'cookie';

export async function POST({ request }) {
  const cookies = cookie.parse(request.headers.get('cookie') || '');
  const sessionId = cookies.sessionId;

  if (sessionId) {
    removeSession(sessionId);
  }

  const headers = {
    'Set-Cookie': cookie.serialize('sessionId', '', {
      httpOnly: true,
      path: '/',
      sameSite: 'lax',
      maxAge: -1,
    }),
  };

  return json({ message: 'Logout successful' }, { status: 200, headers });
}
