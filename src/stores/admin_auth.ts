import { defineStore } from 'pinia'
import axios from 'axios'
import router from '@/router'
import { jwtDecode } from 'jwt-decode'
import { AdminJWTPayload, Admin, AdminAuthState } from '@/types'

export const useAdminAuthStore = defineStore('admin_auth', {
    state: (): AdminAuthState => ({
        token: null,
        isAuthenticated: false,
        name: null,
        level: null,
        adminList: [],
        userListLoading: false,
        userStatusChangeLoading: false,
    }),

    actions: {
        async login(name: string, password: string) {
            try {
                const response = await axios.post<{
                    success: boolean
                    data: { token: string }
                }>('/api/admin/login', {
                    name,
                    password,
                })

                this.token = response.data.data.token

                // JWTをlocalStorageに保存
                if (this.token) {
                    // JWTをデコード
                    const decoded = jwtDecode<AdminJWTPayload>(this.token)

                    this.isAuthenticated = true
                    this.name = decoded.name // ユーザー名を保存
                    this.level = decoded.level
                    localStorage.setItem('admin_jwt_token', this.token)
                    localStorage.setItem('admin_name', this.name)
                    localStorage.setItem('admin_level', String(this.level))

                    // 認証成功後、/admin/indexにリダイレクト
                    router.push('/admin/')
                }
            } catch (error: any) {
                console.log(error)

                if (
                    axios.isAxiosError(error) &&
                    error.response?.data?.message
                ) {
                    throw new Error(error.response.data.message)
                } else {
                    throw new Error('ログインに失敗しました')
                }
            }
        },

        logout() {
            this.token = null
            this.isAuthenticated = false
            this.name = null // ユーザー名をリセット
            localStorage.removeItem('admin_jwt_token')
            localStorage.removeItem('admin_name') // ユーザー名も削除
            router.push('/admin/login')
        },

        checkAuth() {
            const token = localStorage.getItem('admin_jwt_token')
            const storedname = localStorage.getItem('admin_name')
            const storedLevel = localStorage.getItem('admin_level')

            if (token) {
                try {
                    const decoded = jwtDecode<AdminJWTPayload>(token)
                    const currentTime = Date.now() / 1000
                    if (decoded.exp > currentTime) {
                        this.token = token
                        this.isAuthenticated = true
                        this.name = storedname
                        this.level = storedLevel ? Number(storedLevel) : null
                    } else {
                        this.logout() // トークン期限切れ
                    }
                } catch (e) {
                    this.logout() // 解析失敗もログアウト扱い
                }
            }
        },
    },

    persist: true,
})
