<script setup lang="ts">
import { ref } from 'vue'
import type { SubmitPayload, SongFormInput } from '@/types'
import { MAX_LYRICS_LENGTH, EMPTY_DATA } from '@/constants/song'

interface Props {
    isCreate?: boolean
    singerId?: number | null
    editData?: SongFormInput
    serverMessage?: string
}

const props = withDefaults(defineProps<Props>(), {
    isCreate: true,
})

const emit = defineEmits<{
    (e: 'onSubmit', payload: SubmitPayload): void
}>()

const editData = ref<SongFormInput>({ ...EMPTY_DATA })
watch(
    () => props.editData,
    (newVal) => {
        if (newVal) {
            editData.value = { ...newVal }
            if (props.singerId) {
                editData.value.singer_id = props.singerId
            }
        }
    },
    { immediate: true, deep: true },
)

const isConfirmed = ref(false)

const goToConfirm = () => {
    isConfirmed.value = true
}
const backToEdit = () => {
    isConfirmed.value = false
}

const errorMessage = ref({
    name: '',
    youtube_url: '',
    release_date: '',
    lyrics: '',
})
const onValidate = () => {
    Object.keys(errorMessage.value).forEach((key) => {
        errorMessage.value[key as keyof typeof errorMessage.value] = ''
    })

    if (!editData.value.name || editData.value.name === '') {
        errorMessage.value.name = '曲名は必須です'
    }

    if (!editData.value.youtube_url || editData.value.youtube_url === '') {
        errorMessage.value.youtube_url = 'YoutubeURLは必須です'
    }

    // 歌詞: 任意だが長すぎるのはNG
    if (
        editData.value.lyrics &&
        editData.value.lyrics.length > MAX_LYRICS_LENGTH
    ) {
        errorMessage.value.lyrics = `歌詞は${MAX_LYRICS_LENGTH}文字以内で入力してください`
    }

    const hasError = Object.values(errorMessage.value).some((msg) => msg !== '')
    if (!hasError) {
        goToConfirm()
    }
}

const extractYoutubeVideoId = (input: string): string | null => {
    // URL形式でない（http/httpsで始まらない）場合
    if (!/^https?:\/\//i.test(input)) {
        // 半角英数字と - _ のみで構成されていれば videoId として許可
        if (/^[\w-]{6,}$/i.test(input)) {
            return input
        } else {
            return null
        }
    }

    try {
        const u = new URL(input)
        const host = u.hostname.toLowerCase()

        if (host.endsWith('youtube.com')) {
            const v = u.searchParams.get('v')
            return v || null
        }

        return null
    } catch {
        return null
    }
}

const onSubmit = () => {
    const submitData: SongFormInput = { ...editData.value }

    if (!props.singerId) {
        alert('singer_idがないのはおかしい')
        return
    }

    if (!props.isCreate && !submitData.id) {
        alert('編集でidがないのはおかしい')
        return
    }

    if (!submitData.youtube_url) {
        alert('youtube_urlがないのはおかしい')
        return
    }

    const videoId = extractYoutubeVideoId(submitData.youtube_url)
    if (!videoId) {
        console.error('無効なYoutube URLです')
        alert('無効なYoutube URLです')
        return
    }

    submitData.youtube_url = videoId

    if (props.isCreate) {
        delete submitData.id
    }

    if (props.singerId && !submitData.singer_id) {
        submitData.singer_id = props.singerId
    }

    console.log('送信データ:', submitData)

    emit('onSubmit', {
        isCreate: props.isCreate,
        songData: submitData,
    })
}
</script>

