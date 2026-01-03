<script setup lang="ts">
interface Props {
    title: string
    size?: 'full' | 'l' | 'm' | 's'
    isTextCenter?: boolean
    isShow?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    size: 'l',
})

const emit = defineEmits(['close'])

const close = () => {
    emit('close', false)
}
</script>

<template>
    <teleport to="body">
        <transition name="fade">
            <section v-if="isShow" class="Modal" :class="[`Modal--${size}`]">
                <div class="Modal__overlay" @click="close()"></div>
                <div class="Modal__inner">
                    <header class="Modal__header">
                        <h1>{{ title }}</h1>
                        <button class="Modal__closeButton" @click="close()">
                            ×
                        </button>
                    </header>
                    <main
                        class="Modal__body"
                        :class="{ 'Modal__body--textCenter': isTextCenter }"
                    >
                        <el-scrollbar>
                            <div class="Modal__bodyInner">
                                <slot name="body" />
                            </div>
                        </el-scrollbar>
                    </main>
                    <footer class="Modal__footer">
                        <slot name="footer">
                            <Button
                                text="閉じる"
                                color="gray"
                                @click="close()"
                            />
                        </slot>
                    </footer>
                </div>
            </section>
        </transition>
    </teleport>
</template>

<style lang="scss" scoped>
.Modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: 24px;
    z-index: 9999;
    justify-content: center;
    align-items: center;

    &__overlay {
        position: fixed;
        top: 0;
        left: 0;
        background-color: rgba(0, 0, 0, 0.6);
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    &__inner {
        position: relative;
        // height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.4);
        word-break: break-all;
    }

    &--full &__inner {
        width: 100%;
    }

    &--l &__inner {
        width: 800px;
    }

    &--m &__inner {
        width: 500px;
    }

    &--s &__inner {
        width: 300px;
    }

    &__header {
        position: relative;
        text-align: center;
        padding: 12px 24px;
        border-bottom: 1px solid #ccc;

        h1 {
            font-size: 24px;
            line-height: 1.4;
            color: #111;
        }
    }

    &__closeButton {
        position: absolute;
        top: 50%;
        right: 24px;
        transform: translateY(-50%);
        background-color: #333;
        border: 1px solid #333;
        color: #fff;
        border-radius: 50%;
        transition:
            background-color 0.2s ease,
            color 0.2s ease;
        font-size: 16px;
        cursor: pointer;
        $size: 28px;
        width: $size;
        height: $size;

        &:hover {
            background-color: #fff;
            color: #333;
        }
    }

    &__body {
        height: 100%;
        overflow-y: auto;
        padding: 24px 12px;
        color: #111;

        &--textCenter {
            text-align: center;
        }
    }

    &__bodyInner {
        padding: 4px 12px;
    }

    &__footer {
        display: flex;
        justify-content: center;
        padding: 12px 24px 16px;

        :deep(ul) {
            display: flex;
            column-gap: 16px;

            .Button {
                width: 112px;
            }
        }
    }

    @media (max-width: 840px) {
        &--l &__inner {
            width: 100%;
        }
    }

    @media (max-width: 600px) {
        padding: 12px;

        &--m &__inner {
            width: 100%;
        }

        &__body {
            padding: 16px 4px;
        }

        &__header {
            padding: 12px;

            h1 {
                font-size: 18px;
            }
        }

        &__closeButton {
            right: 12px;
        }
    }

    @media (max-width: 400px) {
        &--s &__inner {
            width: 100%;
        }
    }
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
</style>
