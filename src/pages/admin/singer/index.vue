<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
const { width, height, deviceType } = useWindowSizeAndDevice()
import {
    formatGender,
    formatGroup,
    GENDER_OPTIONS,
    SOLO_OR_GROUP_OPTIONS,
} from '@/constants/singer'
import { useSingerModals } from '@/composables/useSingerModals'

const {
    singerList,
    fetchSingerList,
    isLoading,
    isLoadingText,
    selectedSingerData,
    formatDate,
    modalErrorMessage,

    // 編集
    editSingerForm,
    isOpenEditSingerModal,
    openEditSinger,
    closeEditSinger,
    saveEditSinger,
    isSingerEditOk,

    // 作成
    createSingerForm,
    isOpenCreateSingerModal,
    openCreateSinger,
    closeCreateSinger,
    isSingerCreateOk,
    saveCreateSinger,

    // 削除
    isOpenDeleteSingerModal,
    openDeleteSinger,
    closeDeleteSinger,
    runDeleteSinger,
} = useSingerModals()

const headerRef = ref<HTMLElement | null>(null)
const tableHeight = ref<number>(300)

const calcHeight = () => {
    const headerHeight = headerRef.value?.offsetHeight ?? 0
    const inner = document.querySelector<HTMLElement>('.Page__inner')
    const paddingTop = inner
        ? parseFloat(getComputedStyle(inner).paddingTop)
        : 0
    const paddingBottom = inner
        ? parseFloat(getComputedStyle(inner).paddingBottom)
        : 0

    tableHeight.value =
        window.innerHeight - headerHeight - paddingTop - paddingBottom - 30
}

