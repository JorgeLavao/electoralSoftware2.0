import React, { useEffect, useState } from 'react';

interface SuccessToastProps {
    message: string;
    duration?: number;
    autoShow?: boolean;
    onHide?: () => void;
}

export const SuccessToast: React.FC<SuccessToastProps> = ({
    message,
    duration = 5000,
    autoShow = true,
    onHide,
}) => {
    const [isVisible, setIsVisible] = useState(autoShow);

    useEffect(() => {
        if (autoShow && duration > 0) {
            const timer = setTimeout(() => {
                setIsVisible(false);
                onHide?.();
            }, duration);

            return () => clearTimeout(timer);
        }
    }, [duration, autoShow, onHide]);

    const handleClose = () => {
        setIsVisible(false);
        onHide?.();
    };

    if (!isVisible) return null;

    return (
        <div className="bg-white rounded-lg shadow-md p-2 border border-green-200 mt-2">
            <div className="flex items-center justify-between">
                <div className="flex items-center">
                    <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <svg className="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span className="text-gray-700 text-sm">{message}</span>
                </div>
                <button className="text-gray-400 hover:text-gray-600 ml-2" onClick={handleClose} aria-label="Cerrar notificación">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    );
};

