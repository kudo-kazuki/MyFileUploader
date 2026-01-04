<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAdminAuthStore } from '@/stores/admin_auth'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
import FlashMessage from '@/components/FlashMessage.vue'

const { height } = useWindowSizeAndDevice()
const authStore = useAdminAuthStore()

/* settings */
const MAX_FILES = 10
const MAX_FILE_SIZE = 50 * 1024 * 1024
const BLOCKED_EXTENSIONS = ['exe']

/* state */
const files = ref<File[]>([])
const progressMap = ref<Record<string, number>>({})
const uploading = ref(false)
const cooldown = ref(false)
const errorMessage = ref('')

const folderName = ref('')
const folderList = ref<string[]>([])

/* FlashMessage */
const showFlash = ref(false)
const flashMessage = ref('')

/* file input ref (iPhone対応) */
const fileInputRef = ref<HTMLInputElement | null>(null)

/* UI lock */
const isLocked = computed(() => uploading.value || cooldown.value)

/* total progress */
const totalProgress = computed(() => {
    const values = Object.values(progressMap.value)
    if (!values.length) return 0
    return Math.round(values.reduce((a, b) => a + b, 0) / values.length)
})

/* fetch folder list */
const fetchFolderList = async () => {
    const res = await axios.get('/api/upload/folderList', {
        headers: {
            Authorization: `Bearer ${authStore.token}`,
        },
    })
    folderList.value = res.data.data.folders
}

/* add files */
const addFiles = (newFiles: File[]) => {
    if (isLocked.value) return

    errorMessage.value = ''

    for (const file of newFiles) {
        if (files.value.length >= MAX_FILES) {
            errorMessage.value = `最大 ${MAX_FILES} ファイルまでです`
            return
        }

        const ext = file.name.split('.').pop()?.toLowerCase()
        if (ext && BLOCKED_EXTENSIONS.includes(ext)) {
            errorMessage.value = `.exe ファイルは禁止されています`
            return
        }

        if (file.size > MAX_FILE_SIZE) {
            errorMessage.value = `50MB を超えるファイルはアップロードできません`
            return
        }

        files.value.push(file)
        progressMap.value[file.name] = 0
    }
}

/* file input */
const onFileSelect = (e: Event) => {
    const input = e.target as HTMLInputElement
    if (!input.files) return
    addFiles(Array.from(input.files))
    input.value = ''
}

/* open file dialog (iPhone対応) */
const openFileDialog = () => {
    if (isLocked.value) return
    fileInputRef.value?.click()
}

/* drag drop */
const onDrop = (e: DragEvent) => {
    if (isLocked.value) return
    e.preventDefault()
    if (!e.dataTransfer?.files) return
    addFiles(Array.from(e.dataTransfer.files))
}

/* upload */
const uploadSingleFile = async (file: File) => {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('folderName', folderName.value)

    await axios.post('/api/upload/run', formData, {
        headers: {
            Authorization: `Bearer ${authStore.token}`,
        },
        onUploadProgress: (e) => {
            if (!e.total) return
            progressMap.value[file.name] = Math.round(
                (e.loaded * 100) / e.total,
            )
        },
    })
}

const startUpload = async () => {
    if (!files.value.length || !folderName.value || isLocked.value) return

    uploading.value = true
    errorMessage.value = ''

    try {
        const queue = [...files.value]
        const workers = Array(3)
            .fill(null)
            .map(async () => {
                while (queue.length) {
                    const file = queue.shift()
                    if (!file) return
                    await uploadSingleFile(file)
                }
            })
        await Promise.all(workers)

        /* success */
        flashMessage.value = 'アップロードが完了しました'
        showFlash.value = true
    } catch (e: any) {
        errorMessage.value = e.message ?? 'アップロードに失敗しました'
    } finally {
        uploading.value = false
        cooldown.value = true

        /* FlashMessage と同期してリセット */
        setTimeout(() => {
            files.value = []
            progressMap.value = {}
            cooldown.value = false
        }, 3500)
    }
}

onMounted(fetchFolderList)
</script>

<template>
    <div class="Page" :style="{ height: `${height}px` }">
        <FlashMessage
            v-model:isVisible="showFlash"
            type="success"
            :message="flashMessage"
        />

        <el-scrollbar>
            <div class="Page__inner">
                <h1 class="Page__h1">
                    <span>ファイルアップロード</span>
                    <router-link class="Page__h1Link" to="/upload/list"
                        >ファイル一覧へ</router-link
                    >
                </h1>

                <el-alert
                    v-if="errorMessage"
                    type="error"
                    :closable="false"
                    show-icon
                >
                    {{ errorMessage }}
                </el-alert>

                <!-- folder select -->
                <el-form label-position="top">
                    <el-form-item label="保存先フォルダ">
                        <el-select
                            v-model="folderName"
                            placeholder="フォルダを選択 or 入力"
                            filterable
                            allow-create
                            default-first-option
                            clearable
                            :disabled="isLocked"
                        >
                            <el-option
                                v-for="f in folderList"
                                :key="f"
                                :label="f"
                                :value="f"
                            />
                        </el-select>
                    </el-form-item>
                </el-form>

                <!-- drop zone -->
                <div class="Page__dropZone" @drop="onDrop" @dragover.prevent>
                    <p>ドラッグ＆ドロップ</p>

                    <el-button
                        type="primary"
                        :disabled="isLocked"
                        @click="openFileDialog"
                    >
                        ファイル選択
                    </el-button>

                    <input
                        ref="fileInputRef"
                        type="file"
                        multiple
                        style="display: none"
                        :disabled="isLocked"
                        @change="onFileSelect"
                    />
                </div>

                <!-- progress -->
                <el-progress
                    v-if="files.length"
                    :percentage="totalProgress"
                    status="success"
                />

                <el-tag
                    v-for="file in files"
                    :key="file.name"
                    style="margin: 4px"
                >
                    {{ file.name }} {{ progressMap[file.name] }}%
                </el-tag>

                <div style="margin-top: 16px">
                    <el-button
                        type="success"
                        :loading="uploading"
                        :disabled="isLocked || !files.length || !folderName"
                        @click="startUpload"
                    >
                        アップロード開始
                    </el-button>
                </div>
            </div>
        </el-scrollbar>
    </div>
</template>

<style lang="scss" scoped>
.Page {
    &__h1 {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    &__h1Link {
        font-size: 15px;
    }

    &__inner {
        padding: 16px;
    }

    &__dropZone {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border: 2px dashed var(--el-border-color);
        padding: 24px;
        text-align: center;
        margin-bottom: 16px;
        height: 30vh;
        gap: 12px;
    }
}
</style>
