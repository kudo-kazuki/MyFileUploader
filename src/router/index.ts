import { createRouter, createWebHistory } from 'vue-router'
import routes from 'virtual:generated-pages'
import { useAdminAuthStore } from '@/stores/admin_auth'

console.log('routes', routes)

const router = createRouter({
    history: createWebHistory(),
    routes: [...routes],
})

router.beforeEach((to, from, next) => {
    const adminAuthStore = useAdminAuthStore()
    adminAuthStore.checkAuth()

    const isAdminPage = to.path.startsWith('/admin')
    const isAdminLogin = to.path === '/admin/login'

    // 管理者ページにアクセス → ログインしてなければ /admin/login へ
    if (isAdminPage && !isAdminLogin && !adminAuthStore.isAuthenticated) {
        return next('/admin/login')
    }

    next()
})

export default router
