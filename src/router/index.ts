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

    const isUploadPage = to.path.startsWith('/upload')

    const needsAuth = isAdminPage || isUploadPage

    // 認証必須ページに未ログインでアクセスした場合
    if (needsAuth && !isAdminLogin && !adminAuthStore.isAuthenticated) {
        return next('/admin/login')
    }

    next()
})

export default router
