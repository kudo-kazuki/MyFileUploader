<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
import { useAdminAuthStore } from '@/stores/admin_auth'
import { useAdminModals } from '@/composables/useAdminModals'
import router from '@/router'

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

const {
    adminList,
    fetchAdminList,
    isLoading,
    isLoadingText,
    selectedAdminData,
    formatDate,
    modalErrorMessage,

    editedAdminName,
    editedAdminPassword,
    editedAdminLevel,
    editedAdminRemarks,
    isOpenEditAdminModal,
    openEditAdmin,
    closeEditAdmin,
    saveEditAdmin,

    isOpenDeleteAdminModal,
    openDeleteAdmin,
    closeDeleteAdmin,
    deleteAdmin,

    createAdminName,
    createAdminPassword,
    createAdminLevel,
    createAdminRemarks,
    isOpenCreateAdminModal,
    openCreateAdmin,
    closeCreateAdmin,
    isAdminCreateOk,
    saveCreateAdmin,
} = useAdminModals()

onMounted(() => {
    if (adminAuthStore.level === 0) {
        fetchAdminList()
    }
})
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
                <p>
                    ようこそ、{{ adminAuthStore.name }}さん。（レベル：{{
                        adminAuthStore.level
                    }}）
                </p>
                <Button
                    @click.prevent="logout()"
                    class="Page__logoutButton"
                    text="ログアウト"
                    size="m"
                    color="blue"
                />

                <ul>
                    <li>
                        <router-link to="/">トップページ</router-link>
                    </li>
                    <li>
                        <router-link to="/admin/singer">歌手管理</router-link>
                    </li>
                    <li>
                        <router-link to="/admin/song">曲管理</router-link>
                    </li>
                </ul>

                <div
                    v-if="adminAuthStore.level === 0"
                    class="Page__createButtonWrap"
                >
                    <Button
                        class="Page__createButton"
                        text="管理者新規追加"
                        color="blue"
                        @click="openCreateAdmin()"
                    />
                </div>

                <!-- 管理者だけが見れる一覧 -->
                <div v-if="adminAuthStore.level === 0">
                    <h2>管理者一覧</h2>
                    <el-table
                        v-if="adminList.length > 0"
                        :data="adminList"
                        border
                        style="width: 100%; margin-top: 1rem"
                    >
                        <el-table-column prop="id" label="ID" width="80" />
                        <el-table-column prop="name" label="名前" />
                        <el-table-column
                            prop="level"
                            label="レベル"
                            width="100"
                        />
                        <el-table-column prop="remarks" label="備考" />
                        <el-table-column label="作成日">
                            <template #default="{ row }">
                                {{ formatDate(row.created_at) }}
                            </template>
                        </el-table-column>
                        <el-table-column label="更新日">
                            <template #default="{ row }">
                                {{ formatDate(row.updated_at) }}
                            </template>
                        </el-table-column>
                        <el-table-column label="操作" width="160">
                            <template #default="scope">
                                <el-button
                                    size="small"
                                    type="primary"
                                    @click="openEditAdmin(scope.row)"
                                    >編集</el-button
                                >
                                <el-button
                                    size="small"
                                    type="danger"
                                    @click="openDeleteAdmin(scope.row)"
                                    >削除</el-button
                                >
                            </template>
                        </el-table-column>
                    </el-table>

                    <div v-else>読み込み中...</div>
                </div>

                <Modal
                    title="編集"
                    :isShow="isOpenEditAdminModal"
                    @close="closeEditAdmin()"
                >
                    <template #body>
                        <ul class="Page__editItems">
                            <li class="Page__editItem">
                                <label
                                    class="Page__editLabel"
                                    for="editedAdminName"
                                    >名前：<RequireLabel /></label
                                ><input
                                    type="text"
                                    v-model="editedAdminName"
                                    id="editedAdminName"
                                />
                            </li>
                            <li class="Page__editItem">
                                <label
                                    class="Page__editLabel"
                                    for="editedAdminPassword"
                                    >パスワード：<small class="Page__LabelSmall"
                                        >※空なら変更しない</small
                                    ></label
                                ><input
                                    type="password"
                                    v-model="editedAdminPassword"
                                    id="editedAdminPassword"
                                />
                            </li>
                            <li class="Page__editItem">
                                <label
                                    class="Page__editLabel"
                                    for="editedAdminLevel"
                                    >レベル：<RequireLabel /></label
                                ><input
                                    type="number"
                                    v-model="editedAdminLevel"
                                    min="0"
                                    id="editedAdminLevel"
                                />
                            </li>
                            <li class="Page__editItem">
                                <label
                                    class="Page__editLabel"
                                    for="editedAdminRemarks"
                                    >備考：</label
                                ><textarea
                                    v-model="editedAdminRemarks"
                                    id="editedAdminRemarks"
                                />
                            </li>
                        </ul>

                        <p
                            v-if="modalErrorMessage"
                            v-html="modalErrorMessage"
                            class="Page__errorMessage"
                        ></p>
                    </template>

                    <template #footer>
                        <ul>
                            <li>
                                <Button
                                    text="キャンセル"
                                    color="gray"
                                    @click="closeEditAdmin()"
                                />
                            </li>
                            <li>
                                <Button
                                    text="保存"
                                    color="blue"
                                    @click="saveEditAdmin()"
                                />
                            </li>
                        </ul>
                    </template>
                </Modal>

                <Modal
                    title="削除"
                    :isShow="isOpenDeleteAdminModal"
                    size="m"
                    isTextCenter
                    @close="closeDeleteAdmin()"
                >
                    <template #body>
                        <p>
                            {{ selectedAdminData?.id }}：{{
                                selectedAdminData?.name
                            }}<br />を削除しても本当によろしいですか？
                        </p>
                        <p>削除すると元には戻せません。</p>
                        <p
                            v-if="modalErrorMessage"
                            v-html="modalErrorMessage"
                            class="Page__errorMessage"
                        ></p>
                    </template>

                    <template #footer>
                        <ul>
                            <li>
                                <Button
                                    text="キャンセル"
                                    color="gray"
                                    @click="closeDeleteAdmin()"
                                />
                            </li>
                            <li>
                                <Button
                                    text="削除"
                                    color="red"
                                    @click="deleteAdmin()"
                                />
                            </li>
                        </ul>
                    </template>
                </Modal>

                <Modal
                    title="新規追加"
                    :isShow="isOpenCreateAdminModal"
                    @close="closeCreateAdmin()"
                >
                    <template #body>
                        <ul class="Page__editItems">
                            <li class="Page__editItem">
                                <label
                                    class="Page__editLabel"
                                    for="createAdminName"
                                    >名前：<RequireLabel /></label
                                ><input
                                    type="text"
                                    v-model="createAdminName"
                                    id="createAdminName"
                                />
                            </li>
                            <li class="Page__editItem">
                                <label
                                    class="Page__editLabel"
                                    for="createAdminPassword"
                                    >パスワード：<RequireLabel /></label
                                ><input
                                    type="password"
                                    v-model="createAdminPassword"
                                    id="createAdminPassword"
                                />
                            </li>
                            <li class="Page__editItem">
                                <label
                                    class="Page__editLabel"
                                    for="createAdminLevel"
                                    >レベル：<RequireLabel /></label
                                ><input
                                    type="number"
                                    v-model="createAdminLevel"
                                    min="0"
                                    id="createAdminLevel"
                                />
                            </li>
                            <li class="Page__editItem">
                                <label
                                    class="Page__editLabel"
                                    for="createAdminRemarks"
                                    >備考：</label
                                ><textarea
                                    v-model="createAdminRemarks"
                                    id="createAdminRemarks"
                                />
                            </li>
                        </ul>
                        <p
                            v-if="modalErrorMessage"
                            v-html="modalErrorMessage"
                            class="Page__errorMessage"
                        ></p>
                    </template>

                    <template #footer>
                        <ul>
                            <li>
                                <Button
                                    text="キャンセル"
                                    color="gray"
                                    @click="closeCreateAdmin()"
                                />
                            </li>
                            <li>
                                <Button
                                    text="作成"
                                    color="blue"
                                    :isDisabled="!isAdminCreateOk"
                                    @click="saveCreateAdmin()"
                                />
                            </li>
                        </ul>
                    </template>
                </Modal>

                <Loading v-if="isLoading" :text="isLoadingText" />
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
