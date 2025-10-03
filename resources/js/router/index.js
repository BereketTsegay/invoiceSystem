import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../store';

// Import components
import Dashboard from '../pages/Dashboard.vue';
import InvoiceList from '../pages/invoices/invoiceList.vue';
import InvoiceForm from '../pages/invoices/InvoiceForm.vue';
import ClientList from '../pages/clients/ClientsList.vue';
import UserList from '../components/users/UserList.vue';
import RoleList from '../pages/roles/RoleList.vue';
import Reports from '../pages/reports/Reports.vue';
import Login from '../auth/Login.vue';
import Register from '../auth/Register.vue';
import CompleteRegistration from '../auth/CompleteRegistration.vue';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { requiresGuest: true }
  },
  {
    path: '/complete-registration',
    name: 'complete-registration',
    component: CompleteRegistration,
    meta: { requiresGuest: true }
  },
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/invoices',
    name: 'invoices',
    component: InvoiceList,
    meta: { requiresAuth: true, permission: 'invoice.view' }
  },
  {
    path: '/invoices/create',
    name: 'invoices.create',
    component: InvoiceForm,
    meta: { requiresAuth: true, permission: 'invoice.create' }
  },
  {
    path: '/invoices/edit/:id',
    name: 'invoices.edit',
    component: InvoiceForm,
    meta: { requiresAuth: true, permission: 'invoice.edit' }
  },
  {
    path: '/clients',
    name: 'clients',
    component: ClientList,
    meta: { requiresAuth: true, permission: 'client.view' }
  },
  {
    path: '/reports',
    name: 'reports',
    component: Reports,
    meta: { requiresAuth: true, permission: 'report.view' }
  },
  {
    path: '/users',
    name: 'users',
    component: UserList,
    meta: { requiresAuth: true, permission: 'user.view' }
  },
  {
    path: '/roles',
    name: 'roles',
    component: RoleList,
    meta: { requiresAuth: true, permission: 'role.view' }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // Initialize auth if not already done
  if (!authStore.user && localStorage.getItem('auth_token')) {
    try {
      await authStore.fetchUser();
    } catch (error) {
      localStorage.removeItem('auth_token');
    }
  }

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login');
    return;
  }

  // Check if route requires guest (logged out)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/');
    return;
  }

  // Check permissions
  if (to.meta.permission && !authStore.can(to.meta.permission)) {
    next('/unauthorized');
    return;
  }

  next();
});

export default router;