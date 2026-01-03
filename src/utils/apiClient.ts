import axios from 'axios'
import { useAdminAuthStore } from '@/stores/admin_auth'
import router from '@/router'

// 認証不要API用
export const api = axios.create({
    baseURL: '/api',
})

// 認証付きAPI用
export const apiAuth = axios.create({
    baseURL: '/api',
})

// リクエスト時にAuthorizationヘッダを付与
apiAuth.interceptors.request.use((config) => {
    const store = useAdminAuthStore()
    if (store.token) {
        config.headers.Authorization = `Bearer ${store.token}`
    }
    return config
})

// レスポンス時に 401 を検知 → 自動ログアウト
apiAuth.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const store = useAdminAuthStore()
            store.logout()
            router.push('/admin/login')
        }
        return Promise.reject(error)
    },
)