onMounted(async () => {
    fetchSingerList()
    calcHeight()
    window.addEventListener('resize', calcHeight)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', calcHeight)
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
            <div class="Page__inner">
                <header class="Page__header" ref="headerRef">
                    <h1>歌手管理ページ</h1>
                    <Button
                        class="Page__createButton"
                        text="新規追加"
                        color="blue"
                        @click="openCreateSinger()"
                    />
                    <router-link to="/admin/" class="Page__returnPage"
                        >戻る</router-link
                    >
                </header>

                <el-table
                    v-if="singerList.length > 0"
                    :data="singerList"
                    border
                    style="width: 100%; margin-top: 1rem"
                    :height="tableHeight"
                >
                    <el-table-column prop="id" label="ID" width="50" />
                    <el-table-column
                        prop="name"
                        label="名前"
                        width="200"
                        fixed="left"
                    />
                    <el-table-column
                        prop="gender"
                        label="性別"
                        width="80"
                        :formatter="formatGender"
                    />
                    <el-table-column
                        prop="is_group"
                        label="ソロorグループ"
                        :formatter="formatGroup"
                    />
                    <el-table-column prop="debut_date" label="デビュー年" />
                    <el-table-column prop="description" label="詳細" />
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
                    <el-table-column label="操作" width="160" fixed="right">
                        <template #default="scope">
                            <el-button
                                size="small"
                                type="primary"
                                @click="openEditSinger(scope.row)"
                                >編集</el-button
                            >
                            <el-button
                                size="small"
                                type="danger"
                                @click="openDeleteSinger(scope.row)"
                                >削除</el-button
                            >
                        </template>
                    </el-table-column>
                </el-table>

                <div v-else>読み込み中...</div>
            </div>
        </el-scrollbar>

        <Modal
            title="新規追加"
            :isShow="isOpenCreateSingerModal"
            @close="closeCreateSinger()"
        >
            <template #body>
                <ul class="Page__editItems">
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="createSingerName"
                            >名前：<RequireLabel /></label
                        ><input
                            type="text"
                            v-model="createSingerForm.name"
                            id="createSingerName"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="createSingerGender"
                            >性別：</label
                        ><el-select
                            v-model="createSingerForm.gender"
                            id="createSingerGender"
                            placeholder="性別を選択"
                        >
                            <el-option
                                v-for="opt in GENDER_OPTIONS"
                                :key="opt.value"
                                :label="opt.label"
                                :value="opt.value"
                            />
                        </el-select>
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="createSingerGroup"
                            >ソロorグループ：</label
                        ><el-select
                            v-model="createSingerForm.is_group"
                            id="createSingerGroup"
                            placeholder="種別を選択"
                        >
                            <el-option
                                v-for="opt in SOLO_OR_GROUP_OPTIONS"
                                :key="opt.value"
                                :label="opt.label"
                                :value="opt.value"
                            />
                        </el-select>
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="createSingerDebut"
                            >デビュー年月：</label
                        >
                        <el-date-picker
                            v-model="createSingerForm.debut_date"
                            id="createSingerDebut"
                            type="month"
                            placeholder="年月を選択"
                            value-format="YYYY-MM-01"
                            format="YYYY年MM月"
                            :default-value="new Date(2000, 0, 1)"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label
                            class="Page__editLabel"
                            for="createSingerDescription"
                            >説明：</label
                        ><textarea
                            v-model="createSingerForm.description"
                            id="createSingerDescription"
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
                            @click="closeCreateSinger()"
                        />
                    </li>
                    <li>
                        <Button
                            text="作成"
                            color="blue"
                            :isDisabled="!isSingerCreateOk"
                            @click="saveCreateSinger()"
                        />
                    </li>
                </ul>
            </template>
        </Modal>

        <!-- 編集 -->
        <Modal
            title="編集"
            :isShow="isOpenEditSingerModal"
            @close="closeEditSinger()"
        >
            <template #body>
                <p>id: {{ editSingerForm.id }}</p>
                <ul class="Page__editItems">
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="editSingerName"
                            >名前：<RequireLabel /></label
                        ><input
                            type="text"
                            v-model="editSingerForm.name"
                            id="editSingerName"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="editSingerGender"
                            >性別：</label
                        ><el-select
                            v-model="editSingerForm.gender"
                            id="editSingerGender"
                            placeholder="性別を選択"
                        >
                            <el-option
                                v-for="opt in GENDER_OPTIONS"
                                :key="opt.value"
                                :label="opt.label"
                                :value="opt.value"
                            />
                        </el-select>
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="editSingerGroup"
                            >ソロorグループ：</label
                        ><el-select
                            v-model="editSingerForm.is_group"
                            id="editSingerGroup"
                            placeholder="種別を選択"
                        >
                            <el-option
                                v-for="opt in SOLO_OR_GROUP_OPTIONS"
                                :key="opt.value"
                                :label="opt.label"
                                :value="opt.value"
                            />
                        </el-select>
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="editSingerDebut"
                            >デビュー年月：</label
                        >
                        <el-date-picker
                            v-model="editSingerForm.debut_date"
                            id="editSingerDebut"
                            type="month"
                            placeholder="年月を選択"
                            value-format="YYYY-MM-01"
                            format="YYYY年MM月"
                            :default-value="new Date(2000, 0, 1)"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label
                            class="Page__editLabel"
                            for="editSingerDescription"
                            >説明：</label
                        ><textarea
                            v-model="editSingerForm.description"
                            id="editSingerDescription"
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
                            @click="closeEditSinger()"
                        />
                    </li>
                    <li>
                        <Button
                            text="上書き"
                            color="blue"
                            :isDisabled="!isSingerEditOk"
                            @click="saveEditSinger()"
                        />
                    </li>
                </ul>
            </template>
        </Modal>

        <!-- 削除 -->
        <Modal
            title="削除"
            :isShow="isOpenDeleteSingerModal"
            size="m"
            isTextCenter
            @close="closeDeleteSinger()"
        >
            <template #body>
                <p>
                    {{ selectedSingerData?.id }}：{{ selectedSingerData?.name
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
                            @click="closeDeleteSinger()"
                        />
                    </li>
                    <li>
                        <Button
                            text="削除"
                            color="red"
                            @click="runDeleteSinger()"
                        />
                    </li>
                </ul>
            </template>
        </Modal>

        <Loading v-if="isLoading" :text="isLoadingText" />
    </div>
</template>

<style lang="scss" scoped>
.Page {
    width: 100%;
    overflow: hidden;
    height: 100%;

    &__inner {
        padding: 20px;
        overflow: hidden;
    }

    &__header {
        display: flex;
        align-items: center;
        column-gap: 12px;
    }

    &__returnPage {
        margin-left: auto;
    }

    &__createButtonWrap {
        text-align: center;
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
        flex-shrink: 0;
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

    @media screen and (max-width: 740px) {
        &__editLabel {
            width: 130px;
        }
    }
}
</style>
