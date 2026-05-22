import prisma from '$lib/prisma';
import { json } from '@sveltejs/kit';
import bcrypt from 'bcrypt';

export async function POST({ request }) {
  const { name, email, password } = await request.json();

  if (!name || !email || !password) {
    return json({ message: 'Missing name, email, or password' }, { status: 400 });
  }

  const saltRounds = 10;
  const hashedPassword = await bcrypt.hash(password, saltRounds);

  try {
    const user = await prisma.user.create({
      data: {
        name,
        email,
        password: hashedPassword,
      },
    });
    return json({ message: 'User created successfully', user }, { status: 201 });
  } catch (error) {
    return json({ message: 'User with this email already exists' }, { status: 409 });
  }
}
