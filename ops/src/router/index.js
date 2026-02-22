import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue' 

const routes = [
  {
  /*
     path: '/',
     redirect: '/dashboard'
  */
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guest: true }
  },
  {
// Main Layout (Parent Route)
    path: '/',
    component: () => import('../views/Dashboard.vue'),
    meta: { requiresAuth: true },
    children: [
        { path: '', redirect: '/dashboard' },
        {
            path: '/dashboard', 
            name: 'Dashboard',
            component: () => import('../views/DashboardHome.vue')
        },
        {
            path: '/manifest',
            name: 'Laporan Harian',
            component: () => import('../views/Manifest.vue')
        },
        {
            path: '/reports',
            name: 'Statistik & Grafik',
            component: () => import('../views/Reports.vue')
        },
        {
            path: '/proofs',
            name: 'Bukti Pembayaran',
            component: () => import('../views/PaymentProofs.vue')
        },
        {
            path: '/ktm-proofs',
            name: 'Bukti KTM',
            component: () => import('../views/KtmProofs.vue')
        },
        {
            path: '/inventory',
            name: 'Manajemen Aset',
            component: () => import('../views/Assets.vue')
        },
        {
            path: '/rute',
            name: 'Kelola Rute',
            component: () => import('../views/RouteManagement.vue')
        },
        {
            path: '/edit-history',
            name: 'Riwayat Edit',
            component: () => import('../views/EditHistory.vue')
        },
        {
            path: '/users',
            name: 'Manajemen User',
            component: () => import('../views/Users.vue')
        },
        {
            path: '/trip-history',
            name: 'Riwayat Trip',
            component: () => import('../views/TripHistory.vue')
        },
        {
            path: '/booking-history',
            name: 'Riwayat Booking',
            component: () => import('../views/BookingHistory.vue')
        }
    ]
  },
  // Catch-all? No, just end layout route.
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Navigation Guard
router.beforeEach(async (to, from, next) => {
  const isAuthenticated = localStorage.getItem('is_authenticated') === 'true'; 
  
  if (to.meta.requiresAuth && !isAuthenticated) {
     next('/login');
  } else {
    next();
  }
});

export default router
