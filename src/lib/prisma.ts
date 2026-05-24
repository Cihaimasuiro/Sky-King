/**
 * @module $lib/prisma
 * @description This module exports a singleton instance of the PrismaClient.
 * It's used to interact with the database throughout the application.
 */
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export default prisma;
