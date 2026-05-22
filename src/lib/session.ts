import { randomBytes } from 'crypto';

const sessions = new Map<string, number>();

export function createSession(userId: number) {
  const sessionId = randomBytes(16).toString('hex');
  sessions.set(sessionId, userId);
  return sessionId;
}

export function getUserIdFromSession(sessionId: string) {
  return sessions.get(sessionId);
}

export function removeSession(sessionId: string) {
  sessions.delete(sessionId);
}
