<script setup lang="ts">
import { ref } from 'vue'
import AccordionItem from '@/components/AccordionItem.vue'
import type { Song } from '@/types'

interface Props {
    items: Song[]
    isLoading?: boolean
}

const props = withDefaults(defineProps<Props>(), {})

// 各アイテムごとに「iframeを表示するか」のフラグ
const activeIframes = ref<Record<number, boolean>>({})
</script>

<template>
    <div class="SingerSongAccordionList">
        <ul>
            <li v-for="item in items" :key="item.id">
                <AccordionItem
                    :title="item.name"
                    @opened="activeIframes[item.id] = true"
                    @closed="activeIframes[item.id] = false"
                >
                    <div class="SingerSongAccordionList__content">
                        <div class="SingerSongAccordionList__iframeWrap">
                            <iframe
                                v-if="activeIframes[item.id]"
                                class="SingerSongAccordionList__iframe"
                                :src="`https://www.youtube.com/embed/${item.youtube_url}`"
                                frameborder="0"
                                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                            <Loading
                                class="SingerSongAccordionList__loading"
                                text=""
                                :isOverlay="false"
                            />
                        </div>

                        <pre
                            v-if="item.lyrics"
                            class="SingerSongAccordionList__lyrics"
                            v-html="item.lyrics"
                        ></pre>
                    </div>
                </AccordionItem>
            </li>
        </ul>
    </div>
</template>

<style lang="scss" scoped>
.SingerSongAccordionList {
    &__iframeWrap {
        position: relative;
        height: 300px;
    }

    &__iframe {
        position: relative;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    &__loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 0;
    }

    &__lyrics {
        margin-top: 20px;
    }

    @media screen and (max-width: 740px) {
    }
}
</style>
