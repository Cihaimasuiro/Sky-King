import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit';
import bcrypt from 'bcrypt';

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

  // Do not return the password hash
  const { password: _, ...userWithoutPassword } = user;

  return json({ message: 'Login successful', user: userWithoutPassword }, { status: 200 });
}
