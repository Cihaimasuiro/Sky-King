import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit';

// This is not a secure way to check for authentication.
// We will implement a proper session-based authentication later.
const FAKE_USER_ID = 1;

export async function GET() {
  const user = await prisma.user.findUnique({
    where: {
      id: FAKE_USER_ID,
    },
  });

  if (!user) {
    return json({ message: 'Not authenticated' }, { status: 401 });
  }

  // Do not return the password hash
  const { password: _, ...userWithoutPassword } = user;

  return json(userWithoutPassword);
}
