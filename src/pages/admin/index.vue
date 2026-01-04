<script setup lang="ts">
import { ref } from 'vue'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
import { useAdminAuthStore } from '@/stores/admin_auth'

const { width, height, deviceType } = useWindowSizeAndDevice()
const adminAuthStore = useAdminAuthStore()

const errorMessage = ref('')

const logout = async () => {
    try {
        await adminAuthStore.logout()
    } catch (error) {
        errorMessage.value = 'ログアウトに失敗しました。'
    }
}
</script>

<template>
    <div
        class="Page"
        :style="{ height: `${height}px` }"
        :data-device="deviceType"
        :data-windowWidth="width"
    >
        <el-scrollbar>
            <div class="Page__innerContent">
                <h1>管理ページ</h1>
                <p>ようこそ、{{ adminAuthStore.username }}さん。</p>
                <Button
                    @click.prevent="logout()"
                    class="Page__logoutButton"
                    text="ログアウト"
                    size="m"
                    color="blue"
                />

                <ul>
                    <li>
                        <router-link to="/test">簡易テスト</router-link>
                    </li>
                    <li>
                        <router-link to="/upload"
                            >アップロードページ</router-link
                        >
                    </li>
                </ul>
            </div>
        </el-scrollbar>
    </div>
</template>

<style lang="scss" scoped>
.Page {
    overflow: hidden;
    height: 100vh;
    display: flex;
    flex-direction: column;

    &__innerContent {
        padding: 12px;
    }

    & &__logoutButton {
        display: block;
        margin: 12px auto;
    }

    &__createButtonWrap {
        text-align: center;
        padding-top: 16px;
        margin-top: 16px;
        border-top: 1px solid #333;
    }

    & &__createButton {
        width: 150px;
        display: inline-block;
    }

    &__editItems {
        display: flex;
        flex-direction: column;
        row-gap: 12px;
    }

    &__editItem {
        display: flex;
        align-items: center;
    }

    &__editLabel {
        width: 180px;
    }

    &__LabelSmall {
        display: block;
        font-size: 11px;
    }

    &__errorMessage {
        color: crimson;
        text-align: center;
        margin: 10px 0;
    }
}
</style>
