import { getUserIdFromSession } from '$lib/session';
import prisma from '$lib/prisma';
import cookie from 'cookie';

export async function handle({ event, resolve }) {
  const cookies = cookie.parse(event.request.headers.get('cookie') || '');
  const sessionId = cookies.sessionId;

  if (sessionId) {
    const userId = getUserIdFromSession(sessionId);
    if (userId) {
      const user = await prisma.user.findUnique({
        where: { id: userId },
        select: { id: true, email: true, name: true },
      });
      if (user) {
        event.locals.user = user;
      }
    }
  }

  return resolve(event);
}
