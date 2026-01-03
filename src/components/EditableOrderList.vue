<script setup lang="ts">
import draggable from 'vuedraggable'
import dots from '@/assets/images/dots.png'

type Item = Record<string, any>

/**
 * 並び替え対象（必ず keyName を持つこと）
 */
const modelValue = defineModel<Item[]>({
    required: true,
})

const props = withDefaults(
    defineProps<{
        keyName?: string
        labelKey?: string
    }>(),
    {
        keyName: 'id',
        labelKey: 'name',
    },
)
</script>

<template>
    <draggable
        v-model="modelValue"
        :item-key="keyName"
        tag="ul"
        handle=".drag-handle"
        animation="150"
        class="EditableOrderList"
    >
        <template #item="{ element }">
            <li class="EditableOrderList__item">
                <button class="EditableOrderList__button drag-handle">
                    <img class="EditableOrderList__icon" :src="dots" alt="" />
                    <span class="EditableOrderList__label">
                        {{ element[props.labelKey] }}
                    </span>
                </button>
            </li>
        </template>
    </draggable>
</template>

<style lang="scss" scoped>
.EditableOrderList {
    touch-action: manipulation;

    &__item {
        touch-action: manipulation;
    }

    &__item:first-child &__button {
        border-top: 1px solid #ddd;
    }

    &__item[draggable='true'] &__button {
        box-shadow: 1px 1px 4px rgba($color: #000000, $alpha: 0.3);
        background-color: orange;
        transition: 0.2s ease background-color;
    }

    &__button {
        width: 100%;
        cursor: grab;
        padding: 0 12px;
        font-size: 18px;
        user-select: none;
        display: flex;
        align-items: center;
        column-gap: 12px;
        padding: 6px 12px;
        border-bottom: 1px solid #ddd;
        touch-action: manipulation;
        background-color: #fff;
    }

    &__icon {
        width: 12px;
    }

    @media screen and (max-width: 900px) {
        padding-right: 80px;
    }

    @media screen and (max-width: 740px) {
        &__label {
            font-size: 14px;
        }
    }

    @media screen and (max-width: 500px) {
        padding-right: 48px;

        &__label {
            font-size: 13px;
        }
    }
}
</style>
