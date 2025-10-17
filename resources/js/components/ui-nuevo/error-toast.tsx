import React, { useState } from 'react';

interface ErrorToastProps {
    message: string;
    className?: string;
    onHide?: () => void;
}

export const ErrorToast: React.FC<ErrorToastProps> = ({
    message,
    className = '',
    onHide,
    ...props
}) => {
    const [isVisible, setIsVisible] = useState(true);
    if (!isVisible) return null;
    return message ? (
        <div className="bg-white rounded-lg shadow-md p-2 mx-auto border border-red-200">
            <div className="flex items-center justify-between">
                <div className="flex items-center">
                    <div className="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <svg className="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span className="text-gray-700 text-sm">{message}</span>
                </div>
            </div>
        </div>
    ) : null;
};

