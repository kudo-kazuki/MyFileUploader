<script setup lang="ts">
interface Props {
    text: string
    color?: 'blue' | 'black' | 'gray' | 'red' | 'orange' | 'green'
    size?: 'l' | 'm' | 's'
    isActive?: boolean
    isDisabled?: boolean
    icon?: string
}

const props = withDefaults(defineProps<Props>(), {
    color: 'black',
    size: 's',
})
</script>

<template>
    <button
        class="Button"
        :class="[
            `Button--${color}`,
            `Button--${size}`,
            { 'Button--active': isActive, 'Button--disabled': isDisabled },
        ]"
        :disabled="isDisabled ? true : false"
    >
        <img v-if="icon" class="Button__icon" :src="icon" alt="" />
        {{ text }}
    </button>
</template>

<style lang="scss" scoped>
$size-styles: (
    'l': (
        'font-size': 28px,
        'padding': 8px 24px,
    ),
    'm': (
        'font-size': 20px,
        'padding': 12px 20px,
    ),
    's': (
        'font-size': 14px,
        'padding': 8px 12px,
    ),
);

$color-styles: (
    'blue': (
        'color': #fff,
        'background-color': #1542d5,
        'hover-color': #fff,
        'hover-background-color': #1a45d4,
    ),
    'black': (
        'color': #fff,
        'background-color': #111,
        'hover-color': #fff,
        'hover-background-color': #111,
    ),
    'gray': (
        'color': #111,
        'background-color': #eee,
        'hover-color': #fff,
        'hover-background-color': #bbb,
    ),
    'red': (
        'color': #fff,
        'background-color': #c11d1d,
        'hover-color': #fff,
        'hover-background-color': #c11d1d,
    ),
    'orange': (
        'color': #fff,
        'background-color': #e97911,
        'hover-color': #fff,
        'hover-background-color': #ee8019,
    ),
    'green': (
        'color': #fff,
        'background-color': #037427,
        'hover-color': #fff,
        'hover-background-color': #077d2d,
    ),
);

.Button {
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fff;
    padding: 8px 12px;
    font-size: 14px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.4);

    &:not(--disabled):hover {
        opacity: 0.9;
    }

    &--disabled {
        pointer-events: none;
        color: #aaa !important;
        background-color: #efefef !important;
    }

    @each $size, $style in $size-styles {
        &--#{$size} {
            font-size: map-get($style, 'font-size');
            padding: map-get($style, 'padding');
        }
    }

    @each $color, $style in $color-styles {
        &--#{$color} {
            color: map-get($style, 'color');
            background-color: map-get($style, 'background-color');

            &:hover {
                color: map-get($style, 'hover-color');
                background-color: map-get($style, 'hover-background-color');
            }
        }
    }

    &__icon {
        width: 24px;
        height: 24px;
        margin-right: 8px;
    }
}
</style>
