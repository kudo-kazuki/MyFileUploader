<script setup lang="ts">
import { ref } from 'vue'
import type { Singer } from '@/types'

interface Props {
    items: Singer[]
    activeId?: number | null
}

const props = withDefaults(defineProps<Props>(), {})

const emit = defineEmits(['clickedSinger'])

const clickedSinger = (id: number) => {
    emit('clickedSinger', id)
}

const activeId = ref(props.activeId ?? null)
watch(
    () => props.activeId,
    (newVal) => {
        activeId.value = newVal ?? null
    },
)
</script>

<template>
    <div class="SingerButtonList">
        <ul v-if="items.length" class="SingerButtonList__items">
            <li
                v-for="item in items"
                :key="item.id"
                class="SingerButtonList__item"
            >
                <button
                    class="SingerButtonList__button"
                    :class="{
                        'SingerButtonList__button--active':
                            item.id === activeId,
                    }"
                    @click="clickedSinger(item.id)"
                >
                    {{ item.name }}
                </button>
            </li>
        </ul>
        <p v-else>歌手データがありません。</p>
    </div>
</template>

<style lang="scss" scoped>
.SingerButtonList {
    width: 100%;

    &__item {
        border-bottom: 1px solid #ccc;
    }

    &__button {
        width: 100%;
        position: relative;
        display: block;
        font-size: 20px;
        border: none;
        background-color: #fff;
        padding: 12px 20px;
        cursor: pointer;
        color: #111;

        &:not(&--active):hover {
            background-color: #eee;
        }

        &--active {
            background-color: orange;
        }

        &:before {
            content: '';
            display: block;
            width: 20px;
            height: 20px;
            background: url(/src/assets/images/arrowRight.png) no-repeat 0 0;
            background-size: 20px;
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
        }
    }

    @media screen and (max-width: 740px) {
        &__button {
            padding: 10px 30px 10px 12px;
            font-size: 16px;

            &:before {
                $size: 16px;
                width: $size;
                height: $size;
                right: $size;
                background-size: $size;
            }
        }
    }
}
</style>
