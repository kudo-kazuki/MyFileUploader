<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
import { useAdminAuthStore } from '@/stores/admin_auth'

const adminAuthStore = useAdminAuthStore()
const { width, height, deviceType } = useWindowSizeAndDevice()

// 表示用
const result = ref<any>(null)
const error = ref<string | null>(null)

// ※ 動作確認用に一旦ここに直書き
// 実運用では auth store などから取得
const JWT_TOKEN = 'PUT_YOUR_JWT_HERE'

// axios 共通インスタンス（最低限）
const api = axios.create({
    baseURL: '/api',
})

// JWT不要テスト
const testMessage = async () => {
    error.value = null
    try {
        const res = await api.get('/test/message')
        result.value = res.data
    } catch (e: any) {
        error.value = e.message
    }
}

// JWT必須テスト
const testAuth = async () => {
    error.value = null

    try {
        const token = adminAuthStore.token
        if (!token) {
            throw new Error('JWT token not found (not logged in)')
        }

        const res = await api.get('/test/auth', {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })

        result.value = res.data
    } catch (e: any) {
        error.value = e.response?.data ?? e.message
    }
}

// upload ダミー
const testUpload = async (event: Event) => {
    error.value = null

    const input = event.target as HTMLInputElement
    if (!input.files || input.files.length === 0) return

    const token = adminAuthStore.token
    if (!token) {
        error.value = 'JWT token not found (not logged in)'
        return
    }

    const formData = new FormData()
    formData.append('file', input.files[0])

    try {
        const res = await api.post('/test/upload', formData, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })
        result.value = res.data
    } catch (e: any) {
        error.value = e.response?.data ?? e.message
    }
}
</script>

<template>
    <section
        class="Page"
        :style="{ height: `${height}px` }"
        :data-device="deviceType"
        :data-windowWidth="width"
    >
        <el-scrollbar class="TestPage">
            <h2>API 動作確認</h2>

            <div class="buttons">
                <el-button type="primary" @click="testMessage">
                    /api/test/message（JWT不要）
                </el-button>

                <el-button type="success" @click="testAuth">
                    /api/test/auth（JWT必須）
                </el-button>

                <label class="upload">
                    <el-button type="warning">
                        /api/test/upload（JWT必須）
                    </el-button>
                    <input type="file" @change="testUpload" />
                </label>
            </div>

            <el-divider />

            <h3>Result</h3>
            <pre>{{ result }}</pre>

            <h3 v-if="error" style="color: red">Error</h3>
            <pre v-if="error">{{ error }}</pre>
        </el-scrollbar>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    width: 100%;
    overflow: hidden;
    height: 100%;

    pre {
        white-space: pre-wrap;
    }

    @media screen and (max-width: 740px) {
    }
}
</style>
