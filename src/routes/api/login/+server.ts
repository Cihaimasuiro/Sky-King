import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit';
import bcrypt from 'bcrypt';
import { createSession } from '$lib/session';
import cookie from 'cookie';

export async function POST({ request }) {
  const { email, password } = await request.json();

  if (!email || !password) {
    return json({ message: 'Missing email or password' }, { status: 400 });
  }

  const user = await prisma.user.findUnique({
    where: {
      email,
    },
  });

  if (!user) {
    return json({ message: 'Invalid email or password' }, { status: 401 });
  }

  const passwordMatch = await bcrypt.compare(password, user.password);

  if (!passwordMatch) {
    return json({ message: 'Invalid email or password' }, { status: 401 });
  }

  const sessionId = createSession(user.id);

  const headers = {
    'Set-Cookie': cookie.serialize('sessionId', sessionId, {
      httpOnly: true,
      path: '/',
      sameSite: 'lax',
      maxAge: 60 * 60 * 24 * 7, // 1 week
    }),
  };

  // Do not return the password hash
  const { password: _, ...userWithoutPassword } = user;

  return json({ message: 'Login successful', user: userWithoutPassword }, { status: 200, headers });
}
