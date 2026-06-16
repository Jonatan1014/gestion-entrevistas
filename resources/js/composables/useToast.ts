import { toast } from 'vue-sonner';

export function useToast() {
    const success = (message: string) => {
        toast.success(message, {
            duration: 4000,
            style: {
                background: '#0d0d0d',
                color: '#51eead',
                border: '1px solid rgba(81, 238, 173, 0.2)',
            },
        });
    };

    const error = (message: string) => {
        toast.error(message, {
            duration: 5000,
            style: {
                background: '#0d0d0d',
                color: '#f87171',
                border: '1px solid rgba(248, 113, 113, 0.2)',
            },
        });
    };

    const info = (message: string) => {
        toast(message, {
            duration: 3500,
            style: {
                background: '#0d0d0d',
                color: '#f2f2f2',
                border: '1px solid rgba(255, 255, 255, 0.1)',
            },
        });
    };

    return { success, error, info };
}
