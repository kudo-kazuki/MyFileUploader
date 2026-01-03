<script setup lang="ts">
import iconClose from '@/assets/images/close2.png'
import { ElScrollbar } from 'element-plus'
import { useElScrollbarScroll } from '@/composables/useElScrollbarScroll'

interface Props {
    title?: string
}

const props = withDefaults(defineProps<Props>(), {
    title: '',
})

const isOpen = defineModel<boolean>('isOpen', { default: false })

const emit = defineEmits<{
    (e: 'afterClose'): void
}>()

const close = () => {
    isOpen.value = false
}

// スクロール対象の el-scrollbar インスタンス
const scrollbarRef = ref<InstanceType<typeof ElScrollbar> | null>(null)
const { showScrollButton, onScroll, goToPageTop } = useElScrollbarScroll(
    scrollbarRef,
    {
        threshold: 1, // ボタン表示しきい値
        duration: 350, // アニメ時間
    },
)
</script>

<template>
    <transition name="slide" @after-leave="$emit('afterClose')">
        <section v-show="isOpen" class="SlideContents">
            <div class="SlideContents__inner">
                <header class="SlideContents__header">
                    <h1 v-if="title" class="SlideContents__title">
                        {{ title }}
                    </h1>
                    <button class="SlideContents__closeButton" @click="close">
                        <img
                            class="SlideContents__icon"
                            :src="iconClose"
                            alt="閉じる"
                        />
                        閉じる
                    </button>
                </header>

                <el-scrollbar ref="scrollbarRef" @scroll="onScroll">
                    <div class="SlideContents__container">
                        <slot />
                    </div>
                </el-scrollbar>
            </div>

            <ScrollToTopButton
                v-model:isVisible="showScrollButton"
                @clicked="goToPageTop()"
            />
        </section>
    </transition>
</template>

<style lang="scss" scoped>
.SlideContents {
    position: fixed;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
    z-index: 999;

    &__header {
        display: flex;
        justify-content: space-between;
        padding: 16px 16px;
        border-bottom: 1px solid #333;
    }

    &__title {
        font-weight: bold;
    }

    &__closeButton {
        display: flex;
        column-gap: 4px;
        align-items: center;
        height: 32px;
        line-height: 0;
        margin-left: auto;
    }

    &__icon {
        height: 100%;
    }

    &__inner {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    &__container {
        padding: 16px 16px 60px;
    }

    @media screen and (max-width: 740px) {
        &__header {
            padding: 12px;
        }

        &__closeButton {
            height: 24px;
        }

        &__container {
            padding: 12px 12px 52px;
        }
    }
}

/* --- transition アニメーション定義 --- */
.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
    transform: translateX(0);
    opacity: 1;
}

.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}
</style>