<template>
    <section class="SongForm">
        <div class="SongForm__inner">
            <transition name="fade-in-only" mode="out-in">
                <form v-if="!isConfirmed" class="SongForm__form" key="form">
                    <input
                        v-if="!isCreate"
                        type="hidden"
                        :value="editData.id ?? 0"
                    />
                    <input type="hidden" :value="editData.singer_id ?? 0" />
                    <ul class="SongForm__editItems">
                        <li class="SongForm__editItem">
                            <label class="SongForm__editLabel" for="name"
                                >曲名：<RequireLabel
                            /></label>
                            <div class="SongForm__inputWrap">
                                <input
                                    type="text"
                                    v-model="editData.name"
                                    id="name"
                                />
                                <p
                                    v-if="errorMessage.name"
                                    class="SongForm__errorMessage"
                                >
                                    {{ errorMessage.name }}
                                </p>
                            </div>
                        </li>
                        <li class="SongForm__editItem">
                            <label class="SongForm__editLabel" for="name"
                                >YoutubeURL：<RequireLabel
                            /></label>
                            <div class="SongForm__inputWrap">
                                <input
                                    type="text"
                                    v-model="editData.youtube_url"
                                    id="youtube_url"
                                    placeholder="https://www.youtube.com/watch?v=xxxxxxxxxxx"
                                />
                                <p
                                    v-if="errorMessage.youtube_url"
                                    class="SongForm__errorMessage"
                                >
                                    {{ errorMessage.youtube_url }}
                                </p>
                            </div>
                        </li>
                        <li
                            class="SongForm__editItem SongForm__editItem--danochi"
                        >
                            <label class="SongForm__editLabel" for="lyrics"
                                >歌詞：</label
                            >
                            <div class="SongForm__inputWrap">
                                <textarea
                                    class="SongForm__lyrics"
                                    v-model="editData.lyrics"
                                    id="lyrics"
                                ></textarea>
                                <p
                                    v-if="errorMessage.lyrics"
                                    class="SongForm__errorMessage"
                                >
                                    {{ errorMessage.lyrics }}
                                </p>
                            </div>
                        </li>
                    </ul>
                </form>

                <div v-else key="confirm">
                    <p>
                        以下で{{
                            isCreate ? '登録' : '上書き'
                        }}します。<br />よろしいですか？
                    </p>
                    <table class="table SongForm__table">
                        <tbody>
                            <tr>
                                <th>曲名</th>
                                <td>{{ editData.name }}</td>
                            </tr>
                            <tr>
                                <th>YoutubeURL</th>
                                <td>{{ editData.youtube_url }}</td>
                            </tr>
                            <tr>
                                <th>歌詞</th>
                                <td>
                                    <pre
                                        v-if="editData.lyrics"
                                        v-html="editData.lyrics"
                                    ></pre>
                                    <span v-else>-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </transition>

            <transition name="fade-in-only" mode="out-in">
                <div v-if="isConfirmed" class="SongForm__footerButtonWrap">
                    <Button
                        class="SongForm__footerButton"
                        text="戻る"
                        color="gray"
                        @click="backToEdit()"
                    />
                    <Button
                        class="SongForm__footerButton"
                        :text="isCreate ? '登録' : '上書き'"
                        color="blue"
                        @click="onSubmit()"
                    />
                </div>
                <div v-else class="SongForm__footerButtonWrap">
                    <Button
                        class="SongForm__footerButton"
                        text="確認"
                        color="blue"
                        @click="onValidate()"
                    />
                </div>
            </transition>

            <p v-if="serverMessage" class="SongForm__errorMessage">
                {{ serverMessage }}
            </p>
        </div>
    </section>
</template>

<style lang="scss" scoped>
.SongForm {
    width: 100%;
    height: 100%;

    &__inner {
        height: 100%;
    }

    &__editItems {
        display: flex;
        flex-direction: column;
        row-gap: 12px;
    }

    &__editItem {
        display: flex;
        align-items: center;

        &--danochi {
            display: block;
        }
    }

    &__inputWrap {
        width: 100%;
    }

    &__editItem--danochi &__editLabel {
        display: block;
        margin-bottom: 8px;
        width: 70px;
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
        margin: 8px 0;
    }

    &__lyrics {
        height: 300px;
    }

    &__table {
        tr {
            th {
                width: 150px;
            }
        }
    }

    &__footerButtonWrap {
        display: flex;
        flex-shrink: 0;
        justify-content: center;
        column-gap: 16px;
        margin-top: 20px;
    }

    & &__footerButton {
        width: 96px;
    }

    @media screen and (max-width: 740px) {
        &__editLabel {
            width: 138px;
        }

        &__table {
            tr {
                th {
                    width: 100%;
                }
            }
        }
    }
}

/* 出現時のみアニメーション */
.fade-in-only-enter-active {
    transition: opacity 0.3s ease;
}
.fade-in-only-enter-from {
    opacity: 0;
}
.fade-in-only-enter-to {
    opacity: 1;
}

/* 消える側は即座に非表示（アニメーションなし） */
.fade-in-only-leave-active {
    transition: none;
}
.fade-in-only-leave-from,
.fade-in-only-leave-to {
    opacity: 0;
}
</style>
