<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useAdminAuthStore } from '@/stores/admin_auth'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
import FlashMessage from '@/components/FlashMessage.vue'
import Loading from '@/components/Loading.vue'
import { useElScrollbarScroll } from '@/composables/useElScrollbarScroll'
import { ElScrollbar } from 'element-plus'
import { FileItem } from '@/types'
import dayjs from 'dayjs'

const { height } = useWindowSizeAndDevice()
const authStore = useAdminAuthStore()

const formatDate = (iso: string) => {
    return dayjs(iso).format('YYYY-MM-DD HH:mm')
}

const scrollbarRef = ref<InstanceType<typeof ElScrollbar> | null>(null)
const { showScrollButton, onScroll, goToPageTop } = useElScrollbarScroll(
    scrollbarRef,
    {
        threshold: 1, // ボタン表示しきい値
        duration: 350, // アニメ時間
    },
)

/* folder */
const currentFolderName = ref('')
const folderList = ref<string[]>([])
const isLoadingFolderLst = ref(true)

const fileList = ref<FileItem[]>([])
const isLoadingFileList = ref(false)

/* sort */
type SortKey = 'updatedAt' | 'name' | 'extension'
type SortOrder = 'asc' | 'desc'

const sortKey = ref<SortKey>('updatedAt')
const sortOrder = ref<SortOrder>('desc')

/* selection (delete) */
const selectedFiles = ref<Set<string>>(new Set())

/* flash */
const showFlash = ref(false)
const flashMessage = ref('')

/* fetch folder list */
const fetchFolderList = async () => {
    const res = await axios.get('/api/upload/folderList', {
        headers: {
            Authorization: `Bearer ${authStore.token}`,
        },
    })
    folderList.value = res.data.data.folders
    isLoadingFolderLst.value = false

    // 初期選択
    if (folderList.value.length > 0) {
        currentFolderName.value = folderList.value[0]
    }
}

/* fetch file list */
const fetchFileList = async (folderName: string) => {
    isLoadingFileList.value = true
    selectedFiles.value.clear()

    try {
        const res = await axios.get('/api/upload/fileList', {
            headers: {
                Authorization: `Bearer ${authStore.token}`,
            },
            params: { folderName },
        })
        fileList.value = res.data.data.files
    } finally {
        isLoadingFileList.value = false
    }
}

/* watch folder change */
watch(currentFolderName, (val) => {
    if (val) {
        fetchFileList(val)
    }
})

/* sorted files */
const sortedFileList = computed(() => {
    const list = [...fileList.value]

    list.sort((a, b) => {
        let result = 0

        switch (sortKey.value) {
            case 'name':
                result = a.name.localeCompare(b.name)
                break
            case 'extension':
                result = a.extension.localeCompare(b.extension)
                break
            case 'updatedAt':
                result = a.updatedAt - b.updatedAt
                break
        }

        return sortOrder.value === 'asc' ? result : -result
    })

    return list
})

/* helpers */
const isImage = (ext: string) =>
    ['jpg', 'jpeg', 'png', 'gif'].includes(ext.toLowerCase())

const toggleSelect = (path: string) => {
    if (selectedFiles.value.has(path)) {
        selectedFiles.value.delete(path)
    } else {
        selectedFiles.value.add(path)
    }
}

/* delete */
const showDeleteModal = ref(false)
const isDeleting = ref(false)

const deleteSelected = () => {
    if (selectedFiles.value.size === 0) return
    showDeleteModal.value = true
}

