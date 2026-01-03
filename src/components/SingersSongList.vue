<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { Song, SongFormInput, SongSortKey } from '@/types'
import { SONG_SORT_KEY, SONG_SORT_OPTION } from '@/constants/song'

interface Props {
    items: Song[]
    singerId: number | null
    singerName: string
    isLoading?: boolean
    isOpen?: boolean
}

const props = withDefaults(defineProps<Props>(), {})

const emit = defineEmits([
    'clickedNewSongCreate',
    'clickedEdit',
    'clickedDelete',
    'clickedSongOrder',
])

const clickedNewSongCreate = () => {
    emit('clickedNewSongCreate', props.singerId)
}

const clickedSongOrder = () => {
    emit('clickedSongOrder', props.singerId)
}

const clickedEdit = (data: SongFormInput) => {
    emit('clickedEdit', data)
}

const clickedDelete = (data: { id: number; name: string }) => {
    emit('clickedDelete', data)
}

// ソート条件（デフォルト: id昇順）
const sortKey = ref<SongSortKey>(SONG_SORT_KEY.ID_ASC)

// ソート済みリスト
const sortedItems = computed(() => {
    const items = [...props.items] // 破壊しないようコピー
    switch (sortKey.value) {
        case SONG_SORT_KEY.ID_ASC:
            return items.sort((a, b) => a.id - b.id)

        case SONG_SORT_KEY.ID_DESC:
            return items.sort((a, b) => b.id - a.id)

        case SONG_SORT_KEY.UPDATED_ASC:
            return items.sort(
                (a, b) =>
                    new Date(a.updated_at).getTime() -
                    new Date(b.updated_at).getTime(),
            )

        case SONG_SORT_KEY.UPDATED_DESC:
            return items.sort(
                (a, b) =>
                    new Date(b.updated_at).getTime() -
                    new Date(a.updated_at).getTime(),
            )

        case SONG_SORT_KEY.NAME_ASC:
            return items.sort((a, b) => a.name.localeCompare(b.name))

        case SONG_SORT_KEY.NAME_DESC:
            return items.sort((a, b) => b.name.localeCompare(a.name))

        case SONG_SORT_KEY.ORIGINAL:
            return items.sort(
                (a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0),
            )

        default:
            return items
    }
})

// フィルター付きリスト
const filteringText = ref('')
const filteredItems = computed(() => {
    const keyword = filteringText.value.trim().toLowerCase()
    if (!keyword) return sortedItems.value // 入力なしならソート結果そのまま

    return sortedItems.value.filter((item) =>
        (item.name ?? '').toLowerCase().includes(keyword),
    )
})
watch(
    () => props.isOpen,
    (newVal) => {
        if (newVal) {
            filteringText.value = ''
        }
    },
)
const clearFilteringText = () => {
    filteringText.value = ''
}
</script>

<template>
    <div class="SingersSongList">
        <div class="SingersSongList__inner">
            <header class="SingersSongList__header" ref="headerRef">
                <Button
                    class="SingersSongList__createButton"
                    text="新規追加"
                    color="blue"
                    @click="clickedNewSongCreate"
                />
                <Button
                    class="SingersSongList__sortButton"
                    text="並べ替え"
                    color="green"
                    @click="clickedSongOrder"
                />
                <el-select
                    class="SingersSongList__sortSelect"
                    v-model="sortKey"
                    placeholder="ソート"
                    style="margin-left: 12px; width: 160px"
                >
                    <el-option
                        v-for="option in SONG_SORT_OPTION"
                        :key="option.value"
                        :label="option.label"
                        :value="option.value"
                    />
                </el-select>
                <div class="SingersSongList__filterWrap">
                    <label class="SingersSongList__filterLabel">
                        <span class="SingersSongList__filterLabelText"
                            >フィルター:</span
                        >
                        <input
                            class="SingersSongList__filterInput"
                            type="text"
                            v-model="filteringText"
                        />
                    </label>
                    <Button
                        class="SingersSongList__clearButton"
                        text="クリア"
                        color="gray"
                        size="s"
                        @click="clearFilteringText()"
                    />
                </div>
            </header>
            <el-scrollbar>
                <div class="SingersSongList__content">
                    <div v-if="isLoading" class="SingersSongList__loadingWrap">
                        <Loading text="" :isOverlay="false" />
                    </div>

                    <ul
                        v-if="items.length && !isLoading"
                        class="SingersSongList__items"
                    >
                        <li
                            v-for="item in filteredItems"
                            :key="item.id"
                            class="SingersSongList__item"
                        >
                            <div class="SingersSongList__name">
                                <div>
                                    {{ item.name }}
                                    <small
                                        v-if="!item.lyrics"
                                        class="SingersSongList__lyricNone"
                                        >歌詞なし</small
                                    >
                                </div>
                                <div class="SingersSongList__buttons">
                                    <Button
                                        class="SingersSongList__editButton"
                                        text="編集"
                                        color="blue"
                                        @click="clickedEdit(item)"
                                    />
                                    <Button
                                        class="SingersSongList__deleteButton"
                                        text="削除"
                                        color="red"
                                        @click="
                                            clickedDelete({
                                                id: item.id,
                                                name: item.name,
                                            })
                                        "
                                    />
                                </div>
                            </div>
                            <div class="SingersSongList__thumbnail">
                                <YoutubeThumbnail
                                    :videoId="item.youtube_url ?? ''"
                                    size="mqdefault"
                                />
                            </div>
                        </li>
                    </ul>

                    <p v-if="!items.length && !isLoading">
                        曲が登録されていません。
                    </p>
                </div>
            </el-scrollbar>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.SingersSongList {
    &__header {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    & &__createButton {
    }

    & &__sortSelect {
        margin-left: auto !important;
    }

    &__filterWrap {
        display: flex;
        align-items: center;
        column-gap: 12px;
    }

    & &__clearButton {
        font-size: 12px;
        flex-shrink: 0;
        padding: 6px 8px;
    }

    &__filterLabel {
        display: flex;
        align-items: center;
        column-gap: 16px;
    }

    &__filterLabelText {
        flex-shrink: 0;
    }

    &__filterInput[type='text']:not(.el-input__inner) {
        width: 200px;
    }

    &__loadingWrap {
        text-align: center;
        display: flex;
        justify-content: center;
        padding: 20px 12px;
    }

    &__items {
        display: grid;
        grid-template-columns: repeat(3, 1fr); // デフォルトは4列
        gap: 12px; // 各アイテムの間隔
        padding: 6px;
        margin-top: 12px;

        @media screen and (max-width: 1300px) {
            grid-template-columns: repeat(2, 1fr);
        }

        @media screen and (max-width: 800px) {
            grid-template-columns: 1fr; // 1列
        }
    }

    &__item {
        display: flex;
        align-items: center;
        border-bottom: 1px solid #ccc;
        padding: 16px;
        column-gap: 12px;
        box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
        background-color: #fff;
        border-radius: 3px;
    }

    &__name {
        width: 40%;
        font-weight: bold;
        flex-shrink: 0;
    }

    &__lyricNone {
        display: block;
        font-weight: normal;
        font-size: 12px;
        color: #666;
    }

    &__buttons {
        margin: 20px 0 0;
        display: flex;
        align-items: center;
        column-gap: 12px;
    }

    @media screen and (max-width: 740px) {
        & &__sortSelect {
            width: 124px !important;
        }
    }

    @media screen and (max-width: 600px) {
        &__filterWrap {
            width: 100%;
        }

        &__filterLabel {
            width: 100%;
        }

        &__filterInput[type='text']:not(.el-input__inner) {
            width: 100%;
        }
    }
}
</style>
