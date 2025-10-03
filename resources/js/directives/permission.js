import { useAuthStore } from '../store';

export const permissionDirective = {
    mounted(el, binding) {
        const authStore = useAuthStore();
        const { value } = binding;

        if (value && !authStore.can(value)) {
            el.style.display = 'none';
        }
    }
};

export const roleDirective = {
    mounted(el, binding) {
        const authStore = useAuthStore();
        const { value } = binding;

        if (value && !authStore.hasRole(value)) {
            el.style.display = 'none';
        }
    }
};