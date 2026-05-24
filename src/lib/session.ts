import jwt from 'jsonwebtoken';

import { JWT_SECRET } from '$env/static/private';

interface TokenPayload {
	userId: number;
	role: 'USER' | 'ADMIN';
}

export function signToken(payload: TokenPayload): string {
	return jwt.sign(payload, JWT_SECRET, { expiresIn: '7d' });
}

export function verifyToken(token: string): TokenPayload | null {
	try {
		return jwt.verify(token, JWT_SECRET) as TokenPayload;
	} catch (error) {
		return null;
	}
}