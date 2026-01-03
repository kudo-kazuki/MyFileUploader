import { ref, watch, onMounted, type Ref } from 'vue'
import { ElScrollbar } from 'element-plus'

export interface UseElScrollbarScrollOptions {
    threshold?: number // ボタン表示のしきい値(px)
    duration?: number // スクロールアニメーションの時間(ms)
}

export const useElScrollbarScroll = (
    scrollbarRef: Ref<InstanceType<typeof ElScrollbar> | null>,
    options: UseElScrollbarScrollOptions = {},
) => {
    const { threshold = 1, duration = 350 } = options

    const showScrollButton = ref(false)
    const currentTop = ref(0)

    // Element Plusのスクロールイベント型
    type ScrollEvt = { scrollTop: number; scrollLeft: number }

    // スクロール時に位置を記録
    const onScroll = ({ scrollTop }: ScrollEvt) => {
        currentTop.value = scrollTop
    }

    // スクロール位置を監視してボタン表示を制御
    watch(currentTop, (t) => {
        showScrollButton.value = t > threshold
    })

    // スムーススクロール to top
    const goToPageTop = () => {
        const wrap = scrollbarRef.value?.wrapRef
        if (wrap?.scrollTo) {
            wrap.scrollTo({ top: 0, behavior: 'smooth' })
            return
        }
        smoothSetScrollTop(0, duration)
    }

    // fallback: 手動スムーススクロール
    const smoothSetScrollTop = (to: number, duration = 300) => {
        const start = currentTop.value
        const startTime = performance.now()
        const easeOutCubic = (x: number) => 1 - Math.pow(1 - x, 3)

        const frame = (now: number) => {
            const prog = Math.min(1, (now - startTime) / duration)
            const eased = easeOutCubic(prog)
            const value = Math.round(start + (to - start) * eased)
            scrollbarRef.value?.setScrollTop?.(value)
            if (prog < 1) requestAnimationFrame(frame)
        }

        requestAnimationFrame(frame)
    }

    // 初期状態を反映
    onMounted(() => {
        currentTop.value = scrollbarRef.value?.wrapRef?.scrollTop ?? 0
    })

    return {
        showScrollButton,
        currentTop,
        onScroll,
        goToPageTop,
    }
}
