<script setup lang="ts">
import { watch } from 'vue'

const isVisible = defineModel<boolean>('isVisible', { default: false })

const props = defineProps<{
    type?: string
    message: string
}>()

const typeClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'FlashMessage--success'
        case 'warning':
            return 'FlashMessage--warning'
        case 'error':
            return 'FlashMessage--error'
        default:
            return 'FlashMessage--success'
    }
})

// アニメーションクラスを制御する用
const animationClass = ref('')

watch(isVisible, (val) => {
    if (val) {
        animationClass.value = 'animate__animated animate__bounceInRight'
        setTimeout(() => {
            animationClass.value = 'animate__animated animate__rollOut'
            setTimeout(() => {
                isVisible.value = false
            }, 1500)
        }, 2000)
    }
})
</script>

<template>
    <div
        v-if="isVisible"
        class="FlashMessage"
        :class="[typeClass, animationClass]"
    >
        <div class=""></div>
        {{ props.message }}
    </div>
</template>

<style lang="scss" scoped>
.FlashMessage {
    position: fixed;
    bottom: 32px;
    right: 32px;
    border: 1px solid #333;
    color: #fff;
    padding: 16px 24px;
    border-radius: 6px;
    font-weight: bold;
    font-size: 24px;
    box-shadow: 1px 1px 6px rgba(0, 0, 0, 0.5);
    z-index: 10002;

    &--success {
        background-color: #14ab55;
        border-color: #125f32;
    }

    &--warning {
        background-color: #f39c12;
        border-color: #925f0c;
    }

    &--error {
        background-color: #e74c3c;
        border-color: #8c261a;
    }
}
</style>
