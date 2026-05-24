import 'dotenv/config';
import { PrismaMariaDb } from '@prisma/adapter-mariadb';
import { PrismaClient } from '../../generated/prisma/client';

const databaseUrl = process.env.DATABASE_URL;

if (!databaseUrl) {
	throw new Error('DATABASE_URL is required');
}

const url = new URL(databaseUrl);
const adapter = new PrismaMariaDb({
	host: url.hostname,
	port: url.port ? Number(url.port) : 3306,
	user: decodeURIComponent(url.username),
	password: decodeURIComponent(url.password),
	database: url.pathname.slice(1)
});

const prisma = new PrismaClient({ adapter });

export default prisma;
