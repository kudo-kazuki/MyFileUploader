<script setup lang="ts">
import { ref, computed } from 'vue'
import arrowUp from '@/assets/images/arrowUp.png'
import arrowDown from '@/assets/images/arrowDown.png'

interface Props {
    title: string
    isTextCenter?: boolean
}
const props = withDefaults(defineProps<Props>(), { isTextCenter: true })

// 親へ通知したいならここに追加
const emit = defineEmits<{
    (e: 'opened'): void
    (e: 'closed'): void
}>()

const isActive = ref(false)
const isAnimating = ref(false)

// ユニークID（useId がない環境向けの簡易版）
const uid = `acc-${Math.random().toString(36).slice(2, 10)}`
const btnId = `btn-${uid}`
const panelId = `panel-${uid}`

const iconName = computed(() => (isActive.value ? arrowUp : arrowDown))

const toggle = () => {
    if (isAnimating.value) return
    isActive.value = !isActive.value
}

// ---- transition hooks（height アニメ）----
const onBeforeEnter = (el: Element) => {
    const elh = el as HTMLElement
    elh.style.height = '0px'
}
const onEnter = (el: Element, done: () => void) => {
    isAnimating.value = true
    const elh = el as HTMLElement
    // いったん 0 を適用 → リフロー → 目標高さへ
    elh.style.height = '0px'
    void elh.offsetHeight // ★リフロー
    elh.style.height = `${elh.scrollHeight}px`

    const onEnd = (ev: TransitionEvent) => {
        if (ev.propertyName !== 'height') return
        elh.removeEventListener('transitionend', onEnd)
        elh.style.height = 'auto' // 展開後は自動高さでOK（中身が動いても追従）
        isAnimating.value = false
        emit('opened')
        done()
    }
    elh.addEventListener('transitionend', onEnd)
}

const onBeforeLeave = (el: Element) => {
    const elh = el as HTMLElement
    // 現在の実高さを起点にする
    elh.style.height = `${elh.scrollHeight}px`
    void elh.offsetHeight // ★リフロー
}
const onLeave = (el: Element, done: () => void) => {
    isAnimating.value = true
    const elh = el as HTMLElement
    elh.style.height = '0px'

    const onEnd = (ev: TransitionEvent) => {
        if (ev.propertyName !== 'height') return
        elh.removeEventListener('transitionend', onEnd)
        isAnimating.value = false
        emit('closed')
        done()
    }
    elh.addEventListener('transitionend', onEnd)
}
</script>

<template>
    <div
        class="AccordionItem"
        :class="{
            'AccordionItem--center': isTextCenter,
            'AccordionItem--active': isActive,
        }"
    >
        <button
            :id="btnId"
            class="AccordionItem__button"
            type="button"
            :disabled="isAnimating"
            :aria-expanded="isActive"
            :aria-controls="panelId"
            @click="toggle"
        >
            {{ props.title }}
            <img class="AccordionItem__icon" :src="iconName" alt="" />
        </button>

        <transition
            @before-enter="onBeforeEnter"
            @enter="onEnter"
            @before-leave="onBeforeLeave"
            @leave="onLeave"
        >
            <div
                v-show="isActive"
                :id="panelId"
                class="AccordionItem__content"
                role="region"
                :aria-labelledby="btnId"
            >
                <div class="AccordionItem__contentInner">
                    <slot />
                </div>
            </div>
        </transition>
    </div>
</template>

<style lang="scss" scoped>
.AccordionItem {
    border-bottom: 1px solid #111;
    &__button {
        position: relative;
        display: block;
        width: 100%;
        background: #fff;
        padding: 12px 32px 12px 32px;
        font-size: 18px;
        text-align: left;
        cursor: pointer;
    }
    &--center &__button {
        text-align: center;
    }
    &--active &__button {
        background-color: orange;
    }

    &__content {
        overflow: hidden;
        transition: height 0.25s ease;
    }

    &__contentInner {
        padding: 12px;
    }

    &__icon {
        $size: 20px;
        width: $size;
        height: $size;
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
    }

    @media screen and (max-width: 740px) {
        &__button {
            font-size: 15px;
        }

        &__icon {
            $size: 16px;
            width: $size;
            height: $size;
            right: 10px;
        }
    }
}
</style>
