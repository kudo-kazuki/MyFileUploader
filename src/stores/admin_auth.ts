import { defineStore } from 'pinia'
import axios from 'axios'
import router from '@/router'
import { jwtDecode } from 'jwt-decode'
import { AdminJWTPayload } from '@/types'

export const useAdminAuthStore = defineStore('admin_auth', {
    state: () => ({
        token: null as string | null,
        isAuthenticated: false,
        username: null as string | null,
    }),

    actions: {
        async login(username: string, password: string) {
            try {
                const res = await axios.post<{
                    success: boolean
                    data: { token: string }
                }>('/api/auth/login', {
                    username,
                    password,
                })

                const token = res.data.data.token
                const decoded = jwtDecode<AdminJWTPayload>(token)

                this.token = token
                this.isAuthenticated = true
                this.username = decoded.sub

                localStorage.setItem('admin_jwt_token', token)

                router.push('/admin/')
            } catch (error: any) {
                if (
                    axios.isAxiosError(error) &&
                    error.response?.data?.message
                ) {
                    throw error.response.data.message
                }
                throw 'ログインに失敗しました'
            }
        },

        logout() {
            this.token = null
            this.isAuthenticated = false
            this.username = null
            localStorage.removeItem('admin_jwt_token')
            router.push('/admin/login')
        },

        checkAuth() {
            const token = localStorage.getItem('admin_jwt_token')
            if (!token) return

            try {
                const decoded = jwtDecode<AdminJWTPayload>(token)
                const now = Date.now() / 1000

                if (decoded.exp > now && decoded.role === 'admin') {
                    this.token = token
                    this.isAuthenticated = true
                    this.username = decoded.sub
                } else {
                    this.logout()
                }
            } catch {
                this.logout()
            }
        },
    },

    persist: true,
})
