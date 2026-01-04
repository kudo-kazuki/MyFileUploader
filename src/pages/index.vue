<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
const { width, height, deviceType } = useWindowSizeAndDevice()
</script>

<template>
    <section
        class="Page"
        :style="{ height: `${height}px` }"
        :data-device="deviceType"
        :data-windowWidth="width"
    >
        <el-scrollbar ref="scrollbarRef"> ああああ </el-scrollbar>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    width: 100%;
    overflow: hidden;
    height: 100%;

    @media screen and (max-width: 740px) {
    }
}
</style>
