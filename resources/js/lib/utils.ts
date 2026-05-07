import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatUSD(mills: number): string {
    return (mills / 1000).toLocaleString('en-US', { style: 'currency', currency: 'USD' });
}
