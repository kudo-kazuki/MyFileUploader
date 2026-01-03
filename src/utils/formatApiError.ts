import axios from 'axios'

/**
 * APIエラーを整形してメッセージを返す
 * @param error 任意のエラー
 * @param fallback デフォルトメッセージ
 */
export function formatApiError(
    error: unknown,
    fallback = 'サーバーとの通信に失敗しました',
): string {
    if (axios.isAxiosError(error)) {
        const data = error.response?.data
        if (data?.message) {
            return data.message
        }
    }
    return fallback
}
