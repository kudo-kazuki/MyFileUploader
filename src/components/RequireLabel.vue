<script setup lang="ts">
interface Props {
    text?: string
    isRequire?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    text: '必須',
    isRequire: true,
})
</script>

<template>
    <span class="RequireLabel" :class="{ 'RequireLabel--require': isRequire }">
        {{ text }}
    </span>
</template>

<style lang="scss" scoped>
.RequireLabel {
    &--require {
        background-color: #c11d1d;
    }

    background-color: #aaa;
    color: #fff;
    display: inline-block;
    padding: 0 4px;
    font-size: 11px;
}
</style>
