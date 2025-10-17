import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import { Link } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
}

export default function AuthSimpleLayout({
    children,
}: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className='full-center'>
            <div className='login-container'>
                {children}
            </div>
        </div>
    );
}
