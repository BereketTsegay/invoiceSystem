import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const isAuthenticated = computed(() => !!user.value);
    
    // Permission checks
    const hasPermission = (permission) => {
        if (!user.value) return false;
        if (user.value.roles.some(role => role.name === 'super-admin')) return true;
        return user.value.permissions.some(perm => perm.name === permission);
    };

    
    const hasRole = (role) => {
        if (!user.value) return false;
        return user.value.roles.some(r => r.name === role);
    };

    const can = (permission) => hasPermission(permission);

    // Auth methods
    const login = async (credentials) => {
        try {
            const response = await axios.post('/api/login', credentials);
            user.value = response.data.user;
            localStorage.setItem('auth_token', response.data.access_token);
            axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
            return response.data;
        } catch (error) {
            throw error.response.data;
        }
    };

    // Add this method to your existing auth store
const register = async (userData) => {
  try {
    const response = await axios.post('/api/register', userData);
    user.value = response.data.user;
    localStorage.setItem('auth_token', response.data.access_token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
    return response.data;
  } catch (error) {
    // Handle specific error cases
    if (error.response?.status === 422) {
      throw { 
        type: 'validation', 
        errors: error.response.data.errors,
        message: 'Please fix the validation errors.'
      };
    } else if (error.response?.status === 409) {
      throw {
        type: 'conflict',
        message: 'An account with this email already exists.'
      };
    } else {
      throw {
        type: 'general',
        message: error.response?.data?.message || 'Registration failed. Please try again.'
      };
    }
  }
};

    const logout = async () => {
        try {
            await axios.post('/api/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            user.value = null;
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];
        }
    };

    const fetchUser = async () => {
        try {
            const response = await axios.get('/api/user');
            user.value = response.data.user;
            return response.data;
        } catch (error) {
            user.value = null;
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];
            throw error;
        }
    };

    // Initialize auth state
    const init = () => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            fetchUser().catch(() => {
                // Silent fail if token is invalid
            });
        }
    };

    return {
        user,
        isAuthenticated,
        hasPermission,
        hasRole,
        can,
        login,
        register,
        logout,
        fetchUser,
        init
    };
});