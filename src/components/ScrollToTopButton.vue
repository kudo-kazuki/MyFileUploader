<script setup lang="ts">
import arrowUp from '@/assets/images/arrowUp.png'

const isVisible = defineModel<boolean>('isVisible', { default: false })
const emit = defineEmits(['clicked'])
const clicked = () => {
    emit('clicked', true)
}
</script>

<template>
    <transition name="fade">
        <button v-if="isVisible" class="ScrollToTopButton" @click="clicked">
            <img
                class="ScrollToTopButton__icon"
                :src="arrowUp"
                alt="ページトップへ"
            />
        </button>
    </transition>
</template>

<style lang="scss" scoped>
.ScrollToTopButton {
    $size: 48px;
    display: flex;
    justify-content: center;
    align-items: center;
    width: $size;
    height: $size;
    line-height: 0;
    background-color: #fff;
    border: 1px solid #333;
    border-radius: 50%;
    box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4);
    position: fixed;
    bottom: 12px;
    right: 12px;
    z-index: 2;

    &__icon {
        width: 32px;
    }

    &:hover {
        background-color: #f8f8f8;
    }

    @media screen and (max-width: 740px) {
        $size: 36px;
        width: $size;
        height: $size;

        &__icon {
            width: 16px;
        }
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
