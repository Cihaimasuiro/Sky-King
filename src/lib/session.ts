/**
 * @module $lib/session
 * @description This module provides utility functions for signing and verifying JWT tokens.
 * It uses a secret key from environment variables for security.
 */
import jwt from 'jsonwebtoken';
import { JWT_SECRET } from '$env/static/private';

/**
 * Interface for the JWT token payload.
 * @typedef {object} TokenPayload
 * @property {number} userId - The ID of the user.
 * @property {'USER' | 'ADMIN'} role - The role of the user.
 */
interface TokenPayload {
	userId: number;
	role: 'USER' | 'ADMIN';
}

/**
 * Signs a JWT token with the given payload.
 * The token expires in 7 days.
 * @param {TokenPayload} payload - The payload to sign.
 * @returns {string} The signed JWT token.
 */
export function signToken(payload: TokenPayload): string {
	return jwt.sign(payload, JWT_SECRET, { expiresIn: '7d' });
}

/**
 * Verifies a JWT token.
 * @param {string} token - The JWT token to verify.
 * @returns {TokenPayload | null} The decoded payload if the token is valid, otherwise null.
 */
export function verifyToken(token: string): TokenPayload | null {
	try {
		return jwt.verify(token, JWT_SECRET) as TokenPayload;
		// eslint-disable-next-line @typescript-eslint/no-unused-vars
	} catch (error) {
		return null;
	}
}