const confirmDelete = async () => {
    showDeleteModal.value = false
    isDeleting.value = true
    isLoadingFileList.value = true

    try {
        const fileNames = Array.from(selectedFiles.value).map(
            (path) => path.split('/').pop()!,
        )

        const res = await axios.post(
            '/api/upload/deleteFiles',
            {
                folderName: currentFolderName.value,
                files: fileNames,
            },
            {
                headers: {
                    Authorization: `Bearer ${authStore.token}`,
                },
            },
        )

        const deletedCount = res.data.data.deleted?.length ?? 0
        const errorCount = res.data.data.errors?.length ?? 0

        flashMessage.value = `削除完了：成功 ${deletedCount} 件 / 失敗 ${errorCount} 件`
        showFlash.value = true

        await fetchFileList(currentFolderName.value)
    } catch {
        flashMessage.value = '削除に失敗しました'
        showFlash.value = true
    } finally {
        isDeleting.value = false
        isLoadingFileList.value = false
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

        <header class="Page__header">
            <h1>ファイル一覧</h1>

            <p v-if="isLoadingFolderLst">フォルダ読み込み中</p>

            <ul
                v-if="!isLoadingFolderLst && folderList.length"
                class="Page__selectFolderName"
            >
                <li v-for="folder in folderList" :key="folder">
                    <Radio
                        :id="folder"
                        name="folderName"
                        :text="folder"
                        :value="folder"
                        v-model="currentFolderName"
                    />
                </li>
            </ul>

            <p v-if="!isLoadingFolderLst && !folderList.length">
                フォルダーがまだありません。
            </p>

            <!-- sort -->
            <div class="Page__sortArea">
                <select v-model="sortKey" class="Page__sortKey">
                    <option value="updatedAt">更新日</option>
                    <option value="name">ファイル名</option>
                    <option value="extension">拡張子</option>
                </select>
                <select v-model="sortOrder" class="Page__sortOrder">
                    <option value="desc">降順</option>
                    <option value="asc">昇順</option>
                </select>
                <Button
                    class="Page__deleteButton"
                    text="選択したファイルを削除"
                    color="red"
                    size="s"
                    :isDisabled="selectedFiles.size === 0 || isDeleting"
                    @click="deleteSelected"
                />
            </div>
        </header>

        <el-scrollbar ref="scrollbarRef" @scroll="onScroll">
            <div class="Page__inner">
                <Loading v-if="isLoadingFileList || isDeleting" />

                <ul v-else class="Page__fileItems">
                    <li
                        v-for="file in sortedFileList"
                        :key="file.fullPath"
                        class="Page__fileItem"
                    >
                        <input
                            class="Page__fileCheck"
                            type="checkbox"
                            :checked="selectedFiles.has(file.fullPath)"
                            @change="toggleSelect(file.fullPath)"
                        />

                        <div class="Page__fileLinkBlock">
                            <a
                                v-if="isImage(file.extension)"
                                class="Page__thumbLink"
                                :href="file.fullPath"
                                target="_blank"
                                rel="noopener"
                            >
                                <img
                                    v-if="isImage(file.extension)"
                                    :src="file.fullPath"
                                    class="Page__thumb"
                                />
                            </a>

                            <a
                                class="Page__fileName"
                                :href="file.fullPath"
                                target="_blank"
                                rel="noopener"
                            >
                                {{ file.name }}
                            </a>
                        </div>
                        <span class="Page__meta">
                            {{ formatDate(file.updatedAtIso) }}
                        </span>
                    </li>
                </ul>
            </div>
        </el-scrollbar>

        <ScrollToTopButton
            v-model:isVisible="showScrollButton"
            @clicked="goToPageTop()"
        />

        <Modal
            title="削除確認"
            size="m"
            :isShow="showDeleteModal"
            isTextCenter
            @close="showDeleteModal = false"
        >
            <template #body>
                <p>
                    選択した
                    {{ selectedFiles.size }} 件のファイルを削除します。<br />
                    この操作は取り消せません。
                </p>
            </template>

            <template #footer>
                <ul>
                    <li>
                        <Button
                            text="キャンセル"
                            color="gray"
                            @click="showDeleteModal = false"
                        />
                    </li>
                    <li>
                        <Button
                            text="削除する"
                            color="red"
                            :isDisabled="isDeleting"
                            @click="confirmDelete"
                        />
                    </li>
                </ul>
            </template>
        </Modal>
    </div>
</template>

<style lang="scss" scoped>
.Page {
    display: flex;
    flex-direction: column;

    &__header {
        flex-shrink: 0;
        padding: 12px 16px 16px;
        box-shadow: 1px 1px 8px rgba(0, 0, 0, 0.2);
    }

    &__inner {
        padding: 16px;
    }

    &__sortArea {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-top: 8px;
    }

    &__sortKey:not(.el-input__inner):not(.el-select__input) {
        width: 150px;
    }

    &__sortOrder:not(.el-input__inner):not(.el-select__input) {
        width: 100px;
    }

    & &__deleteButton {
        margin-left: auto;
    }

    &__selectFolderName {
        margin-top: 8px;
    }

    &__fileItems {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    &__fileItem {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #ddd;
    }

    &__fileLinkBlock {
        display: flex;
        align-items: center;
        column-gap: 12px;
    }

    &__thumbLink {
        flex-shrink: 0;
        line-height: 0;
    }

    &__thumb {
        width: 80px;
        height: auto;
        object-fit: cover;
        flex-shrink: 0;
    }

    &__fileName {
        text-decoration: underline;
        word-break: break-all;
    }

    &__meta {
        margin-left: auto;
        font-size: 12px;
        color: #666;
    }

    &__fileCheck {
        cursor: pointer;
        $size: 16px;
        width: $size;
        height: $size;
    }

    @media screen and (max-width: 740px) {
        &__header {
            padding: 12px 12px;
        }

        &__inner {
            padding: 12px;
        }

        & &__deleteButton {
            font-size: 11px;
            padding: 4px 5px;
        }

        &__sortKey:not(.el-input__inner):not(.el-select__input) {
            width: 120px;
        }

        &__sortOrder:not(.el-input__inner):not(.el-select__input) {
            width: 80px;
        }

        &__thumb {
            width: 50px;
        }
    }
}
</style>
